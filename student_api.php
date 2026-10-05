<?php
require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Current-User");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$currentUser = get_current_user_context($pdo);

try {
    if ($method === 'GET') {
        $where = ["grade != '7'"];
        $params = [];

        // Enforce teacher access restriction
        if ($currentUser && ($currentUser['role'] ?? '') === 'teacher') {
            $assignedGrade = trim((string)($currentUser['assigned_grade'] ?? ''));
            $assignedSection = trim((string)($currentUser['assigned_section'] ?? ''));

            if ($assignedGrade !== '' && $assignedGrade !== 'all') {
                $grades = array_values(array_filter(array_map('trim', explode(',', $assignedGrade))));
                if (!empty($grades)) {
                    $inPlaceholders = implode(',', array_fill(0, count($grades), '?'));
                    $where[] = "grade IN ($inPlaceholders)";
                    foreach ($grades as $g) { $params[] = $g; }
                }
            }

            if ($assignedSection !== '' && $assignedSection !== 'all') {
                $sections = array_values(array_filter(array_map('trim', explode(',', $assignedSection))));
                if (!empty($sections)) {
                    $inPlaceholders = implode(',', array_fill(0, count($sections), '?'));
                    $where[] = "section IN ($inPlaceholders)";
                    foreach ($sections as $s) { $params[] = $s; }
                }
            }
        } else {
            // Admin or unauthenticated/CLI test harness
            if (!empty($_GET['grade']) && $_GET['grade'] !== 'all') {
                $where[] = "grade = ?";
                $params[] = $_GET['grade'];
            }
            if (!empty($_GET['section']) && $_GET['section'] !== 'all') {
                $where[] = "section = ?";
                $params[] = $_GET['section'];
            }
        }

        $sql = "SELECT id, name, grade, section, recitations, total_score AS totalScore, photo FROM students";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY grade, name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "students" => $students, "count" => count($students)]);

    } else if ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!$input && !empty($_POST)) {
            $input = $_POST;
        }

        if (!$input || !isset($input['id'], $input['name'], $input['grade'])) {
            echo json_encode(["status" => "error", "message" => "id, name, and grade required"]);
            exit;
        }

        $targetGrade = (string)$input['grade'];
        $targetSection = $input['section'] ?? 'A';

        // Disallow Grade 7
        if ($targetGrade === '7') {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Grade 7 is not supported in elementary science."]);
            exit;
        }

        // Access Control check for new student
        if (!check_teacher_grade_access($pdo, $targetGrade, $targetSection, $currentUser)) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Access denied: You are only authorized to add or edit students in your assigned grade/section."
            ]);
            exit;
        }

        // If updating an existing student, verify existing student also belongs to this teacher's load
        $existingStmt = $pdo->prepare("SELECT grade, section FROM students WHERE id = ?");
        $existingStmt->execute([$input['id']]);
        $existingStudent = $existingStmt->fetch(PDO::FETCH_ASSOC);
        if ($existingStudent && !check_teacher_grade_access($pdo, $existingStudent['grade'], $existingStudent['section'], $currentUser)) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Access denied: Cannot modify a student from another grade/section."
            ]);
            exit;
        }

        if (is_sqlite()) {
            $stmt = $pdo->prepare(
                "INSERT INTO students (id, name, grade, section, recitations, total_score, photo)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON CONFLICT(id) DO UPDATE SET
                   name        = excluded.name,
                   grade       = excluded.grade,
                   section     = excluded.section,
                   recitations = excluded.recitations,
                   total_score = excluded.total_score,
                   photo       = excluded.photo"
            );
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO students (id, name, grade, section, recitations, total_score, photo)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   name        = VALUES(name),
                   grade       = VALUES(grade),
                   section     = VALUES(section),
                   recitations = VALUES(recitations),
                   total_score = VALUES(total_score),
                   photo       = VALUES(photo)"
            );
        }
        $stmt->execute([
            $input['id'],
            $input['name'],
            $targetGrade,
            $targetSection,
            $input['recitations']  ?? 0,
            $input['total_score']  ?? $input['totalScore'] ?? 0,
            $input['photo']        ?? null
        ]);

        echo json_encode(["status" => "success", "message" => "Student saved"]);

    } else if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input && !empty($_POST)) $input = $_POST;
        if (!$input && !empty($_GET)) $input = $_GET;
        if (!$input && !empty($_REQUEST)) $input = $_REQUEST;

        if (!$input || !isset($input['id'])) {
            echo json_encode(["status" => "error", "message" => "id required"]);
            exit;
        }

        // Check student exists and permissions
        $stmtCheck = $pdo->prepare("SELECT name, grade, section FROM students WHERE id = ?");
        $stmtCheck->execute([$input['id']]);
        $student = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode(["status" => "error", "message" => "Student not found"]);
            exit;
        }

        if (!check_teacher_grade_access($pdo, $student['grade'], $student['section'], $currentUser)) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Access denied: You are not authorized to delete students outside your assigned grade/section."
            ]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$input['id']]);

        // Clean up linked recitation records and DepEd grades
        try {
            $pdo->prepare("DELETE FROM recitation_records WHERE student_id = ?")->execute([$input['id']]);
            $pdo->prepare("DELETE FROM student_grades WHERE student_name = ? AND grade_level = ?")->execute([$student['name'], $student['grade']]);
        } catch (Exception $cleanEx) { /* ignore */ }

        echo json_encode(["status" => "success", "message" => "Student removed"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

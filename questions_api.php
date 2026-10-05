<?php
require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Current-User");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$currentUser = get_current_user_context($pdo);

try {
    // Dynamic database migration for type column
    try {
        if (!db_column_exists($pdo, 'questions', 'type')) {
            $colType = is_sqlite() ? "TEXT DEFAULT 'multiple-choice'" : "VARCHAR(50) DEFAULT 'multiple-choice'";
            $pdo->exec("ALTER TABLE questions ADD COLUMN type $colType");
        }
    } catch (Exception $e) { /* ignore */ }

    if ($method === 'GET') {
        $where = ["grade != '7'"];
        $params = [];

        $grade = $_GET['grade'] ?? '';
        $topic = $_GET['topic'] ?? '';

        // If teacher is logged in, isolate to their assigned grade if set
        if ($currentUser && ($currentUser['role'] ?? '') === 'teacher') {
            $assignedGrade = trim((string)($currentUser['assigned_grade'] ?? ''));
            if ($assignedGrade !== '' && $assignedGrade !== 'all') {
                if ($grade === '' || $grade === 'all' || !check_teacher_grade_access($pdo, $grade, 'all', $currentUser)) {
                    $grade = $assignedGrade;
                }
            }
        }

        if ($grade !== '' && $grade !== 'all') {
            $where[] = "grade = ?";
            $params[] = (string)$grade;
        }
        if ($topic !== '' && $topic !== 'all') {
            $where[] = "topic = ?";
            $params[] = (string)$topic;
        }

        $sql = "SELECT * FROM questions";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY grade, topic, id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $questions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $questions[] = [
                'id' => $row['id'],
                'grade' => $row['grade'],
                'topic' => $row['topic'],
                'difficulty' => $row['difficulty'],
                'text' => $row['question_text'],
                'type' => $row['type'] ?? 'multiple-choice'
            ];
        }
        echo json_encode(["status" => "success", "questions" => $questions]);
    } 
    else if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception("Invalid JSON");
        
        $grade = (string)($input['grade'] ?? '4');
        if ($grade === '7') throw new Exception("Grade 7 questions are not supported in elementary science.");

        // Teacher access check
        if (!check_teacher_grade_access($pdo, $grade, 'all', $currentUser)) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied: You are only authorized to add questions for your assigned grade."]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO questions (grade, topic, difficulty, question_text, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$grade, $input['topic'], $input['difficulty'], $input['text'], $input['type'] ?? 'multiple-choice']);
        
        if (!empty($grade) && !empty($input['topic'])) {
            try {
                $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
                $stmtTCheck->execute([$grade, $input['topic']]);
                if ($stmtTCheck->fetchColumn() == 0) {
                    $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
                    $stmtTIns->execute([$grade, $input['topic']]);
                }
            } catch(Exception $te) {}
        }

        echo json_encode(["status" => "success", "message" => "Question added", "id" => $pdo->lastInsertId()]);
    }
    else if ($method === 'PUT') {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;
        if (!$input || !isset($input['id'])) throw new Exception("Invalid JSON or missing ID");
        
        $difficulty = $input['difficulty'] ?? 'Medium';
        $text = $input['text'] ?? $input['question_text'] ?? '';
        $type = $input['type'] ?? 'multiple-choice';
        $grade = isset($input['grade']) ? (string)$input['grade'] : null;
        $topic = $input['topic'] ?? null;

        if ($grade === '7') throw new Exception("Grade 7 questions are not supported in elementary science.");

        // Verify existing question permissions
        $stmtCheck = $pdo->prepare("SELECT grade FROM questions WHERE id = ?");
        $stmtCheck->execute([$input['id']]);
        $existingQ = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if ($existingQ && !check_teacher_grade_access($pdo, $existingQ['grade'], 'all', $currentUser)) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied: Cannot edit questions for other grade levels."]);
            exit;
        }

        if ($grade !== null && !check_teacher_grade_access($pdo, $grade, 'all', $currentUser)) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied: Cannot change question grade to an unassigned grade."]);
            exit;
        }

        if ($grade !== null && $topic !== null) {
            $stmt = $pdo->prepare("UPDATE questions SET grade = ?, topic = ?, difficulty = ?, question_text = ?, type = ? WHERE id = ?");
            $stmt->execute([$grade, $topic, $difficulty, $text, $type, $input['id']]);

            try {
                $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
                $stmtTCheck->execute([$grade, $topic]);
                if ($stmtTCheck->fetchColumn() == 0) {
                    $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
                    $stmtTIns->execute([$grade, $topic]);
                }
            } catch(Exception $te) {}
        } else {
            $stmt = $pdo->prepare("UPDATE questions SET difficulty = ?, question_text = ?, type = ? WHERE id = ?");
            $stmt->execute([$difficulty, $text, $type, $input['id']]);
        }
        
        echo json_encode(["status" => "success", "message" => "Question updated"]);
    }
    else if ($method === 'DELETE') {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        if (!$input && !empty($_POST)) $input = $_POST;
        if (!$input && !empty($_GET)) $input = $_GET;
        if (!$input && !empty($_REQUEST)) $input = $_REQUEST;

        if (!$input || !isset($input['id'])) throw new Exception("Invalid JSON or missing ID");
        
        // Verify permissions
        $stmtCheck = $pdo->prepare("SELECT grade FROM questions WHERE id = ?");
        $stmtCheck->execute([$input['id']]);
        $existingQ = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if ($existingQ && !check_teacher_grade_access($pdo, $existingQ['grade'], 'all', $currentUser)) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied: Cannot delete questions for other grade levels."]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(["status" => "success", "message" => "Question deleted"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

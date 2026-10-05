<?php
require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Current-User");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$currentUser = get_current_user_context($pdo);

try {
    if ($method === 'POST') {
        // Record a single recitation result
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;

        if (!$input || !isset($input['student_id'])) {
            echo json_encode(["status" => "error", "message" => "student_id required"]);
            exit;
        }

        // Verify teacher permission for this student
        $stmtSt = $pdo->prepare("SELECT grade, section FROM students WHERE id = ?");
        $stmtSt->execute([$input['student_id']]);
        $st = $stmtSt->fetch(PDO::FETCH_ASSOC);

        if ($st && !check_teacher_grade_access($pdo, $st['grade'], $st['section'], $currentUser)) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied: You are not authorized to record recitations for this student."]);
            exit;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO recitation_records (student_id, topic, difficulty, points, is_correct)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $input['student_id'],
            $input['topic']      ?? '',
            $input['difficulty'] ?? 'Easy',
            $input['points']     ?? 0,
            $input['is_correct'] ?? 0
        ]);

        echo json_encode(["status" => "success", "id" => $pdo->lastInsertId()]);

    } else if ($method === 'GET') {
        // Fetch recitation history for a student (or all)
        $studentId = $_GET['student_id'] ?? null;

        $sql = "SELECT r.*, s.name AS student_name, s.grade, s.section
                FROM recitation_records r
                JOIN students s ON s.id = r.student_id
                WHERE s.grade != '7'";
        $params = [];

        if ($studentId) {
            $sql .= " AND r.student_id = ?";
            $params[] = $studentId;
        } elseif ($currentUser && ($currentUser['role'] ?? '') === 'teacher') {
            $assignedGrade = trim((string)($currentUser['assigned_grade'] ?? ''));
            $assignedSection = trim((string)($currentUser['assigned_section'] ?? ''));

            if ($assignedGrade !== '' && $assignedGrade !== 'all') {
                $sql .= " AND s.grade = ?";
                $params[] = $assignedGrade;
            }
            if ($assignedSection !== '' && $assignedSection !== 'all') {
                $sql .= " AND s.section = ?";
                $params[] = $assignedSection;
            }
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT 200";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "records" => $records]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

<?php
require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'POST') {
        // Record a single recitation result
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;

        if (!$input || !isset($input['student_id'])) {
            echo json_encode(["status" => "error", "message" => "student_id required"]);
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

        $sql = "SELECT r.*, s.name AS student_name
                FROM recitation_records r
                JOIN students s ON s.id = r.student_id";
        $params = [];

        if ($studentId) {
            $sql .= " WHERE r.student_id = ?";
            $params[] = $studentId;
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

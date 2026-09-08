<?php
require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        // Return all students from DB
        $stmt = $pdo->query(
            "SELECT id, name, grade, section, recitations, total_score AS totalScore, photo
             FROM students ORDER BY grade, name"
        );
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "students" => $students]);

    } else if ($method === 'POST') {
        // Add or update a student (upsert)
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!$input && !empty($_POST)) {
            $input = $_POST;
        }

        if (!$input || !isset($input['id'], $input['name'], $input['grade'])) {
            echo json_encode(["status" => "error", "message" => "id, name, and grade required"]);
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
            $input['grade'],
            $input['section']      ?? 'A',
            $input['recitations']  ?? 0,
            $input['total_score']  ?? $input['totalScore'] ?? 0,
            $input['photo']        ?? null
        ]);

        echo json_encode(["status" => "success", "message" => "Student saved"]);

    } else if ($method === 'DELETE') {
        // Remove a student by ID
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['id'])) {
            echo json_encode(["status" => "error", "message" => "id required"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$input['id']]);

        echo json_encode(["status" => "success", "message" => "Student removed"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

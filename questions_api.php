<?php
require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    // Dynamic database migration for type column
    try {
        if (!db_column_exists($pdo, 'questions', 'type')) {
            $colType = is_sqlite() ? "TEXT DEFAULT 'multiple-choice'" : "VARCHAR(50) DEFAULT 'multiple-choice'";
            $pdo->exec("ALTER TABLE questions ADD COLUMN type $colType");
        }
    } catch (Exception $e) { /* ignore */ }

    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM questions ORDER BY grade, topic");
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
        
        $stmt = $pdo->prepare("INSERT INTO questions (grade, topic, difficulty, question_text, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$input['grade'], $input['topic'], $input['difficulty'], $input['text'], $input['type'] ?? 'multiple-choice']);
        
        if (!empty($input['grade']) && !empty($input['topic'])) {
            try {
                $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
                $stmtTCheck->execute([$input['grade'], $input['topic']]);
                if ($stmtTCheck->fetchColumn() == 0) {
                    $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
                    $stmtTIns->execute([$input['grade'], $input['topic']]);
                }
            } catch(Exception $te) {}
        }

        echo json_encode(["status" => "success", "message" => "Question added", "id" => $pdo->lastInsertId()]);
    }
    else if ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) throw new Exception("Invalid JSON or missing ID");
        
        $stmt = $pdo->prepare("UPDATE questions SET difficulty = ?, question_text = ?, type = ? WHERE id = ?");
        $stmt->execute([$input['difficulty'], $input['text'], $input['type'] ?? 'multiple-choice', $input['id']]);
        
        echo json_encode(["status" => "success", "message" => "Question updated"]);
    }
    else if ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['id'])) throw new Exception("Invalid JSON or missing ID");
        
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(["status" => "success", "message" => "Question deleted"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

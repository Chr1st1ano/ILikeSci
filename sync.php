<?php
require 'db.php';
header("Content-Type: application/json");

// Handle CORS Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Save backup (Cloud Backup)
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['state'])) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON payload"]);
        exit;
    }
    
    $jsonString = json_encode($data['state']);
    
    $stmt = $pdo->prepare("REPLACE INTO backups (id, state_json, last_updated) VALUES (1, :state, NOW())");
    
    if ($stmt->execute([':state' => $jsonString])) {
        // --- Populate Relational Tables ---
        try {
            $pdo->beginTransaction();
            
            // 1. Sync Students safely without triggering ON DELETE CASCADE unnecessarily
            $studentIds = [];
            $stmtStudent = $pdo->prepare("INSERT INTO students (id, name, grade, section, recitations, total_score, photo) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), grade=VALUES(grade), section=VALUES(section), recitations=VALUES(recitations), total_score=VALUES(total_score), photo=VALUES(photo)");
            foreach ($data['state']['students'] as $student) {
                $studentIds[] = $student['id'];
                $stmtStudent->execute([$student['id'], $student['name'], $student['grade'], $student['section'] ?? 'A', $student['recitations'], $student['totalScore'], $student['photo'] ?? null]);
            }

            // Remove students that were deleted from the frontend state
            if (count($studentIds) > 0) {
                $inQuery = implode(',', array_fill(0, count($studentIds), '?'));
                $stmtDelete = $pdo->prepare("DELETE FROM students WHERE id NOT IN ($inQuery)");
                $stmtDelete->execute($studentIds);
            } else {
                $pdo->exec("DELETE FROM students");
            }

            // Clear topics and questions (safe to wipe as they have no cascading foreign keys)
            $pdo->exec("DELETE FROM topics");
            $pdo->exec("DELETE FROM questions");

            // 2. Sync Topics
            $stmtTopic = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
            foreach ($data['state']['topics'] as $grade => $topicsList) {
                foreach ($topicsList as $topic) {
                    $stmtTopic->execute([$grade, $topic]);
                }
            }

            // 3. Sync Questions
            $stmtQuestion = $pdo->prepare("INSERT INTO questions (grade, topic, difficulty, question_text) VALUES (?, ?, ?, ?)");
            foreach ($data['state']['questions'] as $q) {
                $stmtQuestion->execute([$q['grade'], $q['topic'], $q['difficulty'], $q['text']]);
            }

            // 4. Sync Multimedia Files (if provided)
            if (isset($data['multimedia']) && is_array($data['multimedia'])) {
                $stmtMedia = $pdo->prepare("INSERT INTO multimedia_files (id, category, name, type, size, data) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE data=VALUES(data)");
                foreach ($data['multimedia'] as $media) {
                    $stmtMedia->execute([$media['id'], $media['category'], $media['name'], $media['type'], $media['size'], $media['data']]);
                }
            }

            $pdo->commit();
            echo json_encode(["status" => "success", "message" => "State backed up to MySQL and relational tables synced successfully!"]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Backup saved, but failed to sync relational tables: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to save backup"]);
    }

} else if ($method === 'GET') {
    // Load backup (Restore from Cloud)
    try {
        $stateData = [
            "students" => [],
            "topics" => [],
            "questions" => []
        ];
        
        // 1. Get Students
        $stmtS = $pdo->query("SELECT * FROM students");
        while ($row = $stmtS->fetch(PDO::FETCH_ASSOC)) {
            $stateData["students"][] = [
                "id" => (int)$row['id'],
                "name" => $row['name'],
                "grade" => $row['grade'],
                "section" => $row['section'] ?? 'A',
                "recitations" => (int)$row['recitations'],
                "totalScore" => (int)$row['total_score']
            ];
        }
        
        // 2. Get Topics
        $stmtT = $pdo->query("SELECT * FROM topics");
        while ($row = $stmtT->fetch(PDO::FETCH_ASSOC)) {
            $grade = $row['grade'];
            // Normalize "Grade 4" to "4" for the state map
            if (strpos($grade, 'Grade ') !== false) {
                $grade = str_replace('Grade ', '', $grade);
            }
            if (!isset($stateData["topics"][$grade])) {
                $stateData["topics"][$grade] = [];
            }
            $stateData["topics"][$grade][] = $row['topic_name'];
        }
        
        // 3. Get Questions
        $stmtQ = $pdo->query("SELECT * FROM questions");
        while ($row = $stmtQ->fetch(PDO::FETCH_ASSOC)) {
            $grade = $row['grade'];
            if (strpos($grade, 'Grade ') !== false) {
                $grade = str_replace('Grade ', '', $grade);
            }
            $stateData["questions"][] = [
                "grade" => $grade,
                "topic" => $row['topic'],
                "difficulty" => $row['difficulty'],
                "text" => $row['question_text']
            ];
        }
        
        // Fetch remaining state stuff from backups if available
        $stmt = $pdo->query("SELECT state_json FROM backups WHERE id = 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $backupData = json_decode($row['state_json'], true);
            // Merge defaults
            $stateData["currentView"] = $backupData["currentView"] ?? 'dashboard';
            $stateData["pointsMap"] = $backupData["pointsMap"] ?? ['Easy'=>1, 'Medium'=>3, 'Hard'=>5];
            $stateData["assessment"] = $backupData["assessment"] ?? [];
        } else {
            $stateData["currentView"] = 'dashboard';
            $stateData["pointsMap"] = ['Easy'=>1, 'Medium'=>3, 'Hard'=>5];
            $stateData["assessment"] = [
                'grade' => '4', 'topic' => '', 'difficulty' => 'Medium', 'studentId' => null, 'activeQuestion' => ''
            ];
        }
        
        echo json_encode(["status" => "success", "state" => $stateData]);
        
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Failed to load database: " . $e->getMessage()]);
    }
}
?>

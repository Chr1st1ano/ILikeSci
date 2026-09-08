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

if ($method === 'GET') {
    // Get slides for a specific lesson (must check this BEFORE generic GET)
    if (isset($_GET['slides'])) {
        $lessonId = $_GET['slides'];

        try {
            $stmt = $pdo->prepare("SELECT * FROM lesson_slides WHERE curriculum_lesson_id = ? ORDER BY slide_number");
            $stmt->execute([$lessonId]);

            $slides = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $slides[] = [
                    'id' => $row['id'],
                    'slide_number' => $row['slide_number'],
                    'title' => $row['title'],
                    'content' => $row['content'],
                    'slide_type' => $row['slide_type'],
                    'media_type' => $row['media_type'] ?? null,
                    'media_url' => $row['media_url'] ?? null
                ];
            }

            echo json_encode(["status" => "success", "slides" => $slides]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }

    } else {
        // Get lessons with optional filters
        $grade = $_GET['grade'] ?? null;
        $quarter = $_GET['quarter'] ?? null;

        try {
            $sql = "SELECT * FROM curriculum_lessons WHERE 1=1";
            $params = [];

            if ($grade) {
                $sql .= " AND grade = ?";
                $params[] = $grade;
            }
            if ($quarter) {
                $sql .= " AND quarter = ?";
                $params[] = $quarter;
            }

            $sql .= " ORDER BY quarter, lesson_number";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $lessons = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lessons[] = [
                    'id' => $row['id'],
                    'grade' => $row['grade'],
                    'quarter' => $row['quarter'],
                    'lesson_number' => $row['lesson_number'],
                    'topic' => $row['topic'],
                    'content' => $row['content'],
                    'objectives' => json_decode($row['objectives'], true) ?: [],
                    'questions' => json_decode($row['questions'], true) ?: []
                ];
            }

            echo json_encode(["status" => "success", "lessons" => $lessons]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

} else if ($method === 'POST') {
    // Create a new lesson or lesson slides
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        if (isset($input['action']) && $input['action'] === 'create_lesson') {
            // Create a new curriculum lesson and its slides
            $grade = $input['grade'] ?? '4';
            $quarter = $input['quarter'] ?? '1';
            $lessonNumber = $input['lesson_number'] ?? '1';
            $topic = $input['topic'] ?? '';
            $objectives = $input['objectives'] ?? [];
            $slides = $input['slides'] ?? [];
            
            if (empty($topic)) {
                throw new Exception("Topic is required.");
            }
            
            $pdo->beginTransaction();
            
            // Build content preview from slides
            $contentParts = [];
            foreach ($slides as $slide) {
                $contentParts[] = ($slide['title'] ?? '') . "\n" . ($slide['content'] ?? '');
            }
            $content = implode("\n\n", $contentParts);
            
            // Insert lesson
            $stmtLesson = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives, questions) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtLesson->execute([
                $grade,
                $quarter,
                $lessonNumber,
                $topic,
                $content,
                json_encode($objectives),
                json_encode([])
            ]);
            $lessonId = $pdo->lastInsertId();

            if (!empty($topic) && !empty($grade)) {
                try {
                    $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
                    $stmtTCheck->execute([$grade, $topic]);
                    if ($stmtTCheck->fetchColumn() == 0) {
                        $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
                        $stmtTIns->execute([$grade, $topic]);
                    }
                } catch(Exception $te) {}
            }
            
            // Clear any orphaned slides for this lesson ID before inserting
            $pdo->prepare("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?")->execute([$lessonId]);

            // Insert slides
            $stmtSlide = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($slides as $index => $slide) {
                $stmtSlide->execute([
                    $lessonId,
                    $index + 1,
                    $slide['title'] ?? 'Slide ' . ($index + 1),
                    $slide['content'] ?? '',
                    $slide['type'] ?? $slide['slide_type'] ?? 'content',
                    $slide['mediaType'] ?? $slide['media_type'] ?? null,
                    $slide['mediaUrl'] ?? $slide['media_url'] ?? null
                ]);
            }
            
            $pdo->commit();
            echo json_encode(["status" => "success", "message" => "Lesson created successfully", "lesson_id" => $lessonId]);
        } else if (isset($input['slides'])) {
            // Create/update lesson slides for an existing curriculum lesson
            $lessonId = $input['curriculum_lesson_id'];
            
            $pdo->beginTransaction();
            
            // Delete existing slides for this lesson
            $pdo->prepare("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?")->execute([$lessonId]);
            
            // Insert new slides
            $stmt = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($input['slides'] as $index => $slide) {
                $stmt->execute([
                    $lessonId,
                    $index + 1,
                    $slide['title'],
                    $slide['content'],
                    $slide['type'] ?? $slide['slide_type'] ?? 'content',
                    $slide['mediaType'] ?? $slide['media_type'] ?? null,
                    $slide['mediaUrl'] ?? $slide['media_url'] ?? null
                ]);
            }
            
            $pdo->commit();
            echo json_encode(["status" => "success", "message" => "Lesson slides created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid request format"]);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else if ($method === 'PUT') {
    // Edit/Update an existing lesson and its slides
    $input = json_decode(file_get_contents('php://input'), true);
    $lessonId = $input['id'] ?? null;
    
    if (!$lessonId) {
        echo json_encode(["status" => "error", "message" => "Missing lesson ID"]);
        exit;
    }
    
    try {
        $grade = $input['grade'] ?? '4';
        $quarter = $input['quarter'] ?? '1';
        $lessonNumber = $input['lesson_number'] ?? '1';
        $topic = $input['topic'] ?? '';
        $objectives = $input['objectives'] ?? [];
        $slides = $input['slides'] ?? [];
        
        if (empty($topic)) {
            throw new Exception("Topic is required.");
        }
        
        $pdo->beginTransaction();
        
        // Build content preview from slides
        $contentParts = [];
        foreach ($slides as $slide) {
            $contentParts[] = ($slide['title'] ?? '') . "\n" . ($slide['content'] ?? '');
        }
        $content = implode("\n\n", $contentParts);
        
        // Update lesson
        $stmtLesson = $pdo->prepare("UPDATE curriculum_lessons SET grade = ?, quarter = ?, lesson_number = ?, topic = ?, content = ?, objectives = ? WHERE id = ?");
        $stmtLesson->execute([
            $grade,
            $quarter,
            $lessonNumber,
            $topic,
            $content,
            is_string($objectives) ? $objectives : json_encode($objectives),
            $lessonId
        ]);
        
        // Delete existing slides
        $pdo->prepare("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?")->execute([$lessonId]);
        
        // Insert new slides
        $stmtSlide = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type, media_type, media_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($slides as $index => $slide) {
            $stmtSlide->execute([
                $lessonId,
                $index + 1,
                $slide['title'] ?? 'Slide ' . ($index + 1),
                $slide['content'] ?? '',
                $slide['type'] ?? $slide['slide_type'] ?? 'content',
                $slide['mediaType'] ?? $slide['media_type'] ?? null,
                $slide['mediaUrl'] ?? $slide['media_url'] ?? null
            ]);
        }
        
        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Lesson updated successfully"]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else if ($method === 'DELETE') {
    // Delete a lesson and all of its slides
    $input = json_decode(file_get_contents('php://input'), true);
    $lessonId = $input['id'] ?? null;
    
    if (!$lessonId) {
        echo json_encode(["status" => "error", "message" => "Missing lesson ID"]);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete lesson (slides cascade delete due to schema)
        $stmt = $pdo->prepare("DELETE FROM curriculum_lessons WHERE id = ?");
        $stmt->execute([$lessonId]);
        
        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Lesson deleted successfully"]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>

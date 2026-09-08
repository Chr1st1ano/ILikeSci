<?php
require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle CORS Preflight
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    // Import curriculum from extracted texts
    $extractedDir = __DIR__ . '/extracted_texts_q1_science';
    $lessons = [];
    
    if (is_dir($extractedDir)) {
        $files = scandir($extractedDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
                $filePath = $extractedDir . '/' . $file;
                $content = file_get_contents($filePath);
                $lessonData = parseLessonFile($content, $file);
                if ($lessonData) {
                    $lessons[] = $lessonData;
                }
            }
        }
    }
    
    // Save to database
    try {
        $pdo->beginTransaction();
        
        // Clear existing curriculum lessons
        $pdo->exec("DELETE FROM curriculum_lessons");
        
        foreach ($lessons as $lesson) {
            $stmt = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives, questions) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $lesson['grade'],
                $lesson['quarter'],
                $lesson['lesson_number'],
                $lesson['topic'],
                $lesson['content'],
                json_encode($lesson['objectives']),
                json_encode($lesson['questions'])
            ]);
        }
        
        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Imported " . count($lessons) . " lessons successfully", "lessons" => $lessons]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Import failed: " . $e->getMessage()]);
    }
} else if ($method === 'GET') {
    // Get all curriculum lessons
    try {
        $stmt = $pdo->query("SELECT * FROM curriculum_lessons ORDER BY grade, quarter, lesson_number");
        $lessons = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lessons[] = [
                'id' => $row['id'],
                'grade' => $row['grade'],
                'quarter' => $row['quarter'],
                'lesson_number' => $row['lesson_number'],
                'topic' => $row['topic'],
                'content' => $row['content'],
                'objectives' => json_decode($row['objectives'], true),
                'questions' => json_decode($row['questions'], true)
            ];
        }
        echo json_encode(["status" => "success", "lessons" => $lessons]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Failed to fetch lessons: " . $e->getMessage()]);
    }
}

function parseLessonFile($content, $filename) {
    $lesson = [
        'grade' => '4',
        'quarter' => '1',
        'lesson_number' => '',
        'topic' => '',
        'content' => '',
        'objectives' => [],
        'questions' => []
    ];
    
    // Extract lesson number from filename
    if (preg_match('/Lesson(\d+)/', $filename, $matches)) {
        $lesson['lesson_number'] = $matches[1];
    }
    
    // Extract quarter
    if (preg_match('/Quarter\s+(\d+)/i', $content, $matches)) {
        $lesson['quarter'] = $matches[1];
    }
    
    // Extract topic/content from Content Standards section
    if (preg_match('/Content Standards\s+(.+?)(?=\n\n|\nB\.|Performance)/is', $content, $matches)) {
        $lesson['topic'] = trim($matches[1]);
    }
    
    // Extract learning objectives
    if (preg_match('/Learning Competencies?\s+(.+?)(?=\n\n|\nD\.|Content)/is', $content, $matches)) {
        $objectivesText = trim($matches[1]);
        $lesson['objectives'] = explode("\n", preg_replace('/^\d+\./m', '', $objectivesText));
        $lesson['objectives'] = array_filter(array_map('trim', $lesson['objectives']));
    }
    
    // Extract multiple choice questions
    if (preg_match('/Multiple Choice[:\s]+(.+?)(?=\n\nANSWER KEY|\n2\. Homework)/is', $content, $matches)) {
        $questionsText = $matches[1];
        preg_match_all('/(\d+)\.\s+(.+?)\n\s*a\.\s+(.+?)\n\s*b\.\s+(.+?)\n\s*c\.\s+(.+?)\n\s*d\.\s+(.+?)(?=\n\n|\n\d+\.)/is', $questionsText, $questionMatches, PREG_SET_ORDER);
        
        foreach ($questionMatches as $q) {
            $lesson['questions'][] = [
                'type' => 'multiple_choice',
                'question' => trim($q[2]),
                'options' => [
                    'a' => trim($q[3]),
                    'b' => trim($q[4]),
                    'c' => trim($q[5]),
                    'd' => trim($q[6])
                ]
            ];
        }
    }
    
    // Store full content for lesson slides
    $lesson['content'] = substr($content, 0, 5000); // Limit content length
    
    return $lesson;
}
?>

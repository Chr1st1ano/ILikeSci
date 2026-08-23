<?php
require_once 'db.php';

// Get all lesson plan files
$lessonDir = 'extracted_texts_q1_science/';
$files = glob($lessonDir . '*.txt');

$importedCount = 0;
$errors = [];

foreach ($files as $filePath) {
    $fileName = basename($filePath);
    $lessonContent = file_get_contents($filePath);
    
    if (!$lessonContent) {
        $errors[] = "Could not read file: $fileName";
        continue;
    }
    
    // Parse lesson info from filename
    // Format: Q1_LE_Science4_Lesson2-Week-2.txt or Q1_WS_Science4_Lesson-1-Week-1.txt
    $grade = '4';
    $quarter = '1';
    $lessonNumber = '';
    $topic = '';
    $lessonType = '';
    
    // Extract lesson type (LE = Lesson Exemplar, WS = Worksheet)
    if (strpos($fileName, 'LE_') !== false) {
        $lessonType = 'Lesson Exemplar';
    } elseif (strpos($fileName, 'WS_') !== false) {
        $lessonType = 'Worksheet';
    }
    
    // Extract lesson number from filename
    if (preg_match('/Lesson(\d+)/i', $fileName, $matches)) {
        $lessonNumber = $matches[1];
    }
    
    // Extract week number
    $weekNumber = '';
    if (preg_match('/Week[-\s]?(\d+)/i', $fileName, $matches)) {
        $weekNumber = $matches[1];
    }
    
    // Parse content to extract topic
    $topic = parseTopicFromContent($lessonContent, $lessonType, $lessonNumber);
    
    // Parse objectives
    $objectives = parseObjectives($lessonContent);
    
    // Parse content into slides
    $contentSections = parseContentSlides($lessonContent, $lessonType, $lessonNumber);
    
    // Check if lesson already exists
    $checkStmt = $pdo->prepare("SELECT id FROM curriculum_lessons WHERE grade = ? AND quarter = ? AND lesson_number = ? AND topic = ?");
    $checkStmt->execute([$grade, $quarter, $lessonNumber, $topic]);
    
    if ($checkStmt->rowCount() > 0) {
        $errors[] = "Lesson already exists: $fileName (Grade $grade, Q$quarter, Lesson $lessonNumber)";
        continue;
    }
    
    // Insert into curriculum_lessons table
    $stmt = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives, questions) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $grade,
        $quarter,
        $lessonNumber,
        $topic,
        $lessonContent,
        json_encode($objectives),
        json_encode([]) // Empty questions array for now
    ]);
    
    $curriculumLessonId = $pdo->lastInsertId();
    
    // Insert slides into lesson_slides table
    foreach ($contentSections as $index => $section) {
        $stmt = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $curriculumLessonId,
            $index + 1,
            $section['title'],
            $section['content'],
            'content'
        ]);
    }
    
    $importedCount++;
}

echo "<h1>Lesson Import Complete!</h1>";
echo "<p><strong>Total Files Processed:</strong> " . count($files) . "</p>";
echo "<p><strong>Successfully Imported:</strong> $importedCount</p>";

if (!empty($errors)) {
    echo "<h2>Errors/Skipped:</h2>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<p><a href='lessons.html'>Go to Lessons Page</a></p>";

// Helper function to parse topic from content
function parseTopicFromContent($content, $lessonType, $lessonNumber) {
    // Try to find topic in content
    if (preg_match('/Lesson Title\/ Topic:\s*(.+)/i', $content, $matches)) {
        return trim($matches[1]);
    }
    
    if (preg_match('/Content\s+(.+)/i', $content, $matches)) {
        return trim($matches[1]);
    }
    
    // Fallback based on lesson type and number
    if ($lessonType == 'Lesson Exemplar') {
        $topics = [
            '1' => 'Famous Filipino/Foreign scientists and their inventions',
            '2' => 'Importance and impact of inventions on everyday life',
            '4' => 'Chemical properties of materials',
            '6' => 'Physical and chemical changes',
            '8' => 'Energy and its forms'
        ];
        return isset($topics[$lessonNumber]) ? $topics[$lessonNumber] : "Lesson $lessonNumber - Science Grade 4";
    } else {
        // Worksheet topics
        return "Worksheet Activity - Lesson $lessonNumber (Week $weekNumber)";
    }
}

// Helper function to parse objectives
function parseObjectives($content) {
    $objectives = [];
    
    // Look for objective sections
    if (preg_match_all('/Objective\(s\):(.+?)(?=I\.|II\.|III\.|IV\.|V\.|$)/s', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $lines = preg_split('/\n/', trim($match));
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && !preg_match('/^(At the end|a\.|b\.)/i', $line)) {
                    $objectives[] = preg_replace('/^[a-z]\.\s*/', '', $line);
                }
            }
        }
    }
    
    // Look for learning competency
    if (preg_match('/Learning Competency\s*(.+)/i', $content, $matches)) {
        $objectives[] = trim($matches[1]);
    }
    
    if (empty($objectives)) {
        $objectives[] = "Complete lesson activities and assessments";
    }
    
    return array_unique($objectives);
}

// Helper function to parse content into slides
function parseContentSlides($content, $lessonType, $lessonNumber) {
    $slides = [];
    
    // Add overview slide
    $slides[] = [
        'title' => 'Lesson Overview',
        'content' => extractOverview($content, $lessonType)
    ];
    
    // Add objectives slide
    $slides[] = [
        'title' => 'Learning Objectives',
        'content' => extractObjectivesContent($content)
    ];
    
    // Add activities based on lesson type
    if ($lessonType == 'Lesson Exemplar') {
        // Parse lesson procedure sections
        if (preg_match_all('/(Activating Prior Knowledge|Developing and Deepening Understanding|Making Generalizations|Evaluating Learning)(.+?)(?=\n[A-Z]|\n[IV]+\.\s|$)/s', $content, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $slides[] = [
                    'title' => trim($matches[1][$i]),
                    'content' => cleanContent(substr($matches[2][$i], 0, 500))
                ];
            }
        }
    } else {
        // Worksheet activities
        $slides[] = [
            'title' => 'Worksheet Activities',
            'content' => 'Complete all activities in the worksheet. Follow instructions carefully and submit your answers to your teacher.'
        ];
    }
    
    // Add assessment slide
    $slides[] = [
        'title' => 'Assessment',
        'content' => 'Complete the assessment section to check your understanding of the lesson.'
    ];
    
    return $slides;
}

function extractOverview($content, $lessonType) {
    if ($lessonType == 'Lesson Exemplar') {
        if (preg_match('/Content Standards\s*(.+)/i', $content, $matches)) {
            return trim($matches[1]);
        }
        return "This is a lesson exemplar for Grade 4 Science covering the MATATAG K to 10 Curriculum.";
    }
    return "This is a learning activity sheet for Grade 4 Science. Complete all activities independently or with a partner.";
}

function extractObjectivesContent($content) {
    if (preg_match('/Objective\(s\):(.+?)(?=I\.|II\.|III\.|IV\.|V\.|$)/s', $content, $matches)) {
        return trim($matches[1]);
    }
    return "Complete the lesson activities and demonstrate understanding of the topic.";
}

function cleanContent($text) {
    $text = preg_replace('/\s+/', ' ', $text);
    $text = strip_tags($text);
    return substr(trim($text), 0, 500);
}
?>

<?php
/**
 * Bulk PPTX Importer for ILikeSci
 * Scans directories for all .pptx files and processes them into database & high-res slide images.
 */

require 'c:/Games/xampp/htdocs/ILikeSci/db.php';

$uploadBaseDir = __DIR__ . '/uploads/pptx/';
$slidesBaseDir = __DIR__ . '/uploads/pptx/slides/';
if (!is_dir($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);
if (!is_dir($slidesBaseDir)) mkdir($slidesBaseDir, 0777, true);

// Find Python executable
$pythonCmd = 'C:\Users\08oyo\AppData\Local\Python\bin\python.exe';
if (!file_exists($pythonCmd)) {
    $pythonCmd = 'python';
}

echo "=== Starting Bulk PPTX Import ===\n";
echo "Using Python: $pythonCmd\n\n";

// Search directories
$searchDirs = [
    __DIR__,
    __DIR__ . '/Curriculums',
];

$allPptxFiles = [];
foreach ($searchDirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'pptx') {
            $path = $file->getPathname();
            // Skip already uploaded files inside uploads/pptx directory to avoid duplication
            if (strpos($path, 'uploads\\pptx') !== false || strpos($path, 'uploads/pptx') !== false) {
                continue;
            }
            $allPptxFiles[] = $path;
        }
    }
}

// Remove duplicates by file basename
$uniqueFiles = [];
foreach ($allPptxFiles as $f) {
    $bn = basename($f);
    if (!isset($uniqueFiles[$bn])) {
        $uniqueFiles[$bn] = $f;
    }
}

echo "Found " . count($uniqueFiles) . " unique PowerPoint files to import.\n\n";

$importedCount = 0;
$skippedCount = 0;

foreach ($uniqueFiles as $originalName => $fullPath) {
    echo "Processing: {$originalName}...\n";

    // Auto-detect grade & quarter from filename
    $grade = '4';
    $quarter = '1';
    if (preg_match('/G(\d+)/i', $originalName, $gm)) $grade = $gm[1];
    elseif (preg_match('/Grade\s*(\d+)/i', $originalName, $gm)) $grade = $gm[1];

    if (preg_match('/Q(\d+)/i', $originalName, $qm)) $quarter = $qm[1];
    elseif (preg_match('/Quarter\s*(\d+)/i', $originalName, $qm)) $quarter = $qm[1];

    $topic = preg_replace('/\.(pptx?|ppt)$/i', '', $originalName);
    $topic = str_replace(['_', '-'], ' ', $topic);
    $topic = preg_replace('/\s+/', ' ', $topic);
    $topic = trim($topic);

    // Check if already imported by original_name
    $stmtCheck = $pdo->prepare("SELECT id FROM pptx_uploads WHERE original_name = ?");
    $stmtCheck->execute([$originalName]);
    if ($stmtCheck->fetchColumn()) {
        echo "  [SKIPPED] Already in database: {$originalName}\n";
        $skippedCount++;
        continue;
    }

    // Copy PPTX to uploads folder
    $savedName = 'pptx_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
    $savedPath = $uploadBaseDir . $savedName;
    copy($fullPath, $savedPath);

    // Convert slides using python
    $uploadTimestamp = microtime(true) * 10000;
    $slidesDirName = "slides_" . intval($uploadTimestamp) . "/";
    $slidesDirFull = $slidesBaseDir . $slidesDirName;
    $slidesDirRelative = "uploads/pptx/slides/{$slidesDirName}";
    $hasImages = false;
    $slideCount = 0;

    $scriptPath = __DIR__ . '/pptx_to_images.py';
    $cmd = "\"$pythonCmd\" \"$scriptPath\" \"$savedPath\" \"$slidesDirFull\" 2>&1";
    $output = shell_exec($cmd);

    $lines = explode("\n", trim($output));
    $jsonLine = end($lines);
    $result = json_decode($jsonLine, true);

    if ($result && $result['status'] === 'success') {
        $hasImages = true;
        $slideCount = $result['total_slides'];
    }

    // Create curriculum lesson
    $stmtLessonNum = $pdo->prepare("SELECT COALESCE(MAX(CAST(lesson_number AS UNSIGNED)),0)+1 FROM curriculum_lessons WHERE grade=? AND quarter=?");
    $stmtLessonNum->execute([$grade, $quarter]);
    $lessonNum = $stmtLessonNum->fetchColumn();

    $stmtLesson = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtLesson->execute([$grade, $quarter, $lessonNum, $topic, "PowerPoint Lesson: {$originalName}", '[]']);
    $curriculumLessonId = $pdo->lastInsertId();

    // Register topic in topics table
    try {
        $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
        $stmtTCheck->execute([$grade, $topic]);
        if ($stmtTCheck->fetchColumn() == 0) {
            $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
            $stmtTIns->execute([$grade, $topic]);
        }
    } catch(Exception $te) {}

    // Save upload record
    $stmtUpload = $pdo->prepare("INSERT INTO pptx_uploads (filename, original_name, grade, quarter, topic, slide_count, curriculum_lesson_id, slides_dir, has_images, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtUpload->execute([
        $savedName, $originalName, $grade, $quarter, $topic,
        $slideCount, $curriculumLessonId,
        $hasImages ? $slidesDirRelative : '',
        $hasImages ? 1 : 0,
        'admin'
    ]);

    echo "  [SUCCESS] Imported {$originalName} ({$slideCount} slides, Grade {$grade} Q{$quarter})\n";
    $importedCount++;
}

echo "\n=== Import Complete ===";
echo "\nTotal Imported: {$importedCount}";
echo "\nTotal Skipped: {$skippedCount}\n";

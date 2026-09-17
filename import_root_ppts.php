<?php
/**
 * Import root PowerPoint presentations into ILikeSci
 * Specifically imports PPT_SCIENCE_G4_Q3_W4.pptx with high-resolution slide images.
 */

require_once __DIR__ . '/db.php';

$uploadDir = __DIR__ . '/uploads/pptx/';
$slidesBaseDir = __DIR__ . '/uploads/pptx/slides/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
if (!is_dir($slidesBaseDir)) mkdir($slidesBaseDir, 0777, true);

$sourcePptx = __DIR__ . '/PPT_SCIENCE_G4_Q3_W4.pptx';
if (!file_exists($sourcePptx)) {
    echo "PPT_SCIENCE_G4_Q3_W4.pptx not found in root.\n";
    exit(1);
}

$originalName = 'PPT_SCIENCE_G4_Q3_W4.pptx';
$grade = '4';
$quarter = '3';
$topic = 'Science Grade 4 Quarter 3 Week 4';

// Check if already in pptx_uploads
$stmtCheck = $pdo->prepare("SELECT id FROM pptx_uploads WHERE original_name = ?");
$stmtCheck->execute([$originalName]);
$existingId = $stmtCheck->fetchColumn();

// Target slide directory
$slidesDirName = 'slides_PPT_SCIENCE_G4_Q3_W4/';
$slidesDirFull = $slidesBaseDir . $slidesDirName;
$slidesDirRelative = 'uploads/pptx/slides/' . $slidesDirName;

if (!is_dir($slidesDirFull)) {
    mkdir($slidesDirFull, 0777, true);
}

// Copy from previous test_preview if already rendered, or run python conversion
$previewDir = $slidesBaseDir . 'test_preview/';
$slideCount = 0;

if (is_dir($previewDir) && count(glob($previewDir . '*.png')) >= 80) {
    echo "Copying rendered slides from preview directory...\n";
    $files = glob($previewDir . '*.png');
    foreach ($files as $f) {
        copy($f, $slidesDirFull . basename($f));
    }
    $slideCount = count($files);
} else {
    echo "Converting slides using Python...\n";
    $pythonCmd = 'py';
    $script = __DIR__ . '/pptx_to_images.py';
    $cmd = escapeshellarg($pythonCmd) . " " . escapeshellarg($script) . " " . escapeshellarg($sourcePptx) . " " . escapeshellarg($slidesDirFull) . " 2>&1";
    $out = shell_exec($cmd);
    $lines = explode("\n", trim($out));
    $res = json_decode(end($lines), true);
    if ($res && $res['status'] === 'success') {
        $slideCount = $res['total_slides'];
    } else {
        $slideCount = count(glob($slidesDirFull . '*.png'));
    }
}

echo "Slide count: {$slideCount}\n";

// Copy PPTX to uploads folder
$savedName = 'pptx_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
$savedPath = $uploadDir . $savedName;
copy($sourcePptx, $savedPath);

// Register topic in topics table
try {
    $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
    $stmtTCheck->execute([$grade, $topic]);
    if ($stmtTCheck->fetchColumn() == 0) {
        $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");
        $stmtTIns->execute([$grade, $topic]);
    }
} catch(Exception $te) {}

// Create or update curriculum_lessons
$castType = is_sqlite() ? "INTEGER" : "UNSIGNED";
$stmtLessonNum = $pdo->prepare("SELECT COALESCE(MAX(CAST(lesson_number AS $castType)),0)+1 FROM curriculum_lessons WHERE grade=? AND quarter=?");
$stmtLessonNum->execute([$grade, $quarter]);
$lessonNum = $stmtLessonNum->fetchColumn();

$stmtLesson = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives) VALUES (?, ?, ?, ?, ?, ?)");
$stmtLesson->execute([$grade, $quarter, $lessonNum, $topic, "PowerPoint Presentation: {$originalName} (84 Visual Slides)", '[]']);
$curriculumLessonId = $pdo->lastInsertId();

// Create sample text slides
$stmtSlide = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type) VALUES (?, ?, ?, ?, ?)");
for ($i = 1; $i <= min(5, $slideCount); $i++) {
    $stmtSlide->execute([
        $curriculumLessonId,
        $i,
        "Slide {$i}: {$topic}",
        "Slide {$i} of {$slideCount} in presentation {$originalName}",
        'content'
    ]);
}

if ($existingId) {
    // Update existing record
    $stmtUp = $pdo->prepare("UPDATE pptx_uploads SET filename = ?, slide_count = ?, curriculum_lesson_id = ?, slides_dir = ?, has_images = 1 WHERE id = ?");
    $stmtUp->execute([$savedName, $slideCount, $curriculumLessonId, $slidesDirRelative, $existingId]);
    $uploadId = $existingId;
    echo "Updated existing presentation ID {$uploadId}!\n";
} else {
    // Insert new record
    $stmtUpload = $pdo->prepare("INSERT INTO pptx_uploads (filename, original_name, grade, quarter, topic, slide_count, curriculum_lesson_id, slides_dir, has_images, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'admin')");
    $stmtUpload->execute([
        $savedName,
        $originalName,
        $grade,
        $quarter,
        $topic,
        $slideCount,
        $curriculumLessonId,
        $slidesDirRelative
    ]);
    $uploadId = $pdo->lastInsertId();
    echo "Inserted new presentation ID {$uploadId}!\n";
}

echo "SUCCESS: Presentation {$originalName} imported! Can be opened at presenter.html?pptx={$uploadId}\n";
?>

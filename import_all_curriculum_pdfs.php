<?php
/**
 * ILikeSci — Master Curriculum PDF & Presentation Batch Importer
 * 
 * Automatically scans `pdfs/pdfs for importing and testing/` across Grades 3-6,
 * renders slide images at optimal resolution for low-end hardware, extracts slide text,
 * links to curriculum lessons, and records in `pptx_uploads` & `lesson_slides`.
 *
 * Can be run from CLI (`php import_all_curriculum_pdfs.php`) or accessed in browser.
 */

require_once __DIR__ . '/db.php';

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Curriculum Presentation Importer</title>";
    echo "<style>body{font-family:'Outfit',sans-serif;background:#0f172a;color:#f8fafc;padding:24px;line-height:1.5;}";
    echo ".log{background:#1e293b;padding:16px;border-radius:10px;font-family:monospace;margin:10px 0;max-height:600px;overflow-y:auto;}";
    echo ".success{color:#10b981;} .info{color:#38bdf8;} .warn{color:#f59e0b;} .err{color:#ef4444;}</style></head><body>";
    echo "<h1><i class='fa-solid fa-file-pdf'></i> ILikeSci Curriculum Presentation Importer</h1><div class='log'>";
}

function log_msg($msg, $type = 'info') {
    global $isCli;
    $time = date('H:i:s');
    if ($isCli) {
        $colors = [
            'success' => "\033[32m",
            'info'    => "\033[36m",
            'warn'    => "\033[33m",
            'err'     => "\033[31m",
            'reset'   => "\033[0m"
        ];
        $c = $colors[$type] ?? $colors['info'];
        echo "[$time] {$c}{$msg}{$colors['reset']}\n";
    } else {
        echo "<div class='{$type}'>[$time] " . htmlspecialchars($msg) . "</div>";
        flush();
    }
}

log_msg("Initializing Curriculum Presentation Importer...", "info");

// 1. Ensure directories exist
$uploadDir = __DIR__ . '/uploads/pptx/';
$slidesBaseDir = __DIR__ . '/uploads/pptx/slides/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
if (!is_dir($slidesBaseDir)) @mkdir($slidesBaseDir, 0777, true);

// 2. Ensure schema has file_type column
try {
    if (!db_column_exists($pdo, 'pptx_uploads', 'file_type')) {
        $colType = is_sqlite() ? "TEXT DEFAULT 'pptx'" : "VARCHAR(20) DEFAULT 'pptx'";
        $pdo->exec("ALTER TABLE pptx_uploads ADD COLUMN file_type $colType");
        log_msg("Added missing file_type column to pptx_uploads.", "info");
    }
} catch (Exception $e) {}

// 3. Find Python executable
$pythonCmd = '';
$pythonPaths = [
    __DIR__ . '/venv/bin/python3',
    __DIR__ . '/venv/bin/python',
    'C:\\Users\\08oyo\\AppData\\Local\\Python\\pythoncore-3.14-64\\python.exe',
    'C:\\Users\\08oyo\\AppData\\Local\\Python\\bin\\python.exe',
    'python3',
    'python',
    'py',
    'C:\\Games\\Python3.14\\python.exe',
    'C:\\Python3\\python.exe',
    'C:\\Python\\python.exe'
];
foreach ($pythonPaths as $pp) {
    if ((strpos($pp, '/') !== false || strpos($pp, '\\') !== false) && !file_exists($pp)) {
        continue;
    }
    $testOut = @shell_exec(escapeshellarg($pp) . " --version 2>&1");
    if ($testOut && preg_match('/^python\s+\d/i', trim($testOut))) {
        $pythonCmd = $pp;
        break;
    }
}

if (!$pythonCmd) {
    log_msg("Python not found in system paths! Cannot convert slide images.", "err");
    if (!$isCli) echo "</div></body></html>";
    exit(1);
}
log_msg("Using Python: $pythonCmd", "info");

// 4. Curriculum unit mapping dictionary
$curriculumMap = [
    // Grade 3
    'Basic Needs of Living Things.pdf' => [
        'grade' => '3', 'quarter' => '2',
        'topic' => 'Basic Needs of Living Things'
    ],
    'Environmental Stewardship.pdf' => [
        'grade' => '3', 'quarter' => '2',
        'topic' => 'Environmental Stewardship and Conservation'
    ],
    'GRADE 3 • LESSON 2.pdf' => [
        'grade' => '3', 'quarter' => '1',
        'topic' => 'Characteristics and Life Processes of Living Things'
    ],
    'GRADE 3 •.pdf' => [
        'grade' => '3', 'quarter' => '1',
        'topic' => 'Scientific Inquiry in Life Science'
    ],
    'Living Things Interactions.pdf' => [
        'grade' => '3', 'quarter' => '2',
        'topic' => 'Interactions Among Living Things and Their Environment'
    ],
    'Organism Structures.pdf' => [
        'grade' => '3', 'quarter' => '2',
        'topic' => 'Structure and Function of Organisms'
    ],

    // Grade 4
    'GRADE 4 LESSON 1.pdf' => [
        'grade' => '4', 'quarter' => '2',
        'topic' => 'Systems in Animals and Plants'
    ],
    'GRADE 4 LESSON 2.pdf' => [
        'grade' => '4', 'quarter' => '2',
        'topic' => 'Plant and Animal Habitats'
    ],
    'GRADE 4 LESSON 3.pdf' => [
        'grade' => '4', 'quarter' => '2',
        'topic' => 'Life Cycles of Plants and Animals'
    ],
    'GRADE 4 LESSON 4.pdf' => [
        'grade' => '4', 'quarter' => '2',
        'topic' => 'Animals and the Food They Eat'
    ],
    'GRADE 4 EPISODE 5.pdf' => [
        'grade' => '4', 'quarter' => '2',
        'topic' => 'Food Chains'
    ],
    'GRADE 4 EPISODE 6.pdf' => [
        'grade' => '4', 'quarter' => '2',
        'topic' => 'Water and Living Things'
    ],
    'GRADE 4 LESSON 7.pdf' => [
        'grade' => '4', 'quarter' => '2',
        'topic' => 'Soil and Plant Growth'
    ],

    // Grade 5
    'GRADE 5 LESSON 1.pdf' => [
        'grade' => '5', 'quarter' => '1',
        'topic' => 'Human Body Systems (Digestive, Respiratory, Reproductive System)'
    ],
    'Classification & Reproduction Showcase for Grade 5.pdf' => [
        'grade' => '5', 'quarter' => '1',
        'topic' => 'Classification and Reproduction of Living Things'
    ],
    'GRADE 5 LESSON 3.pdf' => [
        'grade' => '5', 'quarter' => '1',
        'topic' => 'Life Cycles of Living Things'
    ],

    // Grade 6
    'Grade 6 Science - Changes in Matter (1).pptx' => [
        'grade' => '6', 'quarter' => '1',
        'topic' => 'Changes in Matter'
    ],
    'Human Body Systems.pdf' => [
        'grade' => '6', 'quarter' => '2',
        'topic' => 'Human Body Systems (Circulatory and Nervous Systems)'
    ],
    'Plant Reproduction.pdf' => [
        'grade' => '6', 'quarter' => '2',
        'topic' => 'Reproduction in Plants'
    ],
    'Vertebrates and Invertebrates.pdf' => [
        'grade' => '6', 'quarter' => '2',
        'topic' => 'Vertebrates and Invertebrates'
    ]
];

// 5. Discover presentation files in directory
$baseSourceDir = __DIR__ . '/pdfs/pdfs for importing and testing';
$filesToProcess = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseSourceDir));
foreach ($iterator as $fileInfo) {
    if ($fileInfo->isFile()) {
        $filename = $fileInfo->getFilename();
        $ext = strtolower($fileInfo->getExtension());
        if (in_array($ext, ['pdf', 'pptx'])) {
            // Skip duplicate
            if ($filename === 'Grade 3 Science- Environmental Stewardship.pdf') {
                continue;
            }
            $filesToProcess[$filename] = $fileInfo->getPathname();
        }
    }
}

log_msg("Found " . count($filesToProcess) . " presentations in test folder.", "info");

$importedCount = 0;
$skippedCount = 0;
$errorCount = 0;

foreach ($filesToProcess as $filename => $sourcePath) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // Resolve mapping metadata
    $meta = $curriculumMap[$filename] ?? null;
    $grade = $meta['grade'] ?? '';
    $quarter = $meta['quarter'] ?? '1';
    $topic = $meta['topic'] ?? '';

    // Auto-detect if not in map
    if (!$grade) {
        if (preg_match('/G(?:rade)?[-_\s]*(\d)/i', $filename, $m)) $grade = $m[1];
        elseif (preg_match('/Grade (\d)/i', $sourcePath, $m)) $grade = $m[1];
        else $grade = '4';
    }
    if (!$topic) {
        $topic = preg_replace('/\.(pptx?|pdf)$/i', '', $filename);
        $topic = str_replace(['_', '-'], ' ', $topic);
    }

    log_msg("--------------------------------------------------", "info");
    log_msg("Processing: {$filename} (Grade {$grade}, Q{$quarter}, Topic: {$topic})", "info");

    // Check if already imported with valid slides
    $checkStmt = $pdo->prepare("SELECT id, slide_count, slides_dir, has_images FROM pptx_uploads WHERE original_name = ?");
    $checkStmt->execute([$filename]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing && $existing['has_images'] && $existing['slides_dir']) {
        $firstSlidePath = __DIR__ . '/' . $existing['slides_dir'] . 'slide_1.png';
        if (file_exists($firstSlidePath)) {
            log_msg("✓ Already imported (ID #{$existing['id']}, {$existing['slide_count']} slides). Skipping re-conversion.", "success");
            $skippedCount++;
            continue;
        }
    }

    // Save copy to uploads/pptx/
    $cleanBase = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $savedName = ($ext === 'pdf' ? 'pdf_' : 'pptx_') . time() . '_' . $cleanBase;
    $savedPath = $uploadDir . $savedName;
    copy($sourcePath, $savedPath);

    // Prepare slide images destination
    $uploadTimestamp = time() . '_' . rand(100, 999);
    $slidesDirName = "slides_{$uploadTimestamp}/";
    $slidesDirFull = $slidesBaseDir . $slidesDirName;
    $slidesDirRelative = "uploads/pptx/slides/{$slidesDirName}";
    if (!is_dir($slidesDirFull)) @mkdir($slidesDirFull, 0777, true);

    $hasImages = false;
    $slideCount = 0;
    $extractedSlides = [];

    // Run conversion
    if ($ext === 'pdf') {
        $scriptPath = __DIR__ . '/pdf_to_images.py';
        $cmd = escapeshellarg($pythonCmd) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($savedPath) . " " . escapeshellarg($slidesDirFull) . " 85 2>&1";
        $output = shell_exec($cmd);

        $lines = explode("\n", trim($output));
        $jsonLine = end($lines);
        $res = json_decode($jsonLine, true);

        if ($res && ($res['status'] ?? '') === 'success') {
            $hasImages = true;
            $slideCount = (int)$res['total_slides'];
            if (!empty($res['slides'])) {
                foreach ($res['slides'] as $sl) {
                    $extractedSlides[] = [
                        'title' => $sl['title'] ?? 'Slide',
                        'content' => $sl['content'] ?? '',
                        'type' => $sl['slide_type'] ?? 'content'
                    ];
                }
            }
            log_msg("✓ Converted {$slideCount} PDF slides via PyMuPDF/pypdfium2.", "success");
        } else {
            log_msg("⚠ PDF conversion failed: " . ($res['message'] ?? substr($output, 0, 100)), "err");
            $errorCount++;
            continue;
        }
    } else {
        // PPTX conversion
        $scriptPath = __DIR__ . '/pptx_to_images.py';
        $cmd = escapeshellarg($pythonCmd) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($savedPath) . " " . escapeshellarg($slidesDirFull) . " 2>&1";
        $output = shell_exec($cmd);

        $lines = explode("\n", trim($output));
        $jsonLine = end($lines);
        $res = json_decode($jsonLine, true);

        if ($res && ($res['status'] ?? '') === 'success') {
            $hasImages = true;
            $slideCount = (int)$res['total_slides'];
            log_msg("✓ Converted {$slideCount} PPTX slides.", "success");
        } else {
            log_msg("⚠ PPTX conversion issue: " . ($res['message'] ?? substr($output, 0, 100)), "warn");
        }
    }

    // Match or create curriculum_lessons record
    $curriculumLessonId = null;
    $clStmt = $pdo->prepare("SELECT id FROM curriculum_lessons WHERE grade = ? AND (topic = ? OR topic LIKE ?)");
    $clStmt->execute([$grade, $topic, "%$topic%"]);
    $clId = $clStmt->fetchColumn();

    if ($clId) {
        $curriculumLessonId = (int)$clId;
        log_msg("Linked to existing curriculum lesson ID #{$curriculumLessonId}", "info");
    } else {
        $content = implode("\n\n", array_map(fn($s) => ($s['title'] ?? '') . "\n" . ($s['content'] ?? ''), $extractedSlides));
        $castType = is_sqlite() ? "INTEGER" : "UNSIGNED";
        $stmtLessonNum = $pdo->prepare("SELECT COALESCE(MAX(CAST(lesson_number AS $castType)),0)+1 FROM curriculum_lessons WHERE grade=? AND quarter=?");
        $stmtLessonNum->execute([$grade, $quarter]);
        $lessonNum = $stmtLessonNum->fetchColumn();

        $stmtLesson = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtLesson->execute([$grade, $quarter, $lessonNum, $topic, $content, '[]']);
        $curriculumLessonId = $pdo->lastInsertId();
        log_msg("Created new curriculum lesson ID #{$curriculumLessonId}", "info");
    }

    // Ensure topic in topics table
    try {
        $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
        $stmtTCheck->execute([$grade, $topic]);
        if ($stmtTCheck->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)")->execute([$grade, $topic]);
        }
    } catch (Exception $e) {}

    // Populate lesson_slides if empty
    if ($curriculumLessonId && !empty($extractedSlides)) {
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM lesson_slides WHERE curriculum_lesson_id = ?");
        $stmtCount->execute([$curriculumLessonId]);
        if ($stmtCount->fetchColumn() == 0) {
            $stmtSlide = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type) VALUES (?, ?, ?, ?, ?)");
            foreach ($extractedSlides as $idx => $slide) {
                $stmtSlide->execute([
                    $curriculumLessonId,
                    $idx + 1,
                    $slide['title'] ?? "Slide " . ($idx + 1),
                    $slide['content'] ?? '',
                    $slide['type'] ?? 'content'
                ]);
            }
        }
    }

    // Insert or update pptx_uploads record
    if ($existing) {
        $stmtUp = $pdo->prepare("UPDATE pptx_uploads SET filename = ?, grade = ?, quarter = ?, topic = ?, slide_count = ?, curriculum_lesson_id = ?, slides_dir = ?, has_images = ?, file_type = ? WHERE id = ?");
        $stmtUp->execute([
            $savedName, $grade, $quarter, $topic,
            $slideCount, $curriculumLessonId,
            $slidesDirRelative, $hasImages ? 1 : 0, $ext,
            $existing['id']
        ]);
        log_msg("Updated presentation record ID #{$existing['id']}", "success");
    } else {
        $stmtIns = $pdo->prepare("INSERT INTO pptx_uploads (filename, original_name, grade, quarter, topic, slide_count, curriculum_lesson_id, slides_dir, has_images, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtIns->execute([
            $savedName, $filename, $grade, $quarter, $topic,
            $slideCount, $curriculumLessonId,
            $slidesDirRelative, $hasImages ? 1 : 0, $ext,
            'System Seeder'
        ]);
        $newId = $pdo->lastInsertId();
        log_msg("Created presentation record ID #{$newId}", "success");
    }

    $importedCount++;
}

log_msg("==================================================", "info");
log_msg("BATCH IMPORT COMPLETE! Imported/Converted: {$importedCount} | Skipped: {$skippedCount} | Errors: {$errorCount}", "success");

if (!$isCli) {
    echo "</div><p><a href='lessons.html' style='color:#38bdf8;font-weight:bold;'>← Return to Lessons & Presentations</a></p></body></html>";
}

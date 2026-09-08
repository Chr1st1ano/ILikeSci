<?php
/**
 * PPTX Upload & Visual Slide Viewer API
 * 
 * Uploads .pptx files, converts slides to PNG images using Python,
 * and serves them for a PowerPoint-like viewing experience.
 *
 * POST: Upload a PPTX file → convert to slide images → store in DB
 * GET:  ?action=list → list uploaded presentations
 * GET:  ?action=slides&id=X → get slide image URLs for a presentation
 * GET:  ?action=slide_image&id=X&slide=N → serve a single slide image
 * POST: action=delete → delete a presentation and its files
 */

require 'db.php';

// Ensure directories exist
$uploadDir = __DIR__ . '/uploads/pptx/';
$slidesBaseDir = __DIR__ . '/uploads/pptx/slides/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
if (!is_dir($slidesBaseDir)) mkdir($slidesBaseDir, 0777, true);

// Ensure pptx_uploads table exists with slides_dir column
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS pptx_uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        grade VARCHAR(10) DEFAULT '',
        quarter VARCHAR(10) DEFAULT '',
        topic VARCHAR(255) DEFAULT '',
        slide_count INT DEFAULT 0,
        curriculum_lesson_id INT DEFAULT NULL,
        slides_dir VARCHAR(255) DEFAULT '',
        has_images TINYINT DEFAULT 0,
        uploaded_by VARCHAR(50) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add missing columns if table already existed
    if (!db_column_exists($pdo, 'pptx_uploads', 'slides_dir')) {
        $colType = is_sqlite() ? "TEXT DEFAULT ''" : "VARCHAR(255) DEFAULT ''";
        $pdo->exec("ALTER TABLE pptx_uploads ADD COLUMN slides_dir $colType");
    }
    if (!db_column_exists($pdo, 'pptx_uploads', 'has_images')) {
        $colType = is_sqlite() ? "INTEGER DEFAULT 0" : "TINYINT DEFAULT 0";
        $pdo->exec("ALTER TABLE pptx_uploads ADD COLUMN has_images $colType");
    }
} catch (Exception $e) { /* ignore */ }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ============================================
// GET — List presentations / Get slides / Serve image
// ============================================
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    // Serve a single slide image file
    if ($action === 'slide_image' && isset($_GET['id']) && isset($_GET['slide'])) {
        $id = (int)$_GET['id'];
        $slideNum = (int)$_GET['slide'];

        $stmt = $pdo->prepare("SELECT slides_dir, has_images FROM pptx_uploads WHERE id = ?");
        $stmt->execute([$id]);
        $upload = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$upload || !$upload['has_images']) {
            http_response_code(404);
            exit;
        }

        $imgPath = __DIR__ . '/' . $upload['slides_dir'] . "slide_{$slideNum}.png";
        if (file_exists($imgPath)) {
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=86400');
            readfile($imgPath);
        } else {
            http_response_code(404);
        }
        exit;
    }

    header("Content-Type: application/json");

    if ($action === 'list') {
        $stmt = $pdo->query("SELECT * FROM pptx_uploads ORDER BY created_at DESC");
        $uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "uploads" => $uploads]);
    } elseif ($action === 'slides' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM pptx_uploads WHERE id = ?");
        $stmt->execute([$id]);
        $upload = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$upload) {
            echo json_encode(["status" => "error", "message" => "Upload not found"]);
            exit;
        }

        $slides = [];

        // If we have rendered slide images, return image URLs
        if ($upload['has_images'] && $upload['slides_dir']) {
            $slideCount = $upload['slide_count'];
            for ($i = 1; $i <= $slideCount; $i++) {
                $imgPath = __DIR__ . '/' . $upload['slides_dir'] . "slide_{$i}.png";
                if (file_exists($imgPath)) {
                    $slides[] = [
                        'slide_number' => $i,
                        'type' => 'image',
                        'image_url' => "pptx_api.php?action=slide_image&id={$id}&slide={$i}",
                        'title' => "Slide {$i}"
                    ];
                }
            }
        }

        // Fallback: get text-based slides from lesson_slides table
        if (empty($slides) && $upload['curriculum_lesson_id']) {
            $slidesStmt = $pdo->prepare("SELECT * FROM lesson_slides WHERE curriculum_lesson_id = ? ORDER BY slide_number ASC");
            $slidesStmt->execute([$upload['curriculum_lesson_id']]);
            $dbSlides = $slidesStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($dbSlides as $s) {
                $slides[] = [
                    'slide_number' => $s['slide_number'],
                    'type' => 'text',
                    'title' => $s['title'],
                    'content' => $s['content'],
                    'slide_type' => $s['slide_type']
                ];
            }
        }

        echo json_encode([
            "status" => "success",
            "upload" => $upload,
            "slides" => $slides,
            "has_images" => (bool)$upload['has_images']
        ]);
    }
    exit;
}

// ============================================
// POST — Upload PPTX or Delete
// ============================================
if ($method === 'POST') {
    header("Content-Type: application/json");

    // Handle delete action
    $rawInput = file_get_contents('php://input');
    $jsonInput = json_decode($rawInput, true);

    if (($jsonInput && isset($jsonInput['action']) && $jsonInput['action'] === 'delete') ||
        (isset($_POST['action']) && $_POST['action'] === 'delete')) {

        $id = null;
        if ($jsonInput && isset($jsonInput['id'])) {
            $id = (int)$jsonInput['id'];
        } elseif (isset($_POST['id'])) {
            $id = (int)$_POST['id'];
        }

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No ID provided"]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM pptx_uploads WHERE id = ?");
            $stmt->execute([$id]);
            $upload = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$upload) {
                echo json_encode(["status" => "error", "message" => "Upload not found"]);
                exit;
            }

            // Delete slide images directory
            if ($upload['slides_dir']) {
                $slidesPath = __DIR__ . '/' . $upload['slides_dir'];
                if (is_dir($slidesPath)) {
                    $files = glob($slidesPath . '*');
                    foreach ($files as $f) {
                        if (is_file($f)) unlink($f);
                    }
                    @rmdir($slidesPath);
                }
            }

            // Delete slides and curriculum lesson if they exist
            if ($upload['curriculum_lesson_id']) {
                $pdo->prepare("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?")->execute([$upload['curriculum_lesson_id']]);
                $pdo->prepare("DELETE FROM curriculum_lessons WHERE id = ?")->execute([$upload['curriculum_lesson_id']]);
            }

            // Delete upload record
            $pdo->prepare("DELETE FROM pptx_uploads WHERE id = ?")->execute([$id]);

            // Delete PPTX file from disk
            $filePath = $uploadDir . $upload['filename'];
            if (file_exists($filePath)) unlink($filePath);

            echo json_encode(["status" => "success", "message" => "Presentation deleted"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Delete failed: " . $e->getMessage()]);
        }
        exit;
    }

    // Handle file upload
    if (!isset($_FILES['pptx']) || $_FILES['pptx']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "No file uploaded or upload error"]);
        exit;
    }

    $file = $_FILES['pptx'];
    $originalName = $file['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($ext !== 'pptx') {
        echo json_encode(["status" => "error", "message" => "Only .pptx files are supported. Old .ppt format cannot be parsed."]);
        exit;
    }

    $grade = $_POST['grade'] ?? '';
    $quarter = $_POST['quarter'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $uploadedBy = $_POST['username'] ?? '';

    // Auto-detect grade/quarter from filename like PPT_SCIENCE_G4_Q3_W4.pptx
    if (!$grade && preg_match('/G(\d+)/i', $originalName, $gm)) {
        $grade = $gm[1];
    }
    if (!$quarter && preg_match('/Q(\d+)/i', $originalName, $qm)) {
        $quarter = $qm[1];
    }
    if (!$topic) {
        $topic = preg_replace('/\.(pptx?|ppt)$/i', '', $originalName);
        $topic = str_replace(['_', '-'], ' ', $topic);
    }

    // Save PPTX file
    $savedName = 'pptx_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
    $savedPath = $uploadDir . $savedName;
    move_uploaded_file($file['tmp_name'], $savedPath);

    // ---- Step 1: Convert slides to PNG images using Python ----
    $uploadTimestamp = time();
    $slidesDirName = "slides_{$uploadTimestamp}/";
    $slidesDirFull = $slidesBaseDir . $slidesDirName;
    $slidesDirRelative = "uploads/pptx/slides/{$slidesDirName}";
    $hasImages = false;
    $slideCount = 0;
    $conversionMessage = '';

    // Find Python executable
    $pythonCmd = '';
    $pythonPaths = [
        'C:\\Users\\08oyo\\AppData\\Local\\Python\\pythoncore-3.14-64\\python.exe',
        'C:\\Users\\08oyo\\AppData\\Local\\Python\\bin\\python.exe',
        'python',
        'py',
        'python3',
        'C:\\Games\\Python3.14\\python.exe',
        'C:\\Python3\\python.exe',
        'C:\\Python\\python.exe'
    ];
    foreach ($pythonPaths as $pp) {
        $testOut = shell_exec(escapeshellarg($pp) . " --version 2>&1");
        if ($testOut && stripos($testOut, 'python') !== false) {
            $pythonCmd = $pp;
            break;
        }
    }

    if ($pythonCmd) {
        $scriptPath = __DIR__ . '/pptx_to_images.py';
        if (file_exists($scriptPath)) {
            $cmd = escapeshellarg($pythonCmd) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($savedPath) . " " . escapeshellarg($slidesDirFull) . " 2>&1";
            $output = shell_exec($cmd);

            // Parse Python output — last line should be JSON
            $lines = explode("\n", trim($output));
            $jsonLine = end($lines);
            $result = json_decode($jsonLine, true);

            if ($result && $result['status'] === 'success') {
                $hasImages = true;
                $slideCount = $result['total_slides'];
                $conversionMessage = "Converted {$slideCount} slides to images";
            } else {
                $conversionMessage = "Image conversion failed, using text fallback";
            }
        } else {
            $conversionMessage = "pptx_to_images.py not found, using text fallback";
        }
    } else {
        $conversionMessage = "Python not found, using text fallback";
    }

    // ---- Step 2: Also extract text (for search/accessibility) ----
    $slides = extractPPTXSlides($savedPath);
    if (!$slideCount) $slideCount = count($slides);

    // Store as curriculum lesson (text content for search/fallback)
    $content = implode("\n\n", array_map(function($s) {
        return $s['title'] . "\n" . $s['content'];
    }, $slides));

    $castType = is_sqlite() ? "INTEGER" : "UNSIGNED";
    $stmtLessonNum = $pdo->prepare("SELECT COALESCE(MAX(CAST(lesson_number AS $castType)),0)+1 FROM curriculum_lessons WHERE grade=? AND quarter=?");
    $stmtLessonNum->execute([$grade, $quarter]);
    $lessonNum = $stmtLessonNum->fetchColumn();

    $stmtLesson = $pdo->prepare("INSERT INTO curriculum_lessons (grade, quarter, lesson_number, topic, content, objectives) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtLesson->execute([$grade, $quarter, $lessonNum, $topic, $content, '[]']);
    $curriculumLessonId = $pdo->lastInsertId();

    // Ensure topic exists in topics table
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

    // Store individual text slides
    $stmtSlide = $pdo->prepare("INSERT INTO lesson_slides (curriculum_lesson_id, slide_number, title, content, slide_type) VALUES (?, ?, ?, ?, ?)");
    foreach ($slides as $idx => $slide) {
        $stmtSlide->execute([
            $curriculumLessonId,
            $idx + 1,
            $slide['title'],
            $slide['content'],
            $slide['type'] ?? 'content'
        ]);
    }

    // Record in pptx_uploads
    $stmtUpload = $pdo->prepare("INSERT INTO pptx_uploads (filename, original_name, grade, quarter, topic, slide_count, curriculum_lesson_id, slides_dir, has_images, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtUpload->execute([
        $savedName, $originalName, $grade, $quarter, $topic,
        $slideCount, $curriculumLessonId,
        $hasImages ? $slidesDirRelative : '',
        $hasImages ? 1 : 0,
        $uploadedBy
    ]);
    $uploadId = $pdo->lastInsertId();

    $message = "Uploaded {$originalName}: {$slideCount} slides";
    if ($hasImages) {
        $message .= " (visual mode)";
    } else {
        $message .= " (text mode)";
    }

    echo json_encode([
        "status" => "success",
        "message" => $message,
        "slide_count" => $slideCount,
        "has_images" => $hasImages,
        "curriculum_lesson_id" => $curriculumLessonId,
        "upload_id" => $uploadId,
        "conversion" => $conversionMessage
    ]);
    exit;
}

// Handle DELETE method
if ($method === 'DELETE') {
    header("Content-Type: application/json");
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
    }
    if (!$id) {
        echo json_encode(["status" => "error", "message" => "No ID provided"]);
        exit;
    }

    // Reuse delete logic
    $_POST['action'] = 'delete';
    $_POST['id'] = $id;
    // Re-trigger via redirect... actually, just inline it
    try {
        $stmt = $pdo->prepare("SELECT * FROM pptx_uploads WHERE id = ?");
        $stmt->execute([$id]);
        $upload = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$upload) {
            echo json_encode(["status" => "error", "message" => "Not found"]);
            exit;
        }
        if ($upload['slides_dir']) {
            $slidesPath = __DIR__ . '/' . $upload['slides_dir'];
            if (is_dir($slidesPath)) {
                foreach (glob($slidesPath . '*') as $f) { if (is_file($f)) unlink($f); }
                @rmdir($slidesPath);
            }
        }
        if ($upload['curriculum_lesson_id']) {
            $pdo->prepare("DELETE FROM lesson_slides WHERE curriculum_lesson_id = ?")->execute([$upload['curriculum_lesson_id']]);
            $pdo->prepare("DELETE FROM curriculum_lessons WHERE id = ?")->execute([$upload['curriculum_lesson_id']]);
        }
        $pdo->prepare("DELETE FROM pptx_uploads WHERE id = ?")->execute([$id]);
        $filePath = $uploadDir . $upload['filename'];
        if (file_exists($filePath)) unlink($filePath);
        echo json_encode(["status" => "success", "message" => "Deleted"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

/**
 * Extract text from PPTX slides (fallback when images aren't available)
 */
function extractPPTXSlides($pptxPath) {
    $slides = [];
    $zip = new ZipArchive();
    if ($zip->open($pptxPath) !== true) return [];

    $slideFiles = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (preg_match('/^ppt\/slides\/slide(\d+)\.xml$/i', $name, $m)) {
            $slideFiles[(int)$m[1]] = $name;
        }
    }
    ksort($slideFiles);

    foreach ($slideFiles as $num => $fileName) {
        $xml = $zip->getFromName($fileName);
        if (!$xml) continue;

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $textParts = [];
        $titleParts = [];

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xpath->registerNamespace('p', 'http://schemas.openxmlformats.org/presentationml/2006/main');

        $shapes = $xpath->query('//p:sp');
        foreach ($shapes as $shapeIdx => $shape) {
            $texts = $xpath->query('.//a:t', $shape);
            $shapeText = '';
            foreach ($texts as $t) {
                $shapeText .= $t->textContent;
            }
            $shapeText = trim($shapeText);
            if (empty($shapeText)) continue;

            $nvSpPr = $xpath->query('.//p:nvSpPr/p:nvPr/p:ph', $shape);
            $isTitle = false;
            if ($nvSpPr->length > 0) {
                $phType = $nvSpPr->item(0)->getAttribute('type');
                if (in_array($phType, ['title', 'ctrTitle', 'subTitle'])) {
                    $isTitle = true;
                }
            }

            if ($isTitle || $shapeIdx === 0) {
                $titleParts[] = $shapeText;
            } else {
                $textParts[] = $shapeText;
            }
        }

        libxml_clear_errors();

        $title = !empty($titleParts) ? implode(' — ', $titleParts) : 'Slide ' . $num;
        $content = !empty($textParts) ? implode("\n", $textParts) : '';

        $type = 'content';
        if ($num === 1) $type = 'title';
        if (stripos($title, 'objective') !== false) $type = 'objectives';
        if (stripos($title, 'question') !== false || stripos($title, 'review') !== false) $type = 'questions';
        if (stripos($title, 'thank') !== false || stripos($title, 'end') !== false) $type = 'end';

        $slides[] = ['title' => $title, 'content' => $content, 'type' => $type];
    }

    $zip->close();
    return $slides;
}
?>

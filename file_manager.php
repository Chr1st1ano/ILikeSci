<?php
require 'db.php';
header("Content-Type: application/json");

// Create necessary directories
$saveDir = __DIR__ . '/savestates';
$exportDir = __DIR__ . '/exports';
if (!file_exists($saveDir)) mkdir($saveDir, 0777, true);
if (!file_exists($exportDir)) mkdir($exportDir, 0777, true);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit(0);
}

// ========== GET: List files or download ==========
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'list') {
        // List all saved files from savestates directory
        $files = [];
        foreach (glob($saveDir . '/*') as $file) {
            $files[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'type' => mime_content_type($file),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        echo json_encode(['status' => 'success', 'files' => $files]);
    }
    else if ($action === 'download') {
        $filename = basename($_GET['file'] ?? '');
        $filepath = $saveDir . '/' . $filename;
        if (file_exists($filepath)) {
            header('Content-Type: ' . mime_content_type($filepath));
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found']);
        }
    }
    exit;
}

// ========== POST: Export or Import ==========
if ($method === 'POST') {
    $action = $_POST['action'] ?? ($_GET['action'] ?? '');

    // --- EXPORT: Save data to files ---
    if ($action === 'export') {
        $format = $_POST['format'] ?? 'json';
        $grade = $_POST['grade'] ?? 'all';
        $section = $_POST['section'] ?? 'all';
        $dataType = $_POST['dataType'] ?? 'all'; // students, questions, records, all
        $stateJson = $_POST['stateData'] ?? '{}';
        
        $stateData = json_decode($stateJson, true);
        if (!$stateData) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid state data']);
            exit;
        }

        // Filter data by grade
        $exportPayload = [];

        // Students
        if ($dataType === 'all' || $dataType === 'students') {
            $students = $stateData['students'] ?? [];
            if ($grade !== 'all') {
                $students = array_values(array_filter($students, function($s) use ($grade) {
                    return $s['grade'] === $grade;
                }));
            }
            $exportPayload['students'] = $students;
        }

        // Questions from DB
        if ($dataType === 'all' || $dataType === 'questions') {
            try {
                $sql = "SELECT * FROM questions";
                $params = [];
                if ($grade !== 'all') {
                    $sql .= " WHERE grade = ?";
                    $params[] = $grade;
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $exportPayload['questions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $exportPayload['questions'] = [];
            }
        }

        // Records
        if ($dataType === 'all' || $dataType === 'records') {
            try {
                $sql = "SELECT r.*, s.name as student_name, s.grade FROM recitation_records r LEFT JOIN students s ON r.student_id = s.id";
                $params = [];
                if ($grade !== 'all') {
                    $sql .= " WHERE s.grade = ?";
                    $params[] = $grade;
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $exportPayload['records'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $exportPayload['records'] = [];
            }
        }

        // Settings
        if ($dataType === 'all') {
            $exportPayload['pointsMap'] = $stateData['pointsMap'] ?? ['Easy' => 1, 'Medium' => 3, 'Hard' => 5];
            $exportPayload['topics'] = $stateData['topics'] ?? [];
            $exportPayload['profile'] = $stateData['profile'] ?? [];
        }

        $exportPayload['exportDate'] = date('Y-m-d H:i:s');
        $exportPayload['exportGrade'] = $grade;

        $timestamp = date('Ymd_His');
        $gradeLabel = $grade !== 'all' ? "grade{$grade}" : 'all';

        if ($format === 'csv') {
            // Save CSV files for students and questions separately
            $files = [];

            if (isset($exportPayload['students']) && count($exportPayload['students']) > 0) {
                $csvFile = "students_{$gradeLabel}_{$timestamp}.csv";
                $fp = fopen($saveDir . '/' . $csvFile, 'w');
                fputcsv($fp, ['ID', 'Name', 'Grade', 'Recitations', 'Total Score']);
                foreach ($exportPayload['students'] as $s) {
                    fputcsv($fp, [$s['id'], $s['name'], $s['grade'], $s['recitations'] ?? 0, $s['totalScore'] ?? 0]);
                }
                fclose($fp);
                $files[] = $csvFile;
            }

            if (isset($exportPayload['questions']) && count($exportPayload['questions']) > 0) {
                $csvFile = "questions_{$gradeLabel}_{$timestamp}.csv";
                $fp = fopen($saveDir . '/' . $csvFile, 'w');
                fputcsv($fp, ['ID', 'Grade', 'Topic', 'Difficulty', 'Question']);
                foreach ($exportPayload['questions'] as $q) {
                    fputcsv($fp, [$q['id'], $q['grade'], $q['topic'], $q['difficulty'], $q['question_text'] ?? $q['text'] ?? '']);
                }
                fclose($fp);
                $files[] = $csvFile;
            }

            echo json_encode(['status' => 'success', 'message' => 'CSV files saved to savestates/', 'files' => $files]);
        } else {
            // Save as JSON
            $jsonFile = "ilikesci_{$dataType}_{$gradeLabel}_{$timestamp}.json";
            file_put_contents($saveDir . '/' . $jsonFile, json_encode($exportPayload, JSON_PRETTY_PRINT));
            echo json_encode(['status' => 'success', 'message' => 'JSON file saved to savestates/', 'file' => $jsonFile]);
        }
        exit;
    }

    // --- IMPORT: Load data from uploaded file ---
    if ($action === 'import') {
        if (!isset($_FILES['file'])) {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
            exit;
        }

        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Save a copy to savestates
        $importCopy = "import_" . date('Ymd_His') . "_" . $file['name'];
        copy($file['tmp_name'], $saveDir . '/' . $importCopy);

        if ($ext === 'json') {
            $content = file_get_contents($file['tmp_name']);
            $data = json_decode($content, true);
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid JSON file']);
                exit;
            }
            echo json_encode(['status' => 'success', 'data' => $data, 'savedAs' => $importCopy]);
        }
        else if ($ext === 'csv') {
            $rows = [];
            $headers = [];
            if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
                $lineNum = 0;
                while (($row = fgetcsv($handle)) !== false) {
                    if ($lineNum === 0) {
                        $headers = $row;
                    } else {
                        $assoc = [];
                        foreach ($headers as $i => $h) {
                            $assoc[strtolower(trim($h))] = $row[$i] ?? '';
                        }
                        $rows[] = $assoc;
                    }
                    $lineNum++;
                }
                fclose($handle);
            }
            echo json_encode(['status' => 'success', 'data' => ['rows' => $rows, 'headers' => $headers], 'savedAs' => $importCopy]);
        }
        else {
            // For PDF, PPTX, DOCX, images, videos — save to savestates and return path
            $savedName = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
            move_uploaded_file($file['tmp_name'], $saveDir . '/' . $savedName);
            echo json_encode([
                'status' => 'success',
                'message' => 'File saved to savestates/',
                'savedAs' => $savedName,
                'type' => $ext,
                'size' => $file['size']
            ]);
        }
        exit;
    }

    // --- UPLOAD MEDIA FILE ---
    if ($action === 'upload_media') {
        if (!isset($_FILES['file'])) {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
            exit;
        }
        $file = $_FILES['file'];
        $grade = $_POST['grade'] ?? 'all';
        $category = $_POST['category'] ?? 'general';
        $savedName = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        
        $targetDir = $saveDir . '/' . $category;
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        
        move_uploaded_file($file['tmp_name'], $targetDir . '/' . $savedName);
        
        // Save metadata to DB
        try {
            $stmt = $pdo->prepare("INSERT INTO multimedia_files (id, category, name, type, size) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([uniqid(), $category, $file['name'], $file['type'], $file['size']]);
        } catch (Exception $e) { /* ignore db errors for media */ }

        echo json_encode([
            'status' => 'success',
            'file' => $savedName,
            'path' => 'savestates/' . $category . '/' . $savedName,
            'grade' => $grade
        ]);
        exit;
    }
}

// ========== DELETE: Remove a saved file ==========
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $filename = basename($input['file'] ?? '');
    $filepath = $saveDir . '/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
        echo json_encode(['status' => 'success', 'message' => 'File deleted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File not found']);
    }
}
?>

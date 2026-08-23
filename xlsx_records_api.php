<?php
/**
 * xlsx_records_api.php — DepEd E-Class Record API
 * Handles CRUD for student_grades table (WW 40%, PT 40%, QA 20%)
 * 
 * Actions:
 *   GET  ?action=list&grade=4&section=A&quarter=1
 *   GET  ?action=transmutation_table
 *   POST { action: 'save', records: [...] }
 *   POST { action: 'save_single', ... }
 *   POST { action: 'delete', id: X }
 *   POST { action: 'import_xlsx' } (with file upload)
 */

header('Content-Type: application/json');
require_once 'db.php';

// DepEd Transmutation Table (DepEd Order No. 8, s. 2015)
function getTransmutationTable() {
    return [
        [0, 3.99, 60], [4, 7.99, 61], [8, 11.99, 62], [12, 15.99, 63],
        [16, 19.99, 64], [20, 23.99, 65], [24, 27.99, 66], [28, 31.99, 67],
        [32, 35.99, 68], [36, 39.99, 69], [40, 43.99, 70], [44, 47.99, 71],
        [48, 51.99, 72], [52, 55.99, 73], [56, 59.99, 74], [60, 63.99, 75],
        [64, 67.99, 76], [68, 71.99, 77], [72, 75.99, 78], [76, 79.99, 79],
        [80, 83.99, 80], [84, 87.99, 81], [88, 91.99, 82], [92, 95.99, 83],
        [96, 99.99, 84], [100, 100, 100]
    ];
}

function transmute($initialGrade) {
    if ($initialGrade >= 100) return 100;
    $table = getTransmutationTable();
    foreach ($table as $row) {
        if ($initialGrade >= $row[0] && $initialGrade <= $row[1]) {
            return $row[2];
        }
    }
    return 60;
}

// Science weights: WW 40%, PT 40%, QA 20%
define('WW_WEIGHT', 0.40);
define('PT_WEIGHT', 0.40);
define('QA_WEIGHT', 0.20);

function computeGrades(&$record) {
    $wwScores = json_decode($record['ww_scores'] ?? '[]', true) ?: [];
    $ptScores = json_decode($record['pt_scores'] ?? '[]', true) ?: [];
    $wwHighest = json_decode($record['ww_highest'] ?? '[]', true) ?: [];
    $ptHighest = json_decode($record['pt_highest'] ?? '[]', true) ?: [];
    $qaHighest = floatval($record['qa_highest'] ?? 0);

    $wwTotal = array_sum($wwScores);
    $ptTotal = array_sum($ptScores);
    $qaScore = floatval($record['qa_score'] ?? 0);

    $wwHPS = array_sum($wwHighest) ?: 1;
    $ptHPS = array_sum($ptHighest) ?: 1;
    $qaHPS = $qaHighest ?: 1;

    $wwPS = ($wwTotal / $wwHPS) * 100;
    $ptPS = ($ptTotal / $ptHPS) * 100;
    $qaPS = ($qaScore / $qaHPS) * 100;

    $wwWS = $wwPS * WW_WEIGHT;
    $ptWS = $ptPS * PT_WEIGHT;
    $qaWS = $qaPS * QA_WEIGHT;

    $initialGrade = $wwWS + $ptWS + $qaWS;
    $transmuted = transmute($initialGrade);

    $record['ww_total'] = round($wwTotal, 2);
    $record['ww_ps'] = round($wwPS, 2);
    $record['ww_ws'] = round($wwWS, 2);
    $record['pt_total'] = round($ptTotal, 2);
    $record['pt_ps'] = round($ptPS, 2);
    $record['pt_ws'] = round($ptWS, 2);
    $record['qa_ps'] = round($qaPS, 2);
    $record['qa_ws'] = round($qaWS, 2);
    $record['initial_grade'] = round($initialGrade, 2);
    $record['transmuted_grade'] = $transmuted;
}

$method = $_SERVER['REQUEST_METHOD'];

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(100) NOT NULL,
        grade_level VARCHAR(10) NOT NULL DEFAULT '4',
        section VARCHAR(10) NOT NULL DEFAULT 'A',
        quarter INT NOT NULL DEFAULT 1,
        gender VARCHAR(10) DEFAULT 'M',
        ww_scores JSON DEFAULT NULL,
        ww_total DECIMAL(8,2) DEFAULT 0,
        ww_ps DECIMAL(8,2) DEFAULT 0,
        ww_ws DECIMAL(8,2) DEFAULT 0,
        pt_scores JSON DEFAULT NULL,
        pt_total DECIMAL(8,2) DEFAULT 0,
        pt_ps DECIMAL(8,2) DEFAULT 0,
        pt_ws DECIMAL(8,2) DEFAULT 0,
        qa_score DECIMAL(8,2) DEFAULT 0,
        qa_ps DECIMAL(8,2) DEFAULT 0,
        qa_ws DECIMAL(8,2) DEFAULT 0,
        initial_grade DECIMAL(8,2) DEFAULT 0,
        transmuted_grade INT DEFAULT 0,
        ww_highest JSON DEFAULT NULL,
        pt_highest JSON DEFAULT NULL,
        qa_highest DECIMAL(8,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_student_quarter (student_name, grade_level, section, quarter)
    )");
} catch (Exception $e) {
    // table may already exist
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'transmutation_table') {
        echo json_encode(['status' => 'success', 'table' => getTransmutationTable()]);
        exit;
    }

    // List records
    $grade = $_GET['grade'] ?? 'all';
    $section = $_GET['section'] ?? 'all';
    $quarter = $_GET['quarter'] ?? 1;

    $sql = "SELECT * FROM student_grades WHERE quarter = ?";
    $params = [intval($quarter)];

    if ($grade !== 'all') {
        $sql .= " AND grade_level = ?";
        $params[] = $grade;
    }
    if ($section !== 'all') {
        $sql .= " AND section = ?";
        $params[] = $section;
    }

    $sql .= " ORDER BY gender ASC, student_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Parse JSON fields
    foreach ($records as &$r) {
        $r['ww_scores'] = json_decode($r['ww_scores'] ?? '[]', true) ?: [];
        $r['pt_scores'] = json_decode($r['pt_scores'] ?? '[]', true) ?: [];
        $r['ww_highest'] = json_decode($r['ww_highest'] ?? '[]', true) ?: [];
        $r['pt_highest'] = json_decode($r['pt_highest'] ?? '[]', true) ?: [];
    }
    unset($r);

    echo json_encode(['status' => 'success', 'records' => $records, 'count' => count($records)]);
    exit;
}

if ($method === 'POST') {
    // Check if it's a file upload (import)
    if (isset($_FILES['xlsx'])) {
        $action = 'import_xlsx';
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
    }

    if ($action === 'save_single') {
        // Save a single student grade record
        $name = trim($input['student_name'] ?? '');
        $gradeLevel = $input['grade_level'] ?? '4';
        $section = $input['section'] ?? 'A';
        $quarter = intval($input['quarter'] ?? 1);
        $gender = $input['gender'] ?? 'M';
        $wwScores = json_encode($input['ww_scores'] ?? []);
        $ptScores = json_encode($input['pt_scores'] ?? []);
        $qaScore = floatval($input['qa_score'] ?? 0);
        $wwHighest = json_encode($input['ww_highest'] ?? []);
        $ptHighest = json_encode($input['pt_highest'] ?? []);
        $qaHighest = floatval($input['qa_highest'] ?? 0);

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Student name required']);
            exit;
        }

        // Compute grades
        $record = [
            'ww_scores' => $wwScores,
            'pt_scores' => $ptScores,
            'qa_score' => $qaScore,
            'ww_highest' => $wwHighest,
            'pt_highest' => $ptHighest,
            'qa_highest' => $qaHighest
        ];
        computeGrades($record);

        $stmt = $pdo->prepare("INSERT INTO student_grades 
            (student_name, grade_level, section, quarter, gender, ww_scores, ww_total, ww_ps, ww_ws, pt_scores, pt_total, pt_ps, pt_ws, qa_score, qa_ps, qa_ws, initial_grade, transmuted_grade, ww_highest, pt_highest, qa_highest)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
            gender=VALUES(gender), ww_scores=VALUES(ww_scores), ww_total=VALUES(ww_total), ww_ps=VALUES(ww_ps), ww_ws=VALUES(ww_ws),
            pt_scores=VALUES(pt_scores), pt_total=VALUES(pt_total), pt_ps=VALUES(pt_ps), pt_ws=VALUES(pt_ws),
            qa_score=VALUES(qa_score), qa_ps=VALUES(qa_ps), qa_ws=VALUES(qa_ws),
            initial_grade=VALUES(initial_grade), transmuted_grade=VALUES(transmuted_grade),
            ww_highest=VALUES(ww_highest), pt_highest=VALUES(pt_highest), qa_highest=VALUES(qa_highest)");
        
        $stmt->execute([
            $name, $gradeLevel, $section, $quarter, $gender,
            $wwScores, $record['ww_total'], $record['ww_ps'], $record['ww_ws'],
            $ptScores, $record['pt_total'], $record['pt_ps'], $record['pt_ws'],
            $qaScore, $record['qa_ps'], $record['qa_ws'],
            $record['initial_grade'], $record['transmuted_grade'],
            $wwHighest, $ptHighest, $qaHighest
        ]);

        echo json_encode(['status' => 'success', 'message' => "Saved record for $name"]);
        exit;
    }

    if ($action === 'save') {
        // Batch save
        $records = $input['records'] ?? [];
        $saved = 0;
        $errors = [];

        foreach ($records as $rec) {
            $name = trim($rec['student_name'] ?? '');
            if (empty($name)) continue;

            $gradeLevel = $rec['grade_level'] ?? '4';
            $section = $rec['section'] ?? 'A';
            $quarter = intval($rec['quarter'] ?? 1);
            $gender = $rec['gender'] ?? 'M';
            $wwScores = json_encode($rec['ww_scores'] ?? []);
            $ptScores = json_encode($rec['pt_scores'] ?? []);
            $qaScore = floatval($rec['qa_score'] ?? 0);
            $wwHighest = json_encode($rec['ww_highest'] ?? []);
            $ptHighest = json_encode($rec['pt_highest'] ?? []);
            $qaHighest = floatval($rec['qa_highest'] ?? 0);

            $record = [
                'ww_scores' => $wwScores, 'pt_scores' => $ptScores, 'qa_score' => $qaScore,
                'ww_highest' => $wwHighest, 'pt_highest' => $ptHighest, 'qa_highest' => $qaHighest
            ];
            computeGrades($record);

            try {
                $stmt = $pdo->prepare("INSERT INTO student_grades 
                    (student_name, grade_level, section, quarter, gender, ww_scores, ww_total, ww_ps, ww_ws, pt_scores, pt_total, pt_ps, pt_ws, qa_score, qa_ps, qa_ws, initial_grade, transmuted_grade, ww_highest, pt_highest, qa_highest)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                    gender=VALUES(gender), ww_scores=VALUES(ww_scores), ww_total=VALUES(ww_total), ww_ps=VALUES(ww_ps), ww_ws=VALUES(ww_ws),
                    pt_scores=VALUES(pt_scores), pt_total=VALUES(pt_total), pt_ps=VALUES(pt_ps), pt_ws=VALUES(pt_ws),
                    qa_score=VALUES(qa_score), qa_ps=VALUES(qa_ps), qa_ws=VALUES(qa_ws),
                    initial_grade=VALUES(initial_grade), transmuted_grade=VALUES(transmuted_grade),
                    ww_highest=VALUES(ww_highest), pt_highest=VALUES(pt_highest), qa_highest=VALUES(qa_highest)");
                
                $stmt->execute([
                    $name, $gradeLevel, $section, $quarter, $gender,
                    $wwScores, $record['ww_total'], $record['ww_ps'], $record['ww_ws'],
                    $ptScores, $record['pt_total'], $record['pt_ps'], $record['pt_ws'],
                    $qaScore, $record['qa_ps'], $record['qa_ws'],
                    $record['initial_grade'], $record['transmuted_grade'],
                    $wwHighest, $ptHighest, $qaHighest
                ]);
                $saved++;
            } catch (Exception $e) {
                $errors[] = "$name: " . $e->getMessage();
            }
        }

        echo json_encode(['status' => 'success', 'saved' => $saved, 'errors' => $errors, 'message' => "$saved records saved"]);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($input['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM student_grades WHERE id = ?")->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Record deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        }
        exit;
    }

    if ($action === 'delete_quarter') {
        $gradeLevel = $input['grade_level'] ?? '';
        $section = $input['section'] ?? '';
        $quarter = intval($input['quarter'] ?? 0);
        
        $stmt = $pdo->prepare("DELETE FROM student_grades WHERE grade_level = ? AND section = ? AND quarter = ?");
        $stmt->execute([$gradeLevel, $section, $quarter]);
        $count = $stmt->rowCount();
        echo json_encode(['status' => 'success', 'message' => "$count records deleted"]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
?>

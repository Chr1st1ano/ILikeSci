<?php
require_once dirname(__DIR__) . '/db.php';

echo "=======================================================\n";
echo "      FOOLPROOF & USABILITY SAFEGUARDS TEST SUITE      \n";
echo "=======================================================\n\n";

$passed = 0;
$failed = 0;

function check($title, $condition, $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $title\n";
        $passed++;
    } else {
        echo "  [FAIL] $title" . ($details ? " ($details)" : "") . "\n";
        $failed++;
    }
}

function run_endpoint_test($script, $method = 'GET', $params = [], $body = null, $currentUser = 'admin') {
    $baseDir = dirname(__DIR__);
    $tmpFile = $baseDir . '/scratch/test_sub_' . uniqid() . '.php';
    
    $postData = ($body !== null && is_array($body)) ? array_merge($params, $body) : $params;
    $getData = ($method === 'GET') ? $params : [];
    
    $rawBody = '';
    if ($body !== null) {
        $rawBody = is_string($body) ? $body : json_encode($body);
    } elseif (!empty($postData) && $method !== 'GET') {
        $rawBody = json_encode($postData);
    }

    $code = "<?php\n";
    $code .= "\$_SERVER['REQUEST_METHOD'] = " . var_export($method, true) . ";\n";
    $code .= "\$_SERVER['HTTP_X_CURRENT_USER'] = " . var_export($currentUser, true) . ";\n";
    $code .= "\$_GET = " . var_export($getData, true) . ";\n";
    $code .= "\$_POST = " . var_export($postData, true) . ";\n";
    $code .= "require " . var_export($baseDir . '/' . $script, true) . ";\n";

    file_put_contents($tmpFile, $code);

    $descriptorSpec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];

    $process = proc_open("php " . escapeshellarg($tmpFile), $descriptorSpec, $pipes);
    $stdout = '';
    if (is_resource($process)) {
        if ($rawBody !== '') {
            fwrite($pipes[0], $rawBody);
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }

    @unlink($tmpFile);
    $decoded = json_decode($stdout, true);
    return $decoded !== null ? $decoded : $stdout;
}

// 1. Verify Coney assigned grade is strictly 4 in database
$coney = $pdo->query("SELECT * FROM users WHERE username = 'coney'")->fetch(PDO::FETCH_ASSOC);
check("User 'coney' exists in database", !empty($coney));
check("User 'coney' has assigned_grade == '4'", ($coney['assigned_grade'] ?? '') === '4');

// 2. Test profile_api.php safeguard for Coney
$coneyProfileReq = [
    'username' => 'coney',
    'display_name' => 'Ma\'am Coney',
    'assigned_grade' => '5', // attempt to change to 5
    'assigned_section' => 'A'
];
$profRes = run_endpoint_test('profile_api.php', 'POST', [], $coneyProfileReq, 'coney');
$coneyAfter = $pdo->query("SELECT assigned_grade FROM users WHERE username = 'coney'")->fetchColumn();
check("profile_api.php rejected grade alteration for Coney (remains 4)", $coneyAfter === '4');

// 3. Test admin_api.php RBAC for teacher
$createByTeacher = run_endpoint_test('admin_api.php', 'POST', [], ['action' => 'create_user', 'username' => 'test_hacker', 'password' => '123'], 'coney');
check("admin_api.php blocks non-admin from creating users", is_array($createByTeacher) && ($createByTeacher['status'] ?? '') === 'error' && strpos($createByTeacher['message'], 'Access denied') !== false);

// 4. Test admin_api.php Coney deletion protection
$delConeyRes = run_endpoint_test('admin_api.php', 'POST', [], ['action' => 'delete_user', 'id' => $coney['id']], 'admin');
check("admin_api.php prevents deleting primary account 'coney'", is_array($delConeyRes) && ($delConeyRes['status'] ?? '') === 'error' && strpos($delConeyRes['message'], 'coney') !== false);

// 5. Test questions_api.php filtering
$qRes = run_endpoint_test('questions_api.php', 'GET', ['grade' => '4'], null, 'admin');
$allG4 = true;
if (is_array($qRes) && !empty($qRes['questions'])) {
    foreach ($qRes['questions'] as $q) {
        if ($q['grade'] !== '4') { $allG4 = false; break; }
    }
}
check("questions_api.php?grade=4 returns only Grade 4 questions", $allG4 && !empty($qRes['questions']));

// 6. Test questions_api.php rejects Grade 7
$q7Res = run_endpoint_test('questions_api.php', 'POST', [], ['grade' => '7', 'topic' => 'Test', 'difficulty' => 'Easy', 'text' => 'Sample?'], 'admin');
check("questions_api.php rejects adding Grade 7 questions", is_array($q7Res) && ($q7Res['status'] ?? '') === 'error');

// 7. Test xlsx_records_api.php rejects section 'all' in save_single
$secAllRes = run_endpoint_test('xlsx_records_api.php', 'POST', [], [
    'action' => 'save_single',
    'student_name' => 'Test Dummy Learner',
    'grade_level' => '4',
    'section' => 'all', // invalid section
    'quarter' => 1
], 'admin');
check("xlsx_records_api.php rejects saving student grade with section 'all'", is_array($secAllRes) && ($secAllRes['status'] ?? '') === 'error');

// 8. Test student deletion cleans up student_grades
// Clean any leftover from previous runs
$pdo->exec("DELETE FROM students WHERE id = 99998");
$pdo->exec("DELETE FROM student_grades WHERE student_name = 'Temp Deletion Tester'");

$pdo->exec("INSERT INTO students (id, name, grade, section) VALUES (99998, 'Temp Deletion Tester', '4', 'Einstein')");
$pdo->exec("INSERT INTO student_grades (student_name, grade_level, section, quarter) VALUES ('Temp Deletion Tester', '4', 'Einstein', 1)");

$delStudRes = run_endpoint_test('student_api.php', 'DELETE', [], ['id' => 99998], 'admin');
if (!is_array($delStudRes) || ($delStudRes['status'] ?? '') !== 'success') {
    echo "  [DEBUG] delStudRes: " . json_encode($delStudRes) . "\n";
}

$remainingStudent = $pdo->query("SELECT COUNT(*) FROM students WHERE id = 99998")->fetchColumn();
$remainingGrade = $pdo->query("SELECT COUNT(*) FROM student_grades WHERE student_name = 'Temp Deletion Tester'")->fetchColumn();

check("student_api.php DELETE removes student record", (int)$remainingStudent === 0);
check("student_api.php DELETE cascades to student_grades without orphaned rows", (int)$remainingGrade === 0);

// 9. Test questions_api.php blocks teacher Coney from adding Grade 6 question
$qTeacherCrossRes = run_endpoint_test('questions_api.php', 'POST', [], [
    'grade' => '6',
    'topic' => 'Illegal Cross-Grade Topic',
    'difficulty' => 'Easy',
    'text' => 'Should Coney be able to add this?'
], 'coney');
check("questions_api.php blocks teacher Coney from adding Grade 6 question", is_array($qTeacherCrossRes) && ($qTeacherCrossRes['status'] ?? '') === 'error');

// 10. Test lessons_api.php blocks teacher Coney from creating Grade 6 lesson
$lesTeacherCrossRes = run_endpoint_test('lessons_api.php', 'POST', [], [
    'action' => 'create_lesson',
    'grade' => '6',
    'quarter' => '1',
    'lesson_number' => '1',
    'topic' => 'Illegal Cross-Grade Lesson',
    'objectives' => ['Obj 1'],
    'slides' => [['title' => 'S1', 'content' => 'C1']]
], 'coney');
check("lessons_api.php blocks teacher Coney from creating Grade 6 lesson", is_array($lesTeacherCrossRes) && ($lesTeacherCrossRes['status'] ?? '') === 'error');

// 11. Test pptx_api.php?action=list for teacher Coney returns strictly Grade 4 presentations
$pptxConeyRes = run_endpoint_test('pptx_api.php', 'GET', ['action' => 'list'], [], 'coney');
$allPptxG4 = true;
if (is_array($pptxConeyRes) && !empty($pptxConeyRes['uploads'])) {
    foreach ($pptxConeyRes['uploads'] as $u) {
        if ((string)$u['grade'] !== '4') {
            $allPptxG4 = false;
            break;
        }
    }
} else {
    $allPptxG4 = false;
}
check("pptx_api.php?action=list for teacher Coney returns only Grade 4 presentations", $allPptxG4);

// 12. Test pptx_api.php blocks teacher Coney from deleting a Grade 6 presentation
$g6PptxId = $pdo->query("SELECT id FROM pptx_uploads WHERE grade = '6' LIMIT 1")->fetchColumn();
if ($g6PptxId) {
    $delPptxRes = run_endpoint_test('pptx_api.php', 'POST', [], ['action' => 'delete', 'id' => $g6PptxId], 'coney');
    check("pptx_api.php blocks teacher Coney from deleting Grade 6 presentation", is_array($delPptxRes) && ($delPptxRes['status'] ?? '') === 'error');
    // Verify it was NOT deleted
    $stillExists = $pdo->query("SELECT COUNT(*) FROM pptx_uploads WHERE id = $g6PptxId")->fetchColumn();
    check("Grade 6 presentation remains intact in database", (int)$stillExists === 1);
} else {
    check("pptx_api.php delete test skipped (no Grade 6 presentation)", true);
}

echo "\n=======================================================\n";
echo "SAFEGUARD RESULTS: $passed PASSED, $failed FAILED\n";
echo "=======================================================\n";

if ($failed > 0) exit(1);

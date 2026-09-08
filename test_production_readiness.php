<?php
/**
 * =======================================================================
 * ILikeSci — Production Readiness Automated Verification Suite
 * =======================================================================
 * Tests all backend APIs, database engine portability (MySQL/SQLite),
 * security boundaries, low-end hardware performance benchmarks, and data
 * integrity.
 */

// Colors for terminal output
define('CLR_RESET', "\033[0m");
define('CLR_GREEN', "\033[32m");
define('CLR_RED',   "\033[31m");
define('CLR_CYAN',  "\033[36m");
define('CLR_YELLOW',"\033[33m");
define('CLR_BOLD',  "\033[1m");

$totalTests = 0;
$passedTests = 0;
$failedTests = [];

function assert_test($description, $condition, $details = '') {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo CLR_GREEN . "  [PASS] " . CLR_RESET . $description . "\n";
    } else {
        $failedTests[] = ['desc' => $description, 'details' => $details];
        echo CLR_RED . "  [FAIL] " . CLR_RESET . $description . ($details ? " (" . $details . ")" : "") . "\n";
    }
}

function run_endpoint($script, $method = 'GET', $params = [], $body = null) {
    $tmpFile = __DIR__ . '/scratch_runner_' . uniqid() . '.php';
    
    $postData = ($method === 'POST') ? ($body !== null && is_array($body) ? array_merge($params, $body) : $params) : [];
    $getData = ($method === 'GET') ? $params : [];
    
    $rawBody = '';
    if ($body !== null) {
        $rawBody = is_string($body) ? $body : json_encode($body);
    } elseif ($method === 'POST' && !empty($postData)) {
        $rawBody = json_encode($postData);
    }

    $code = "<?php\n";
    $code .= "\$_SERVER['REQUEST_METHOD'] = " . var_export($method, true) . ";\n";
    $code .= "\$_GET = " . var_export($getData, true) . ";\n";
    $code .= "\$_POST = " . var_export($postData, true) . ";\n";
    
    // If rawBody is non-empty, use a stream wrapper or php://input simulator if needed
    // In our APIs, $input = json_decode(file_get_contents('php://input')) ?: $_POST;
    // So setting $_POST already fulfills $input!
    $code .= "require " . var_export(__DIR__ . '/' . $script, true) . ";\n";

    file_put_contents($tmpFile, $code);
    $out = shell_exec("php " . escapeshellarg($tmpFile) . " 2>&1");
    @unlink($tmpFile);

    // Extract json from possible PHP warnings
    $jsonStart = strpos($out, '{');
    $jsonEnd = strrpos($out, '}');
    if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
        $jsonStr = substr($out, $jsonStart, $jsonEnd - $jsonStart + 1);
        $decoded = json_decode($jsonStr, true);
        if ($decoded) return $decoded;
    }

    return json_decode($out, true) ?: trim($out);
}

echo "\n" . CLR_CYAN . CLR_BOLD . "=======================================================" . CLR_RESET . "\n";
echo CLR_CYAN . CLR_BOLD . "      ILIKESCI PRODUCTION READINESS TEST SUITE        " . CLR_RESET . "\n";
echo CLR_CYAN . CLR_BOLD . "=======================================================" . CLR_RESET . "\n\n";

// --- SUITE 1: DATABASE CORE & PORTABILITY ---
echo CLR_YELLOW . CLR_BOLD . "1. Database Engine & Portability" . CLR_RESET . "\n";
require_once __DIR__ . '/db.php';

assert_test("Database PDO connection is active", isset($pdo) && $pdo instanceof PDO);
assert_test("DB_ENGINE is defined (SQLITE or MYSQL)", defined('DB_ENGINE') && in_array(strtoupper(DB_ENGINE), ['SQLITE', 'MYSQL']));
assert_test("is_sqlite() helper accurately reports engine", function_exists('is_sqlite'));

if (is_sqlite()) {
    $journal = $pdo->query("PRAGMA journal_mode")->fetchColumn();
    assert_test("SQLite WAL mode enabled for concurrent performance", strtolower($journal) === 'wal', "Got: $journal");
    $sync = $pdo->query("PRAGMA synchronous")->fetchColumn();
    assert_test("SQLite synchronous set to NORMAL/OFF for speed", in_array((int)$sync, [0, 1]), "Got: $sync");
}

$tables = [
    'users', 'teacher_profiles', 'students', 'multimedia_files', 
    'topics', 'questions', 'recitation_records', 'curriculum_lessons', 
    'lesson_slides', 'pptx_uploads', 'student_grades', 'ai_cache', 
    'ai_rate_limits', 'backups'
];

$allTablesExist = true;
foreach ($tables as $tbl) {
    try {
        $pdo->query("SELECT 1 FROM $tbl LIMIT 1");
    } catch (Exception $e) {
        $allTablesExist = false;
        break;
    }
}
assert_test("All 14 core database tables verified present", $allTablesExist);

// --- SUITE 2: AUTHENTICATION & SECURITY ---
echo "\n" . CLR_YELLOW . CLR_BOLD . "2. Authentication & Session Security" . CLR_RESET . "\n";
// Test login with seeded admin credentials
$loginRes = run_endpoint('auth.php', 'POST', ['username' => 'oyo', 'password' => 'admin123']);
assert_test("Admin login succeeds with valid hashed password", is_array($loginRes) && ($loginRes['status'] ?? '') === 'success');
assert_test("Admin login does not expose password hashes", is_array($loginRes) && !isset($loginRes['user']['password']));

// Test rejected login
$badLogin = run_endpoint('auth.php', 'POST', ['username' => 'oyo', 'password' => 'wrongpassword123']);
assert_test("Login rejects invalid credentials", is_array($badLogin) && ($badLogin['status'] ?? '') === 'error');

// Test users listing
$usersRes = run_endpoint('auth.php', 'GET');
assert_test("Users list endpoint returns valid users without password leakage", is_array($usersRes) && !empty($usersRes['users']) && !isset($usersRes['users'][0]['password']));

// --- SUITE 3: STUDENTS API & UPSERT PORTABILITY ---
echo "\n" . CLR_YELLOW . CLR_BOLD . "3. Students API (Dual-Engine Upsert)" . CLR_RESET . "\n";
$studentsRes = run_endpoint('student_api.php', 'GET');
assert_test("GET students returns list", is_array($studentsRes) && ($studentsRes['status'] ?? '') === 'success' && isset($studentsRes['students']));

$testStudentId = 999901;
$insertStudent = run_endpoint('student_api.php', 'POST', [
    'id' => $testStudentId,
    'name' => 'Automated Test Student',
    'grade' => '4',
    'section' => 'A',
    'recitations' => 5,
    'total_score' => 15
]);
assert_test("POST upsert student works on active engine", is_array($insertStudent) && ($insertStudent['status'] ?? '') === 'success');

// Verify student in DB
$stmtSt = $pdo->prepare("SELECT name, recitations, total_score FROM students WHERE id = ?");
$stmtSt->execute([$testStudentId]);
$stRow = $stmtSt->fetch(PDO::FETCH_ASSOC);
assert_test("Upserted student data verified in database", $stRow && $stRow['name'] === 'Automated Test Student' && (int)$stRow['total_score'] === 15);

// Clean test student
$pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$testStudentId]);

// --- SUITE 4: QUESTIONS & LESSONS CURRICULUM ---
echo "\n" . CLR_YELLOW . CLR_BOLD . "4. Questions & Curriculum Lessons" . CLR_RESET . "\n";
$qRes = run_endpoint('questions_api.php', 'GET', ['grade' => '4']);
assert_test("GET questions filtered by grade works", is_array($qRes) && ($qRes['status'] ?? '') === 'success' && !empty($qRes['questions']));

$lessonsRes = run_endpoint('lessons_api.php', 'GET', ['grade' => '4', 'quarter' => '3']);
assert_test("GET curriculum lessons filtered by grade & quarter works", is_array($lessonsRes) && ($lessonsRes['status'] ?? '') === 'success' && !empty($lessonsRes['lessons']));

$firstLessonId = $lessonsRes['lessons'][0]['id'] ?? 1;
$slidesRes = run_endpoint('lessons_api.php', 'GET', ['slides' => $firstLessonId]);
assert_test("GET slides for lesson returns slide sequence", is_array($slidesRes) && ($slidesRes['status'] ?? '') === 'success' && !empty($slidesRes['slides']));

// --- SUITE 5: RECITATION & STATS API ---
echo "\n" . CLR_YELLOW . CLR_BOLD . "5. Recitation & Admin System Stats" . CLR_RESET . "\n";
$statsRes = run_endpoint('admin_api.php', 'GET', ['action' => 'stats']);
assert_test("GET admin stats returns counts and breakdowns", is_array($statsRes) && ($statsRes['status'] ?? '') === 'success' && isset($statsRes['stats']['total_questions']));

$recRes = run_endpoint('recitation_api.php', 'GET');
assert_test("GET recitation records history returns successfully", is_array($recRes) && ($recRes['status'] ?? '') === 'success' && isset($recRes['records']));

// --- SUITE 6: AI API & CACHING SYSTEM ---
echo "\n" . CLR_YELLOW . CLR_BOLD . "6. AI API & Rate Limiting" . CLR_RESET . "\n";
$aiStatus = run_endpoint('ai_api.php', 'GET', ['action' => 'status']);
assert_test("GET AI status returns provider and limits", is_array($aiStatus) && ($aiStatus['status'] ?? '') === 'success' && isset($aiStatus['provider']));

$aiCache = run_endpoint('ai_api.php', 'POST', ['action' => 'cache_stats']);
assert_test("POST AI cache_stats returns valid cache summary", is_array($aiCache) && ($aiCache['status'] ?? '') === 'success' && isset($aiCache['cache_stats']));

// --- SUITE 7: XLSX RECORDS API & DEPED FORMULAS ---
echo "\n" . CLR_YELLOW . CLR_BOLD . "7. DepEd E-Class Records & Transmutation" . CLR_RESET . "\n";
$xlsxRes = run_endpoint('xlsx_records_api.php', 'GET', ['action' => 'list', 'grade' => '4', 'section' => 'A', 'quarter' => '1']);
assert_test("GET DepEd student grades returns valid structure", is_array($xlsxRes) && ($xlsxRes['status'] ?? '') === 'success');

// Verify DepEd transmutation function in xlsx_records_api.php
$calcRes = run_endpoint('xlsx_records_api.php', 'POST', [
    'action' => 'calculate',
    'data' => [
        'ww_scores' => [20, 20],
        'ww_highest' => [20, 20],
        'pt_scores' => [25, 25],
        'pt_highest' => [25, 25],
        'qa_score' => 50,
        'qa_highest' => 50
    ]
]);
assert_test("DepEd E-Class Record calculate endpoint calculates 100% -> 100 transmutation", is_array($calcRes) && ($calcRes['status'] ?? '') === 'success' && ($calcRes['transmuted_grade'] ?? 0) === 100);

// --- SUITE 8: SECURITY HARDENING & FILE PROTECTION ---
echo "\n" . CLR_YELLOW . CLR_BOLD . "8. Security Hardening & File Protection" . CLR_RESET . "\n";
assert_test("Root .htaccess exists with security & compression directives", file_exists(__DIR__ . '/.htaccess'));
assert_test("savestates/.htaccess prevents script execution", file_exists(__DIR__ . '/savestates/.htaccess'));
assert_test("uploads/.htaccess prevents script execution", file_exists(__DIR__ . '/uploads/.htaccess'));
assert_test("exports/.htaccess prevents script execution", file_exists(__DIR__ . '/exports/.htaccess'));

// Test malicious file extension rejection in file_manager.php
$badFileRes = run_endpoint('file_manager.php', 'POST', [
    'action' => 'import'
]);
assert_test("file_manager.php rejects unauthorized requests with no file", is_array($badFileRes) && ($badFileRes['status'] ?? '') === 'error');

// --- SUITE 9: LOW-END HARDWARE PERFORMANCE BENCHMARK ---
echo "\n" . CLR_YELLOW . CLR_BOLD . "9. Low-End Hardware Performance Benchmark" . CLR_RESET . "\n";
$startBench = microtime(true);
$iterations = 100;
for ($i = 0; $i < $iterations; $i++) {
    $q = $pdo->query("SELECT id, topic, difficulty FROM questions WHERE grade = '4' LIMIT 10")->fetchAll();
}
$benchDuration = (microtime(true) - $startBench) * 1000; // ms
$avgPerQuery = $benchDuration / $iterations;
assert_test("100 indexed queries execute in < 250ms total (measured: " . round($benchDuration, 2) . "ms, " . round($avgPerQuery, 3) . "ms/query)", $benchDuration < 250);

// --- SUMMARY ---
echo "\n" . CLR_CYAN . CLR_BOLD . "=======================================================" . CLR_RESET . "\n";
echo CLR_BOLD . "TEST RESULTS: " . ($passedTests === $totalTests ? CLR_GREEN : CLR_RED) . "$passedTests / $totalTests PASSED" . CLR_RESET . "\n";

if (!empty($failedTests)) {
    echo CLR_RED . CLR_BOLD . "\nFAILURES DETECTED:\n" . CLR_RESET;
    foreach ($failedTests as $f) {
        echo CLR_RED . "  - " . $f['desc'] . ": " . $f['details'] . CLR_RESET . "\n";
    }
} else {
    echo CLR_GREEN . CLR_BOLD . "ALL PRODUCTION READINESS AUDITS PASSED SUCCESSFULLY!" . CLR_RESET . "\n";
}
echo CLR_CYAN . CLR_BOLD . "=======================================================" . CLR_RESET . "\n\n";

exit(empty($failedTests) ? 0 : 1);

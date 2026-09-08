<?php
/**
 * ILikeSci — Unified Production Database Abstraction Layer
 * Supports high-concurrency MySQL with automatic, seamless fallback to SQLite.
 * Tuned for maximum performance on low-end hardware (WAL mode, index optimizations).
 */

if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database Connection Configuration (environment variables with XAMPP defaults)
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'ilikesci_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$pdo = null;
$dbEngine = 'mysql';

// 1. Try MySQL Connection
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2
    ]);
    $dbEngine = 'mysql';
} catch (Exception $e) {
    try {
        // Attempt to auto-create database if MySQL server is accessible
        $pdoServer = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2
        ]);
        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdoServer = null;

        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $dbEngine = 'mysql';
    } catch (Exception $serverEx) {
        $pdo = null;
    }
}

// 2. Fallback to SQLite (Zero-Config, Offline Resilient)
if (!$pdo) {
    try {
        $sqliteFile = __DIR__ . '/ilikesci_db.sqlite';
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $dbEngine = 'sqlite';

        // Performance Tuning for Low-End Hardware & Concurrent Reads/Writes
        $pdo->exec("PRAGMA journal_mode = WAL;");
        $pdo->exec("PRAGMA synchronous = NORMAL;");
        $pdo->exec("PRAGMA cache_size = -64000;"); // 64 MB memory cache
        $pdo->exec("PRAGMA foreign_keys = ON;");
        $pdo->exec("PRAGMA temp_store = MEMORY;");

        // Complete 14-Table SQLite Schema Definition
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                display_name TEXT DEFAULT '',
                email TEXT DEFAULT '',
                role TEXT DEFAULT 'teacher',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                grade TEXT NOT NULL,
                section TEXT DEFAULT 'A',
                recitations INTEGER DEFAULT 0,
                total_score INTEGER DEFAULT 0,
                photo TEXT
            )",
            "CREATE TABLE IF NOT EXISTS questions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                grade TEXT NOT NULL,
                topic TEXT NOT NULL,
                difficulty TEXT NOT NULL,
                question_text TEXT NOT NULL,
                type TEXT DEFAULT 'multiple-choice'
            )",
            "CREATE TABLE IF NOT EXISTS recitation_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL,
                topic TEXT,
                difficulty TEXT,
                points INTEGER DEFAULT 0,
                is_correct INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS curriculum_lessons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                grade TEXT NOT NULL,
                quarter TEXT DEFAULT '1',
                lesson_number TEXT DEFAULT '1',
                topic TEXT NOT NULL,
                content TEXT NOT NULL,
                objectives TEXT,
                questions TEXT,
                lesson_title TEXT,
                slides_json TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS lesson_slides (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                curriculum_lesson_id INTEGER NOT NULL,
                slide_number INTEGER NOT NULL,
                title TEXT NOT NULL,
                content TEXT NOT NULL,
                slide_type TEXT DEFAULT 'content',
                media_type TEXT,
                media_url TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS topics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                grade TEXT NOT NULL,
                topic_name TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS backups (
                id INTEGER PRIMARY KEY,
                state_json TEXT,
                last_updated DATETIME
            )",
            "CREATE TABLE IF NOT EXISTS teacher_profiles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                display_name TEXT DEFAULT '',
                bio TEXT DEFAULT '',
                avatar_data TEXT,
                border_style TEXT DEFAULT 'none',
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS student_grades (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_name TEXT NOT NULL,
                grade_level TEXT NOT NULL DEFAULT '4',
                section TEXT NOT NULL DEFAULT 'A',
                quarter INTEGER NOT NULL DEFAULT 1,
                gender TEXT DEFAULT 'M',
                ww_scores TEXT,
                ww_total REAL DEFAULT 0,
                ww_ps REAL DEFAULT 0,
                ww_ws REAL DEFAULT 0,
                pt_scores TEXT,
                pt_total REAL DEFAULT 0,
                pt_ps REAL DEFAULT 0,
                pt_ws REAL DEFAULT 0,
                qa_score REAL DEFAULT 0,
                qa_ps REAL DEFAULT 0,
                qa_ws REAL DEFAULT 0,
                initial_grade REAL DEFAULT 0,
                transmuted_grade INTEGER DEFAULT 0,
                ww_highest TEXT,
                pt_highest TEXT,
                qa_highest REAL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(student_name, grade_level, section, quarter)
            )",
            "CREATE TABLE IF NOT EXISTS multimedia_files (
                id TEXT PRIMARY KEY,
                category TEXT NOT NULL,
                name TEXT NOT NULL,
                type TEXT,
                size INTEGER,
                data TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS pptx_uploads (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filename TEXT NOT NULL,
                original_name TEXT NOT NULL,
                grade TEXT DEFAULT '',
                quarter TEXT DEFAULT '',
                topic TEXT DEFAULT '',
                slide_count INTEGER DEFAULT 0,
                curriculum_lesson_id INTEGER,
                slides_dir TEXT DEFAULT '',
                has_images INTEGER DEFAULT 0,
                uploaded_by TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS ai_cache (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                prompt_hash TEXT UNIQUE NOT NULL,
                action TEXT NOT NULL,
                prompt_text TEXT NOT NULL,
                response_text TEXT NOT NULL,
                provider TEXT DEFAULT 'gemini',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS ai_rate_limits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        ];

        foreach ($tables as $sql) {
            $pdo->exec($sql);
        }

        try {
            $cols = $pdo->query("PRAGMA table_info(teacher_profiles)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('username', $cols)) {
                $pdo->exec("DROP TABLE IF EXISTS teacher_profiles");
                $pdo->exec("CREATE TABLE teacher_profiles (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    display_name TEXT DEFAULT '',
                    bio TEXT DEFAULT '',
                    avatar_data TEXT,
                    border_style TEXT DEFAULT 'none',
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
            }
        } catch (Exception $e) {}

        try {
            $cols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('email', $cols)) $pdo->exec("ALTER TABLE users ADD COLUMN email TEXT DEFAULT ''");
            if (!in_array('role', $cols)) $pdo->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'teacher'");
            if (!in_array('display_name', $cols)) $pdo->exec("ALTER TABLE users ADD COLUMN display_name TEXT DEFAULT ''");
        } catch (Exception $e) {}

        try {
            $cols = $pdo->query("PRAGMA table_info(curriculum_lessons)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('quarter', $cols)) $pdo->exec("ALTER TABLE curriculum_lessons ADD COLUMN quarter TEXT DEFAULT '1'");
            if (!in_array('lesson_number', $cols)) $pdo->exec("ALTER TABLE curriculum_lessons ADD COLUMN lesson_number TEXT DEFAULT '1'");
            if (!in_array('questions', $cols)) $pdo->exec("ALTER TABLE curriculum_lessons ADD COLUMN questions TEXT");
            if (!in_array('content', $cols)) $pdo->exec("ALTER TABLE curriculum_lessons ADD COLUMN content TEXT");
            if (!in_array('objectives', $cols)) $pdo->exec("ALTER TABLE curriculum_lessons ADD COLUMN objectives TEXT");
        } catch (Exception $e) {}

    } catch (Exception $sqliteEx) {
        if (!headers_sent()) header("Content-Type: application/json");
        echo json_encode([
            "status" => "error",
            "message" => "Database initialization failed: " . $sqliteEx->getMessage()
        ]);
        exit;
    }
}

// 3. Define Constants & Engine Flag
if (!defined('DB_ENGINE')) {
    define('DB_ENGINE', $dbEngine);
}

// 4. Create Performance Indexes for Fast Execution on Low-End Hardware
try {
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_lessons_lookup ON curriculum_lessons(grade, quarter, lesson_number)",
        "CREATE INDEX IF NOT EXISTS idx_slides_lesson ON lesson_slides(curriculum_lesson_id, slide_number)",
        "CREATE INDEX IF NOT EXISTS idx_questions_filter ON questions(grade, topic, difficulty)",
        "CREATE INDEX IF NOT EXISTS idx_students_class ON students(grade, section)",
        "CREATE INDEX IF NOT EXISTS idx_recitation_student ON recitation_records(student_id, created_at)",
        "CREATE INDEX IF NOT EXISTS idx_grades_student ON student_grades(grade_level, section, quarter)",
        "CREATE INDEX IF NOT EXISTS idx_pptx_filter ON pptx_uploads(grade, quarter)",
        "CREATE INDEX IF NOT EXISTS idx_topics_grade ON topics(grade, topic_name)"
    ];

    foreach ($indexes as $idxSql) {
        $pdo->exec($idxSql);
    }
} catch (Exception $e) {
    // Indexes non-fatal if already handled by engine
}

// 5. Seed Default Admin Accounts if Users Table is Empty
try {
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount === 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, display_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $defaultUsers = [
            ['oyo', password_hash('admin123', PASSWORD_DEFAULT), 'Administrator Oyo', 'oyo@ilikesci.edu', 'admin'],
            ['tine', password_hash('teacher123', PASSWORD_DEFAULT), 'Teacher Christine', 'tine@ilikesci.edu', 'teacher'],
            ['dondell', password_hash('teacher123', PASSWORD_DEFAULT), 'Teacher Dondell', 'dondell@ilikesci.edu', 'teacher'],
            ['coney', password_hash('teacher123', PASSWORD_DEFAULT), 'Teacher Coney', 'coney@ilikesci.edu', 'teacher']
        ];
        foreach ($defaultUsers as $u) {
            $stmt->execute($u);
        }

        // Seed initial teacher profiles
        $stmtProfile = $pdo->prepare("INSERT INTO teacher_profiles (username, display_name, bio) VALUES (?, ?, ?)");
        $stmtProfile->execute(['oyo', 'Administrator Oyo', 'ILikeSci System Administrator']);
        $stmtProfile->execute(['tine', 'Teacher Christine', 'Grade 4 Science Teacher']);
        $stmtProfile->execute(['dondell', 'Teacher Dondell', 'Grade 5 Science Teacher']);
        $stmtProfile->execute(['coney', 'Teacher Coney', 'Grade 6 Science Teacher']);
    }
} catch (Exception $e) {}

// 6. Seed Standard Classroom Students Roster if Students Table is Empty
try {
    $studentCount = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    if ($studentCount === 0) {
        $defaultStudents = [
            // Grade 3
            [301, 'Alex Brown', '3', 'A', 4, 12],
            [302, 'Sophia Martinez', '3', 'A', 5, 15],
            [303, 'Ethan Williams', '3', 'A', 3, 9],
            [304, 'Olivia Taylor', '3', 'B', 4, 10],
            [305, 'Liam Johnson', '3', 'B', 6, 18],
            // Grade 4
            [401, 'Maria Garcia', '4', 'A', 8, 24],
            [402, 'Noah Thomas', '4', 'A', 7, 21],
            [403, 'Emma Jackson', '4', 'A', 9, 27],
            [404, 'Lucas White', '4', 'B', 5, 15],
            [405, 'Ava Harris', '4', 'B', 6, 18],
            // Grade 5
            [501, 'James Smith', '5', 'A', 6, 18],
            [502, 'Isabella Clark', '5', 'A', 7, 21],
            [503, 'Benjamin Lewis', '5', 'A', 5, 14],
            [504, 'Charlotte Robinson', '5', 'B', 8, 22],
            // Grade 6
            [601, 'Daniel Walker', '6', 'A', 9, 27],
            [602, 'Mia Hall', '6', 'A', 8, 24],
            [603, 'Henry Allen', '6', 'B', 6, 17],
            [604, 'Amelia Young', '6', 'B', 7, 20]
        ];

        $stmtStudent = $pdo->prepare("INSERT INTO students (id, name, grade, section, recitations, total_score) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($defaultStudents as $s) {
            $stmtStudent->execute($s);
        }
    }
} catch (Exception $e) {}

// 7. Universal Database Helper Functions
if (!function_exists('is_sqlite')) {
    function is_sqlite() {
        return defined('DB_ENGINE') && DB_ENGINE === 'sqlite';
    }
}

if (!function_exists('db_column_exists')) {
    function db_column_exists($pdo, $table, $column) {
        try {
            if (is_sqlite()) {
                $cols = $pdo->query("PRAGMA table_info(`$table`)")->fetchAll(PDO::FETCH_COLUMN, 1);
                return in_array($column, $cols);
            } else {
                $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
                $stmt->execute([$table, $column]);
                return (bool)$stmt->fetchColumn();
            }
        } catch (Exception $e) {
            return false;
        }
    }
}

<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ilikesci_db");
    $pdo->exec("USE ilikesci_db");
    
    // Create the backups table (for JSON state payload)
    $pdo->exec("CREATE TABLE IF NOT EXISTS backups (
        id INT PRIMARY KEY,
        state_json LONGTEXT NOT NULL,
        last_updated DATETIME NOT NULL
    )");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        display_name VARCHAR(100) DEFAULT '',
        role VARCHAR(20) DEFAULT 'teacher',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add missing columns if table already existed from older version
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'ilikesci_db' AND TABLE_NAME = 'users'")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('display_name', $cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN display_name VARCHAR(100) DEFAULT '' AFTER password");
    }
    if (!in_array('created_at', $cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER role");
    }

    // Insert admin users with hashed passwords
    // Admins: Oyo, Tine, Dondell, Coney Alcantara Quintos
    $admins = [
        ['oyo',     'oyo',     'Oyo',                      'admin'],
        ['tine',    'tine',    'Tine',                      'admin'],
        ['dondell', 'dondell', 'Dondell',                   'admin'],
        ['coney',   'coney',   'Coney Alcantara Quintos',   'admin']
    ];

    foreach ($admins as $a) {
        $hashed = password_hash($a[1], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, display_name, role) VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE password = VALUES(password), display_name = VALUES(display_name), role = VALUES(role)");
        $stmt->execute([$a[0], $hashed, $a[2], $a[3]]);
    }

    // Remove old 'admin' user if it exists (was a dev-only account)
    $pdo->exec("DELETE FROM users WHERE username = 'admin'");

    // Create teacher_profiles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        display_name VARCHAR(100) DEFAULT '',
        bio TEXT DEFAULT '',
        avatar_data LONGTEXT,
        border_style VARCHAR(30) DEFAULT 'none',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Auto-create profile entries for admins
    foreach ($admins as $a) {
        $pdo->prepare("INSERT IGNORE INTO teacher_profiles (username, display_name) VALUES (?, ?)")
            ->execute([$a[0], $a[2]]);
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id BIGINT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        grade VARCHAR(10) NOT NULL,
        section VARCHAR(10) DEFAULT 'A',
        recitations INT DEFAULT 0,
        total_score INT DEFAULT 0,
        photo LONGTEXT
    )");

    // Add section column if students table existed from older version
    try { $pdo->exec("ALTER TABLE students ADD COLUMN section VARCHAR(10) DEFAULT 'A' AFTER grade"); } catch(Exception $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS multimedia_files (
        id VARCHAR(100) PRIMARY KEY,
        category VARCHAR(50) NOT NULL,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(100),
        size INT,
        data LONGTEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        grade VARCHAR(10) NOT NULL,
        topic_name VARCHAR(100) NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        grade VARCHAR(10) NOT NULL,
        topic VARCHAR(100) NOT NULL,
        difficulty VARCHAR(20) NOT NULL,
        question_text TEXT NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'multiple-choice'
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS recitation_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id BIGINT NOT NULL,
        topic VARCHAR(100) NOT NULL,
        difficulty VARCHAR(20) NOT NULL,
        points INT NOT NULL,
        is_correct BOOLEAN NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )");

    // Create curriculum lessons table for imported lessons
    $pdo->exec("CREATE TABLE IF NOT EXISTS curriculum_lessons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        grade VARCHAR(10) NOT NULL,
        quarter VARCHAR(10) NOT NULL,
        lesson_number VARCHAR(10) NOT NULL,
        topic VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        objectives JSON,
        questions JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create lesson slides table for slide-by-slide lesson delivery
    $pdo->exec("CREATE TABLE IF NOT EXISTS lesson_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        curriculum_lesson_id INT NOT NULL,
        slide_number INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        slide_type VARCHAR(50) DEFAULT 'content',
        media_type VARCHAR(50) DEFAULT NULL,
        media_url TEXT DEFAULT NULL,
        FOREIGN KEY (curriculum_lesson_id) REFERENCES curriculum_lessons(id) ON DELETE CASCADE
    )");

    // Add missing columns if lesson_slides table already existed
    try {
        $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'ilikesci_db' AND TABLE_NAME = 'lesson_slides'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('media_type', $cols)) {
            $pdo->exec("ALTER TABLE lesson_slides ADD COLUMN media_type VARCHAR(50) DEFAULT NULL AFTER slide_type");
        }
        if (!in_array('media_url', $cols)) {
            $pdo->exec("ALTER TABLE lesson_slides ADD COLUMN media_url TEXT DEFAULT NULL AFTER media_type");
        }
    } catch(Exception $e) {}

    // Create PPTX uploads table for tracking imported PowerPoint files
    $pdo->exec("CREATE TABLE IF NOT EXISTS pptx_uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        grade VARCHAR(10) DEFAULT '',
        quarter VARCHAR(10) DEFAULT '',
        topic VARCHAR(255) DEFAULT '',
        slide_count INT DEFAULT 0,
        curriculum_lesson_id INT DEFAULT NULL,
        uploaded_by VARCHAR(50) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Student grades table — DepEd E-Class Record (WW 40%, PT 40%, QA 20%)
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

    // AI cache table — stores cached AI responses for offline access
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_cache (
        id INT AUTO_INCREMENT PRIMARY KEY,
        prompt_hash VARCHAR(64) NOT NULL UNIQUE,
        action VARCHAR(50) NOT NULL,
        prompt_text TEXT NOT NULL,
        response_text MEDIUMTEXT NOT NULL,
        provider VARCHAR(20) DEFAULT 'groq',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_action (action),
        INDEX idx_created (created_at)
    )");

    // AI rate limiting table — prevents API abuse
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_rate_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(128) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_created (created_at)
    )");

    // Auto-sync topics table from curriculum_lessons, pptx_uploads, and questions
    try {
        $topicsToInsert = [];
        $stmtCL = $pdo->query("SELECT grade, topic FROM curriculum_lessons WHERE topic IS NOT NULL AND topic != ''");
        while ($r = $stmtCL->fetch(PDO::FETCH_ASSOC)) {
            $topicsToInsert[] = ['grade' => $r['grade'] ?: '4', 'topic' => trim($r['topic'])];
        }
        $stmtPU = $pdo->query("SELECT grade, topic, original_name FROM pptx_uploads");
        while ($r = $stmtPU->fetch(PDO::FETCH_ASSOC)) {
            $t = !empty($r['topic']) ? trim($r['topic']) : preg_replace('/\.(pptx?|ppt)$/i', '', $r['original_name']);
            $topicsToInsert[] = ['grade' => $r['grade'] ?: '4', 'topic' => trim($t)];
        }
        $stmtQ = $pdo->query("SELECT grade, topic FROM questions WHERE topic IS NOT NULL AND topic != ''");
        while ($r = $stmtQ->fetch(PDO::FETCH_ASSOC)) {
            $topicsToInsert[] = ['grade' => $r['grade'] ?: '4', 'topic' => trim($r['topic'])];
        }

        $stmtTCheck = $pdo->prepare("SELECT COUNT(*) FROM topics WHERE grade = ? AND topic_name = ?");
        $stmtTIns = $pdo->prepare("INSERT INTO topics (grade, topic_name) VALUES (?, ?)");

        foreach ($topicsToInsert as $ti) {
            if (empty($ti['topic'])) continue;
            $stmtTCheck->execute([$ti['grade'], $ti['topic']]);
            if ($stmtTCheck->fetchColumn() == 0) {
                $stmtTIns->execute([$ti['grade'], $ti['topic']]);
            }
        }
    } catch (Exception $exT) { /* ignore */ }

    echo "<h1 style='color:green;'>✅ Database initialized successfully!</h1>";
    echo "<h2>Admin Accounts Created:</h2><ul>";
    foreach ($admins as $a) {
        echo "<li><strong>{$a[2]}</strong> — username: <code>{$a[0]}</code>, password: <code>{$a[1]}</code> (role: {$a[3]})</li>";
    }
    echo "</ul>";
    echo "<p>All passwords are hashed with <code>password_hash()</code>.</p>";
    echo "<p><a href='login.html'>Go to Login →</a></p>";
} catch (PDOException $e) {
    echo "<h1 style='color:red;'>Database initialization failed!</h1><p>Make sure MySQL (via XAMPP) is running.</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

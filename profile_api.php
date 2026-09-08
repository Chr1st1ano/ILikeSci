<?php
require 'db.php';
header("Content-Type: application/json");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Ensure teacher_profiles table exists
try {
    if (is_sqlite()) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            display_name TEXT DEFAULT '',
            bio TEXT DEFAULT '',
            avatar_data TEXT,
            border_style TEXT DEFAULT 'none',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            display_name VARCHAR(100) DEFAULT '',
            bio TEXT DEFAULT '',
            avatar_data LONGTEXT,
            border_style VARCHAR(30) DEFAULT 'none',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }
} catch (Exception $e) { /* table may already exist */ }

// GET: Load profile
if ($method === 'GET') {
    $username = $_GET['username'] ?? '';
    if (!$username) {
        echo json_encode(['status' => 'error', 'message' => 'Username required']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM teacher_profiles WHERE username = ?");
    $stmt->execute([$username]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profile) {
        echo json_encode(['status' => 'success', 'profile' => $profile]);
    } else {
        echo json_encode(['status' => 'not_found', 'message' => 'No profile saved yet']);
    }
    exit;
}

// POST: Save profile
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    $username = $input['username'] ?? '';
    $displayName = $input['display_name'] ?? '';
    $bio = $input['bio'] ?? '';
    $avatarData = $input['avatar_data'] ?? null;
    $borderStyle = $input['border_style'] ?? 'none';
    
    if (!$username) {
        echo json_encode(['status' => 'error', 'message' => 'Username required']);
        exit;
    }
    
    try {
        // Upsert: insert or update
        if (is_sqlite()) {
            $stmt = $pdo->prepare("INSERT INTO teacher_profiles (username, display_name, bio, avatar_data, border_style) 
                VALUES (?, ?, ?, ?, ?) 
                ON CONFLICT(username) DO UPDATE SET 
                display_name = excluded.display_name,
                bio = excluded.bio,
                avatar_data = excluded.avatar_data,
                border_style = excluded.border_style");
        } else {
            $stmt = $pdo->prepare("INSERT INTO teacher_profiles (username, display_name, bio, avatar_data, border_style) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                display_name = VALUES(display_name),
                bio = VALUES(bio),
                avatar_data = VALUES(avatar_data),
                border_style = VALUES(border_style)");
        }
        $stmt->execute([$username, $displayName, $bio, $avatarData, $borderStyle]);
        
        echo json_encode(['status' => 'success', 'message' => 'Profile saved to database']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>

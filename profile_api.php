<?php
require 'db.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') { exit(0); }

// Ensure teacher_profiles table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        display_name VARCHAR(100) DEFAULT '',
        bio TEXT DEFAULT '',
        avatar_data LONGTEXT,
        border_style VARCHAR(30) DEFAULT 'none',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
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
    $input = json_decode(file_get_contents('php://input'), true);
    
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
        $stmt = $pdo->prepare("INSERT INTO teacher_profiles (username, display_name, bio, avatar_data, border_style) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            display_name = VALUES(display_name),
            bio = VALUES(bio),
            avatar_data = VALUES(avatar_data),
            border_style = VALUES(border_style)");
        $stmt->execute([$username, $displayName, $bio, $avatarData, $borderStyle]);
        
        echo json_encode(['status' => 'success', 'message' => 'Profile saved to database']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>

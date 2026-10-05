<?php
require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Current-User");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Ensure teacher_profiles columns exist
try {
    if (is_sqlite()) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            display_name TEXT DEFAULT '',
            bio TEXT DEFAULT '',
            avatar_data TEXT,
            border_style TEXT DEFAULT 'none',
            assigned_grade TEXT DEFAULT '4',
            assigned_section TEXT DEFAULT 'all',
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
            assigned_grade VARCHAR(50) DEFAULT '4',
            assigned_section VARCHAR(100) DEFAULT 'all',
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
    
    $stmt = $pdo->prepare("SELECT tp.*, u.role, 
                                  COALESCE(NULLIF(tp.assigned_grade, ''), u.assigned_grade, '4') AS assigned_grade,
                                  COALESCE(NULLIF(tp.assigned_section, ''), u.assigned_section, 'all') AS assigned_section
                           FROM teacher_profiles tp 
                           LEFT JOIN users u ON tp.username = u.username 
                           WHERE tp.username = ?");
    $stmt->execute([$username]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        // Fallback: check users table
        $uStmt = $pdo->prepare("SELECT username, display_name, role, assigned_grade, assigned_section FROM users WHERE username = ?");
        $uStmt->execute([$username]);
        $u = $uStmt->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            $profile = [
                'username' => $u['username'],
                'display_name' => $u['display_name'],
                'bio' => '',
                'avatar_data' => null,
                'border_style' => 'none',
                'role' => $u['role'],
                'assigned_grade' => $u['assigned_grade'] ?? '4',
                'assigned_section' => $u['assigned_section'] ?? 'all'
            ];
        }
    }
    
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

    $currentUser = get_current_user_context($pdo);
    $isAdmin = ($currentUser && ($currentUser['role'] ?? '') === 'admin');
    $isCli = (!$currentUser && php_sapi_name() === 'cli');

    // Check existing user assignment in database
    $checkUserStmt = $pdo->prepare("SELECT role, assigned_grade, assigned_section FROM users WHERE username = ?");
    $checkUserStmt->execute([$username]);
    $existingUser = $checkUserStmt->fetch(PDO::FETCH_ASSOC);

    // Non-admin teachers cannot reassign their own grade or section
    if (!$isAdmin && !$isCli && $existingUser) {
        $assignedGrade = $existingUser['assigned_grade'] ?? '4';
        $assignedSection = $existingUser['assigned_section'] ?? 'all';
    }

    // Absolute rule: Teacher Coney is strictly Grade 4 Science
    if (strtolower($username) === 'coney') {
        $assignedGrade = '4';
    }
    if ($assignedGrade === '7') {
        $assignedGrade = '4';
    }
    
    try {
        if (is_sqlite()) {
            $stmt = $pdo->prepare("INSERT INTO teacher_profiles (username, display_name, bio, avatar_data, border_style, assigned_grade, assigned_section) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON CONFLICT(username) DO UPDATE SET 
                display_name = excluded.display_name,
                bio = excluded.bio,
                avatar_data = COALESCE(excluded.avatar_data, teacher_profiles.avatar_data),
                border_style = excluded.border_style,
                assigned_grade = CASE WHEN excluded.assigned_grade != '' THEN excluded.assigned_grade ELSE teacher_profiles.assigned_grade END,
                assigned_section = CASE WHEN excluded.assigned_section != '' THEN excluded.assigned_section ELSE teacher_profiles.assigned_section END");
        } else {
            $stmt = $pdo->prepare("INSERT INTO teacher_profiles (username, display_name, bio, avatar_data, border_style, assigned_grade, assigned_section) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                display_name = VALUES(display_name),
                bio = VALUES(bio),
                avatar_data = COALESCE(VALUES(avatar_data), avatar_data),
                border_style = VALUES(border_style),
                assigned_grade = CASE WHEN VALUES(assigned_grade) != '' THEN VALUES(assigned_grade) ELSE assigned_grade END,
                assigned_section = CASE WHEN VALUES(assigned_section) != '' THEN VALUES(assigned_section) ELSE assigned_section END");
        }
        $stmt->execute([$username, $displayName, $bio, $avatarData, $borderStyle, $assignedGrade, $assignedSection]);

        // Sync updates to users table
        $userUpdates = [];
        $userParams = [];
        if ($displayName !== '') {
            $userUpdates[] = "display_name = ?";
            $userParams[] = $displayName;
        }
        if ($assignedGrade !== '') {
            $userUpdates[] = "assigned_grade = ?";
            $userParams[] = $assignedGrade;
        }
        if ($assignedSection !== '') {
            $userUpdates[] = "assigned_section = ?";
            $userParams[] = $assignedSection;
        }
        if (!empty($userUpdates)) {
            $userParams[] = $username;
            $pdo->prepare("UPDATE users SET " . implode(", ", $userUpdates) . " WHERE username = ?")->execute($userParams);
        }

        // Update session if currently logged in
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!empty($_SESSION['user']) && $_SESSION['user']['username'] === $username) {
            if ($displayName !== '') $_SESSION['user']['display_name'] = $displayName;
            if ($assignedGrade !== '') $_SESSION['user']['assigned_grade'] = $assignedGrade;
            if ($assignedSection !== '') $_SESSION['user']['assigned_section'] = $assignedSection;
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Profile saved to database',
            'assigned_grade' => $assignedGrade,
            'assigned_section' => $assignedSection
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>

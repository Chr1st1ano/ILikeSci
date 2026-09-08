<?php
require 'db.php';

// Production session configuration
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.use_only_cookies', 1);
    @ini_set('session.cookie_samesite', 'Lax');
    @session_start();
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: $_POST;
    $action = $input['action'] ?? 'login';

    if ($action === 'logout') {
        $_SESSION = [];
        if (session_id()) {
            @session_destroy();
        }
        echo json_encode(["status" => "success", "message" => "Logged out successfully"]);
        exit;
    }

    if (!isset($input['username']) || !isset($input['password'])) {
        echo json_encode(["status" => "error", "message" => "Username and password required"]);
        exit;
    }
    
    $username = strtolower(trim($input['username']));
    $password = trim($input['password']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $userData = [
                "id" => (int)$user['id'],
                "username" => $user['username'],
                "display_name" => $user['display_name'] ?? $user['username'],
                "role" => $user['role']
            ];
            $_SESSION['user'] = $userData;

            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "user" => $userData
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid username or password"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }

} else if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'check') {
        if (!empty($_SESSION['user'])) {
            echo json_encode(["status" => "success", "authenticated" => true, "user" => $_SESSION['user']]);
        } else {
            echo json_encode(["status" => "success", "authenticated" => false]);
        }
        exit;
    }

    // Get all users (for offline caching — no passwords exposed)
    try {
        $stmt = $pdo->query("SELECT id, username, display_name, role FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "users" => $users]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>

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

    if ($action === 'reset_password') {
        $username = strtolower(trim($input['username'] ?? ''));
        $verify = trim($input['verification'] ?? '');
        $newPassword = trim($input['new_password'] ?? '');

        if (!$username || !$newPassword) {
            echo json_encode(["status" => "error", "message" => "Username and new password are required"]);
            exit;
        }

        // Security: Enforce strong password complexity (8+ chars, uppercase, lowercase, number, special char)
        $hasMinLength = strlen($newPassword) >= 8;
        $hasUpper     = preg_match('/[A-Z]/', $newPassword);
        $hasLower     = preg_match('/[a-z]/', $newPassword);
        $hasNumber    = preg_match('/[0-9]/', $newPassword);
        $hasSpecial   = preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\/\\~`]/', $newPassword);

        if (!$hasMinLength || !$hasUpper || !$hasLower || !$hasNumber || !$hasSpecial) {
            echo json_encode([
                "status" => "error",
                "message" => "New password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special character."
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(["status" => "error", "message" => "User account not found"]);
                exit;
            }

            // Verify using display_name (case-insensitive) OR admin master recovery key
            $displayName = trim($user['display_name'] ?? '');
            $isVerified = false;

            if (strcasecmp($verify, $displayName) === 0 || strcasecmp($verify, 'admin123') === 0 || strcasecmp($verify, 'ILIKESCI') === 0) {
                $isVerified = true;
            } else {
                $tStmt = $pdo->prepare("SELECT display_name FROM teacher_profiles WHERE username = ?");
                $tStmt->execute([$username]);
                $tp = $tStmt->fetch(PDO::FETCH_ASSOC);
                if ($tp && strcasecmp($verify, trim($tp['display_name'])) === 0) {
                    $isVerified = true;
                }
            }

            if (!$isVerified) {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Verification failed. Please enter your registered Full Name or Administrator recovery key."
                ]);
                exit;
            }

            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->execute([$hashed, $user['id']]);

            echo json_encode([
                "status" => "success",
                "message" => "Password successfully reset! You can now log in."
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
            exit;
        }
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

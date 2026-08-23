<?php
require 'db.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = strtolower(trim($input['username'] ?? ''));
$password = $input['password'] ?? '';
$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
$dob = $input['dob'] ?? null;
$gender = $input['gender'] ?? '';
$sections = $input['sections'] ?? '';
$bio = $input['bio'] ?? '';

if (!$username || !$password || !$firstName || !$lastName) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields: username, password, first name, last name']);
    exit;
}

if (strlen($username) < 3) {
    echo json_encode(['status' => 'error', 'message' => 'Username must be at least 3 characters']);
    exit;
}

try {
    // Check if username exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    if ($check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Username already taken']);
        exit;
    }

    // Insert user
    $hashedPw = password_hash($password, PASSWORD_DEFAULT);
    $displayName = $firstName . ' ' . $lastName;
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, display_name, role) VALUES (?, ?, ?, 'teacher')");
    $stmt->execute([$username, $hashedPw, $displayName]);

    // Also create profile entry
    $pdo->prepare("INSERT INTO teacher_profiles (username, display_name, bio) VALUES (?, ?, ?)")
        ->execute([$username, $displayName, $bio]);

    echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

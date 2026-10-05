<?php
require 'db.php';
header("Content-Type: application/json");

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: $_POST;
$username = strtolower(trim($input['username'] ?? ''));
$password = $input['password'] ?? '';
$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
$dob = $input['dob'] ?? null;
$gender = $input['gender'] ?? '';
$bio = $input['bio'] ?? '';

$assignedGrade = trim($input['assigned_grade'] ?? $input['grade'] ?? '4');
$assignedSection = trim($input['assigned_section'] ?? $input['section'] ?? $input['sections'] ?? 'all');
if ($assignedGrade === '7') $assignedGrade = '4'; // disallow Grade 7
if (empty($assignedSection)) $assignedSection = 'all';

if (!$username || !$password || !$firstName || !$lastName) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields: username, password, first name, last name']);
    exit;
}

if (strlen($username) < 3) {
    echo json_encode(['status' => 'error', 'message' => 'Username must be at least 3 characters']);
    exit;
}

// Security: Enforce strong password complexity (8+ chars, uppercase, lowercase, number, special char)
$hasMinLength = strlen($password) >= 8;
$hasUpper     = preg_match('/[A-Z]/', $password);
$hasLower     = preg_match('/[a-z]/', $password);
$hasNumber    = preg_match('/[0-9]/', $password);
$hasSpecial   = preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\/\\~`]/', $password);

if (!$hasMinLength || !$hasUpper || !$hasLower || !$hasNumber || !$hasSpecial) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.'
    ]);
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

    // Insert user with assigned grade and section
    $hashedPw = password_hash($password, PASSWORD_DEFAULT);
    $displayName = $firstName . ' ' . $lastName;
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, display_name, role, assigned_grade, assigned_section) VALUES (?, ?, ?, 'teacher', ?, ?)");
    $stmt->execute([$username, $hashedPw, $displayName, $assignedGrade, $assignedSection]);

    // Also create profile entry
    $pdo->prepare("INSERT INTO teacher_profiles (username, display_name, bio, assigned_grade, assigned_section) VALUES (?, ?, ?, ?, ?)")
        ->execute([$username, $displayName, $bio, $assignedGrade, $assignedSection]);

    echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

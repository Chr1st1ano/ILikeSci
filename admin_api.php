<?php
/**
 * Admin API — User management, system stats, DepEd grading export
 * Endpoints:
 *   GET    ?action=users           → List all users
 *   GET    ?action=stats           → System statistics
 *   POST   action=create_user      → Create new user
 *   POST   action=update_user      → Update user (name/email/password/role)
 *   POST   action=delete_user      → Delete user by id
 *   POST   action=export_eclass    → Export DepEd E-Class Record CSV
 */

require 'db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// Ensure email column exists
try {
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ilikesci_db' AND TABLE_NAME='users'")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('email', $cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(150) DEFAULT '' AFTER display_name");
    }
} catch (Exception $e) { /* ignore */ }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'users';

    if ($action === 'users') {
        // List all users (no passwords)
        $stmt = $pdo->query("SELECT id, username, display_name, email, role, created_at FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "users" => $users]);

    } elseif ($action === 'stats') {
        // System statistics
        $stats = [];
        $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        $stats['total_questions'] = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
        $stats['total_lessons'] = $pdo->query("SELECT COUNT(*) FROM curriculum_lessons")->fetchColumn();
        $stats['total_recitations'] = $pdo->query("SELECT COUNT(*) FROM recitation_records")->fetchColumn();
        
        // Per-grade student counts
        $gradeStmt = $pdo->query("SELECT grade, COUNT(*) as count FROM students GROUP BY grade ORDER BY grade");
        $stats['students_by_grade'] = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Per-grade question counts
        $qGradeStmt = $pdo->query("SELECT grade, COUNT(*) as count FROM questions GROUP BY grade ORDER BY grade");
        $stats['questions_by_grade'] = $qGradeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent recitations (last 10)
        try {
            $recentStmt = $pdo->query("SELECT r.*, s.name as student_name FROM recitation_records r LEFT JOIN students s ON r.student_id = s.id ORDER BY r.created_at DESC LIMIT 10");
            $stats['recent_recitations'] = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stats['recent_recitations'] = [];
        }

        echo json_encode(["status" => "success", "stats" => $stats]);

    } elseif ($action === 'export_eclass') {
        // Export DepEd E-Class Record as CSV
        $grade = $_GET['grade'] ?? 'all';
        $section = $_GET['section'] ?? 'A';
        $quarter = $_GET['quarter'] ?? '1';

        $query = "SELECT s.*, 
                    (SELECT COUNT(*) FROM recitation_records r WHERE r.student_id = s.id) as total_recitations,
                    (SELECT SUM(r.points) FROM recitation_records r WHERE r.student_id = s.id) as total_points,
                    (SELECT SUM(CASE WHEN r.is_correct = 1 THEN 1 ELSE 0 END) FROM recitation_records r WHERE r.student_id = s.id) as correct_answers,
                    (SELECT COUNT(*) FROM recitation_records r WHERE r.student_id = s.id) as total_attempts
                  FROM students s";
        $params = [];
        
        if ($grade !== 'all') {
            $query .= " WHERE s.grade = ?";
            $params[] = $grade;
        }
        $query .= " ORDER BY s.name ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // DepEd transmutation table (initial grade → transmuted grade)
        $transmutationTable = [
            100 => 100, 98.40 => 99, 96.80 => 98, 95.20 => 97, 93.60 => 96,
            92.00 => 95, 90.40 => 94, 88.80 => 93, 87.20 => 92, 85.60 => 91,
            84.00 => 90, 82.40 => 89, 80.80 => 88, 79.20 => 87, 77.60 => 86,
            76.00 => 85, 74.40 => 84, 72.80 => 83, 71.20 => 82, 69.60 => 81,
            68.00 => 80, 66.40 => 79, 64.80 => 78, 63.20 => 77, 61.60 => 76,
            60.00 => 75, 56.00 => 74, 52.00 => 73, 48.00 => 72, 44.00 => 71,
            40.00 => 70, 36.00 => 69, 32.00 => 68, 28.00 => 67, 24.00 => 66,
            20.00 => 65, 16.00 => 64, 12.00 => 63, 8.00 => 62, 4.00 => 61,
            0 => 60
        ];

        // Build E-Class Record CSV
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=ILikeSci_EClassRecord_Grade{$grade}_Q{$quarter}.csv");
        
        $output = fopen('php://output', 'w');
        
        // Header rows (DepEd E-Class Record format)
        fputcsv($output, ['SCHOOL CLASS RECORD IN SCIENCE']);
        fputcsv($output, ['(Pursuant to DepEd Order 8 series of 2015)']);
        fputcsv($output, []);
        fputcsv($output, ['GRADE & SECTION:', "Grade $grade - Section $section", '', '', 'TEACHER:', '', 'SUBJECT:', 'SCIENCE']);
        fputcsv($output, []);
        
        // Column headers matching GRADE-4-6_SCIENCE.xlsx structure
        $headers = ['No.', 'LEARNER\'S NAMES', 'WW Score', 'WW PS', 'WW WS (40%)', 'PT Score', 'PT PS', 'PT WS (40%)', 'QA Score', 'QA PS', 'QA WS (20%)', 'Initial Grade', 'Quarterly Grade', 'DepEd Level'];
        fputcsv($output, $headers);
        
        // HIGHEST POSSIBLE SCORE row
        fputcsv($output, ['', 'HIGHEST POSSIBLE SCORE', '', '100', '0.40', '', '100', '0.40', '', '100', '0.20', '', '', '']);
        
        $num = 1;
        foreach ($students as $s) {
            $totalAttempts = (int)$s['total_attempts'];
            $correctAnswers = (int)$s['correct_answers'];
            $totalPoints = (int)($s['total_points'] ?? $s['total_score']);
            
            // Written Works (WW) = quiz correctness score
            $wwScore = $totalAttempts > 0 ? round(($correctAnswers / $totalAttempts) * 100) : 0;
            $wwPS = $wwScore; // Percentage Score
            $wwWS = round($wwPS * 0.40, 2); // Weighted Score (40%)
            
            // Performance Tasks (PT) = participation points
            $maxPossiblePT = max($totalAttempts * 5, 1); // max 5 pts per recitation
            $ptScore = $totalPoints;
            $ptPS = min(round(($ptScore / $maxPossiblePT) * 100), 100);
            $ptWS = round($ptPS * 0.40, 2); // Weighted Score (40%)
            
            // Quarterly Assessment (QA) = composite recitation performance
            $qaScore = $totalAttempts > 0 ? round((($correctAnswers + $totalPoints) / ($totalAttempts * 2 + $maxPossiblePT)) * 100) : 0;
            $qaPS = min($qaScore, 100);
            $qaWS = round($qaPS * 0.20, 2); // Weighted Score (20%)
            
            // Initial Grade
            $initialGrade = round($wwWS + $ptWS + $qaWS, 2);
            
            // Transmute
            $transmuted = 60;
            foreach ($transmutationTable as $threshold => $grade_val) {
                if ($initialGrade >= $threshold) {
                    $transmuted = $grade_val;
                    break;
                }
            }
            
            // DepEd proficiency level
            $level = 'DNME';
            if ($transmuted >= 90) $level = 'O';
            elseif ($transmuted >= 85) $level = 'VS';
            elseif ($transmuted >= 80) $level = 'S';
            elseif ($transmuted >= 75) $level = 'FS';
            
            fputcsv($output, [
                $num++, $s['name'],
                $wwScore, $wwPS, $wwWS,
                $ptScore, $ptPS, $ptWS,
                $qaScore, $qaPS, $qaWS,
                $initialGrade, $transmuted, $level
            ]);
        }
        
        fclose($output);
        exit;
    }

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;
    
    $action = $input['action'] ?? '';

    if ($action === 'create_user') {
        $username = strtolower(trim($input['username'] ?? ''));
        $password = trim($input['password'] ?? '');
        $displayName = trim($input['display_name'] ?? $username);
        $email = trim($input['email'] ?? '');
        $role = $input['role'] ?? 'teacher';

        if (!$username || !$password) {
            echo json_encode(["status" => "error", "message" => "Username and password required"]);
            exit;
        }

        // Check if username already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            echo json_encode(["status" => "error", "message" => "Username '$username' already exists"]);
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, display_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashed, $displayName, $email, $role]);
        
        // Auto-create teacher profile
        $pdo->prepare("INSERT IGNORE INTO teacher_profiles (username, display_name) VALUES (?, ?)")
            ->execute([$username, $displayName]);

        echo json_encode(["status" => "success", "message" => "User '$username' created", "id" => $pdo->lastInsertId()]);

    } elseif ($action === 'update_user') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "User ID required"]);
            exit;
        }

        $updates = [];
        $params = [];

        if (isset($input['display_name']) && $input['display_name'] !== '') {
            $updates[] = "display_name = ?";
            $params[] = trim($input['display_name']);
        }
        if (isset($input['email'])) {
            $updates[] = "email = ?";
            $params[] = trim($input['email']);
        }
        if (isset($input['role']) && in_array($input['role'], ['admin', 'teacher'])) {
            $updates[] = "role = ?";
            $params[] = $input['role'];
        }
        if (isset($input['password']) && $input['password'] !== '') {
            $updates[] = "password = ?";
            $params[] = password_hash(trim($input['password']), PASSWORD_DEFAULT);
        }
        if (isset($input['username']) && $input['username'] !== '') {
            $newUsername = strtolower(trim($input['username']));
            // Check uniqueness
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->execute([$newUsername, $id]);
            if ($check->fetch()) {
                echo json_encode(["status" => "error", "message" => "Username '$newUsername' is already taken"]);
                exit;
            }
            $updates[] = "username = ?";
            $params[] = $newUsername;
        }

        if (empty($updates)) {
            echo json_encode(["status" => "error", "message" => "No fields to update"]);
            exit;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(["status" => "success", "message" => "User updated"]);

    } elseif ($action === 'delete_user') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "User ID required"]);
            exit;
        }

        // Prevent deleting the last admin
        $adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        $isAdmin = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $isAdmin->execute([$id]);
        $userRole = $isAdmin->fetchColumn();
        
        if ($userRole === 'admin' && $adminCount <= 1) {
            echo json_encode(["status" => "error", "message" => "Cannot delete the last admin account"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["status" => "success", "message" => "User deleted"]);

    } else {
        echo json_encode(["status" => "error", "message" => "Unknown action: $action"]);
    }
}
?>

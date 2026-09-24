<?php
require 'db.php';
$stmt = $pdo->query('SELECT grade, count(*) as count FROM students GROUP BY grade ORDER BY grade');
echo "STUDENT COUNTS BY GRADE (PHP/PDO):\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$statsStmt = $pdo->query("SELECT COUNT(*) FROM students");
echo "Total Students: " . $statsStmt->fetchColumn() . "\n";

// Sample Grade 4
$g4Sample = $pdo->query("SELECT * FROM students WHERE grade = '4' LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
echo "Grade 4 Sample:\n";
print_r($g4Sample);

// Sample Grade 6
$g6Sample = $pdo->query("SELECT * FROM students WHERE grade = '6' LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
echo "Grade 6 Sample:\n";
print_r($g6Sample);

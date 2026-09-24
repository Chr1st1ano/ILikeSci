<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
require 'student_api.php';
$out = ob_get_clean();

$data = json_decode($out, true);
echo "API status: " . ($data['status'] ?? 'error') . "\n";
echo "Total students: " . count($data['students'] ?? []) . "\n";

$byGrade = [];
$byGradeSection = [];
foreach ($data['students'] as $s) {
    $g = $s['grade'];
    $sec = $s['section'];
    $byGrade[$g] = ($byGrade[$g] ?? 0) + 1;
    $byGradeSection["$g - $sec"] = ($byGradeSection["$g - $sec"] ?? 0) + 1;
}
ksort($byGrade);
ksort($byGradeSection);

echo "\n--- BY GRADE ---\n";
print_r($byGrade);

echo "\n--- BY GRADE & SECTION ---\n";
print_r($byGradeSection);

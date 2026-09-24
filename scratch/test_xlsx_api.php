<?php
$_GET['action'] = 'list';
$_GET['grade'] = '4';
$_GET['section'] = 'Maagap';
$_GET['quarter'] = 1;

ob_start();
require 'xlsx_records_api.php';
$output = ob_get_clean();

$res = json_decode($output, true);
echo "Status: " . ($res['status'] ?? 'err') . "\n";
echo "Count for Grade 4 Maagap: " . ($res['count'] ?? 0) . "\n";
if (!empty($res['records'])) {
    echo "First student: " . $res['records'][0]['student_name'] . " (" . $res['records'][0]['gender'] . ")\n";
    echo "Last student: " . end($res['records'])['student_name'] . " (" . end($res['records'])['gender'] . ")\n";
}

// Test Grade 6 Pasteur
$_GET['grade'] = '6';
$_GET['section'] = 'Pasteur';
ob_start();
// Include again with new GET params
$sql = "SELECT * FROM student_grades WHERE quarter = ? AND grade_level = ? AND section = ? ORDER BY gender ASC, student_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([1, '6', 'Pasteur']);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nCount for Grade 6 Pasteur: " . count($records) . "\n";
if (!empty($records)) {
    echo "First student: " . $records[0]['student_name'] . " (" . $records[0]['gender'] . ")\n";
    echo "Last student: " . end($records)['student_name'] . " (" . end($records)['gender'] . ")\n";
}

<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'stats';
ob_start();
require 'admin_api.php';
$out = ob_get_clean();

$data = json_decode($out, true);
echo "Admin stats status: " . ($data['status'] ?? 'error') . "\n";
echo "Total students: " . ($data['stats']['total_students'] ?? 0) . "\n";
echo "Students by grade:\n";
print_r($data['stats']['students_by_grade'] ?? []);

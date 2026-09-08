<?php
/**
 * ILikeSci — Database Initialization & Health Check
 * Engine-Agnostic: Fully works on MySQL or SQLite
 */
require_once 'db.php';

$isCli = (php_sapi_name() === 'cli');

try {
    $engine = defined('DB_ENGINE') ? DB_ENGINE : 'unknown';
    
    if (!$isCli) {
        echo "<!DOCTYPE html><html><head><title>Database Initialization — ILikeSci</title>";
        echo "<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;line-height:1.6;color:#333;background:#f8fafc;padding:20px;border-radius:12px;} h1{color:#10b981;} code{background:#e2e8f0;padding:2px 6px;border-radius:4px;}</style></head><body>";
        echo "<h1>Database Initialized Successfully</h1>";
        echo "<p>Active Database Engine: <strong>" . strtoupper($engine) . "</strong></p>";
        echo "<h3>Table Status:</h3><ul>";
    } else {
        echo "=== ILikeSci Database Initialized ===\n";
        echo "Engine: " . strtoupper($engine) . "\n";
    }

    $tables = [
        'users', 'teacher_profiles', 'students', 'multimedia_files', 
        'topics', 'questions', 'recitation_records', 'curriculum_lessons', 
        'lesson_slides', 'pptx_uploads', 'student_grades', 'ai_cache', 
        'ai_rate_limits', 'backups'
    ];

    foreach ($tables as $tbl) {
        $count = $pdo->query("SELECT COUNT(*) FROM $tbl")->fetchColumn();
        if (!$isCli) {
            echo "<li>Table <code>$tbl</code>: <strong>$count</strong> records</li>";
        } else {
            echo "- $tbl: $count records\n";
        }
    }

    if (!$isCli) {
        echo "</ul>";
        echo "<p><a href='login.html' style='display:inline-block;padding:10px 20px;background:#3b82f6;color:white;text-decoration:none;border-radius:8px;'>Go to Login &rarr;</a></p>";
        echo "</body></html>";
    }
} catch (Exception $e) {
    if (!$isCli) {
        echo "<h1 style='color:#ef4444;'>Database Initialization Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

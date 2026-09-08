<?php
/**
 * Export Matter & Materials units to JSON files in exports/ directory.
 * Synchronized directly with seed_matter_materials.php!
 */

require_once __DIR__ . '/seed_matter_materials.php';

$outDir = __DIR__ . '/exports';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

// Format for ILikeSci materials.html / JSON importer
$allLessons = [];
$allQuestions = [];

foreach ($matterUnits as $u) {
    $allLessons[] = [
        'title' => $u['topic'],
        'grade' => $u['grade'],
        'quarter' => $u['quarter'],
        'lesson_number' => $u['lesson_number'],
        'objectives' => $u['objectives'],
        'content' => $u['content'],
        'slides' => array_map(function($s) {
            return [
                'title' => $s['title'],
                'content' => $s['content'],
                'mediaType' => $s['media_type'] ?? 'none',
                'mediaUrl' => $s['media_url'] ?? ''
            ];
        }, $u['slides'])
    ];

    foreach ($u['questions'] as $q) {
        $allQuestions[] = [
            'grade' => $q['grade'],
            'topic' => $q['topic'],
            'difficulty' => $q['difficulty'],
            'text' => $q['text']
        ];
    }
}

// 1. Complete Package
$completeData = [
    'title' => 'DepEd Science MATATAG Curriculum — Complete Matter and Materials Package (Grades 3-6)',
    'description' => 'Comprehensive curriculum units, multi-slide presentations, and tiered recitation questions.',
    'customLessons' => $allLessons,
    'questions' => $allQuestions
];

$completeFile = $outDir . '/material_matter_and_materials_complete.json';
file_put_contents($completeFile, json_encode($completeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "<p>Exported Complete Package: $completeFile</p>\n";

// 2. Grade-Specific Packages
foreach (['3', '4', '5', '6'] as $g) {
    $gLessons = array_values(array_filter($allLessons, function($l) use ($g) { return $l['grade'] === $g; }));
    $gQuestions = array_values(array_filter($allQuestions, function($q) use ($g) { return $q['grade'] === $g; }));

    $gData = [
        'title' => "DepEd Science MATATAG Curriculum — Grade $g Matter and Materials",
        'grade' => $g,
        'customLessons' => $gLessons,
        'questions' => $gQuestions
    ];

    $gFile = $outDir . "/material_g{$g}_matter.json";
    file_put_contents($gFile, json_encode($gData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "<p>Exported Grade $g Package (" . count($gLessons) . " lessons, " . count($gQuestions) . " questions): $gFile</p>\n";
}

if (file_exists(__DIR__ . '/generate_matter_json.py')) {
    unlink(__DIR__ . '/generate_matter_json.py');
}

echo "<h3>🎉 All JSON Material Packages exported successfully to /exports!</h3>\n";
?>

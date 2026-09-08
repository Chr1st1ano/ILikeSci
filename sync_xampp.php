<?php
require_once 'db.php';

$checks = [];

// 1. PHP Version
$phpVersion = phpversion();
$checks['php_version'] = [
    'title' => 'PHP Version',
    'value' => $phpVersion,
    'status' => version_compare($phpVersion, '7.4.0', '>=') ? 'pass' : 'warn',
    'msg' => 'PHP 7.4+ is recommended for ILikeSci.'
];

// 2. Database Engine & Connection
$checks['db_engine'] = [
    'title' => 'Database Engine',
    'value' => strtoupper($dbEngine ?? 'UNKNOWN'),
    'status' => $pdo ? 'pass' : 'fail',
    'msg' => $pdo ? ($dbEngine === 'mysql' ? 'Connected to XAMPP MySQL (Port 3306)' : 'Running on portable SQLite engine') : 'Database connection failed'
];

// 3. Critical Extensions
$extensions = ['pdo', 'pdo_mysql', 'pdo_sqlite', 'json', 'mbstring', 'gd', 'zip'];
$missingExt = [];
foreach ($extensions as $ext) {
    if (!extension_loaded($ext)) $missingExt[] = $ext;
}
$checks['extensions'] = [
    'title' => 'PHP Extensions',
    'value' => empty($missingExt) ? 'All Active (' . implode(', ', $extensions) . ')' : 'Missing: ' . implode(', ', $missingExt),
    'status' => empty($missingExt) ? 'pass' : 'warn',
    'msg' => empty($missingExt) ? 'All required multimedia and database extensions are loaded.' : 'Enable missing extensions in php.ini if needed.'
];

// 4. Database Tables & Records
$tableCounts = [];
if ($pdo) {
    $tables = ['users', 'students', 'questions', 'curriculum_lessons', 'recitation_records'];
    foreach ($tables as $t) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            $tableCounts[$t] = $count;
        } catch (Exception $e) {
            $tableCounts[$t] = 'Not Found';
        }
    }
}
$checks['tables'] = [
    'title' => 'Database Tables & Records',
    'value' => !empty($tableCounts) ? json_encode($tableCounts) : 'None',
    'status' => (!empty($tableCounts) && !in_array('Not Found', $tableCounts, true)) ? 'pass' : 'warn',
    'msg' => 'Counts: ' . implode(', ', array_map(fn($k, $v) => "$k ($v)", array_keys($tableCounts), $tableCounts))
];

// 5. Upload Directories Write Permissions
$uploadDirs = [__DIR__ . '/uploads', __DIR__ . '/pptx_slides'];
$dirStatus = [];
foreach ($uploadDirs as $dir) {
    if (!file_exists($dir)) @mkdir($dir, 0777, true);
    $dirStatus[basename($dir)] = is_writable($dir) ? 'Writable' : 'Not Writable';
}
$checks['writable'] = [
    'title' => 'Upload Storage Permissions',
    'value' => json_encode($dirStatus),
    'status' => !in_array('Not Writable', $dirStatus, true) ? 'pass' : 'warn',
    'msg' => 'Required for PPTX presentations and local student media.'
];

// Handle AJAX Sync Action
if (isset($_GET['action']) && $_GET['action'] === 'reinit') {
    header('Content-Type: application/json');
    try {
        include 'init_db.php';
        echo json_encode(['status' => 'success', 'message' => 'XAMPP database re-synchronized successfully!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ILikeSci — XAMPP Diagnostic & Sync Tool</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    body { background: #0f172a; color: #f8fafc; font-family: 'Outfit', sans-serif; padding: 30px; }
    .diag-box { max-width: 800px; margin: 0 auto; background: #1e293b; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
    .diag-item { display: flex; justify-content: space-between; align-items: center; padding: 14px; background: rgba(255,255,255,0.04); border-radius: 10px; margin-bottom: 12px; }
    .status-badge { padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 12px; }
    .status-pass { background: rgba(16,185,129,0.2); color: #10b981; border: 1px solid #10b981; }
    .status-warn { background: rgba(245,158,11,0.2); color: #f59e0b; border: 1px solid #f59e0b; }
    .status-fail { background: rgba(239,68,68,0.2); color: #ef4444; border: 1px solid #ef4444; }
  </style>
</head>
<body>
  <div class="diag-box">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 16px;">
      <div>
        <h2 style="margin:0; font-size:1.6rem; color:#60a5fa;"><i class="fa-solid fa-server text-primary"></i> XAMPP & System Sync Diagnostic</h2>
        <p style="margin:4px 0 0 0; font-size:13px; color:#94a3b8;">ILikeSci offline platform health & database status</p>
      </div>
      <a href="index.html" class="btn btn-secondary btn-sm"><i class="fa-solid fa-house"></i> Open ILikeSci</a>
    </div>

    <?php foreach ($checks as $k => $c): ?>
      <div class="diag-item">
        <div>
          <strong style="font-size:15px;"><?= htmlspecialchars($c['title']) ?></strong>
          <div style="font-size:12px; color:#94a3b8; margin-top:4px;"><?= htmlspecialchars($c['msg']) ?></div>
        </div>
        <div style="text-align:right;">
          <span class="status-badge status-<?= $c['status'] ?>"><?= strtoupper($c['status']) ?></span>
          <div style="font-size:12px; font-weight:600; color:#cbd5e1; margin-top:4px;"><?= htmlspecialchars($c['value']) ?></div>
        </div>
      </div>
    <?php endforeach; ?>

    <div style="display:flex; gap:12px; margin-top: 24px; flex-wrap:wrap;">
      <button class="btn btn-primary" onclick="reinitDatabase()"><i class="fa-solid fa-rotate"></i> Sync / Re-initialize XAMPP Database</button>
      <button class="btn btn-secondary" onclick="location.reload()"><i class="fa-solid fa-arrows-rotate"></i> Re-check Status</button>
    </div>

    <div id="sync-result" style="margin-top:16px; font-weight:600;"></div>
  </div>

  <script>
    async function reinitDatabase() {
      const resEl = document.getElementById('sync-result');
      resEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Synchronizing database...';
      try {
        const res = await fetch('init_db.php');
        resEl.innerHTML = '<span style="color:#10b981;">✅ Database synchronized with XAMPP MySQL successfully! Refreshing...</span>';
        setTimeout(() => location.reload(), 1500);
      } catch(e) {
        resEl.innerHTML = '<span style="color:#ef4444;">❌ Synchronization error: ' + e.message + '</span>';
      }
    }
  </script>
</body>
</html>

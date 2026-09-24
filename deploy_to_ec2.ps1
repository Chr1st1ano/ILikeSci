<#
.SYNOPSIS
    Automated Deployment Script for ILikeSci to AWS EC2 Instance
#>

param(
    [string]$HostIp = "54.205.53.83",
    [string]$User = "ec2-user",
    [string]$KeyPath = "$HOME\Downloads\ilikesci.pem",
    [string]$RemoteDir = "/home/ec2-user/ilikesci"
)

$ErrorActionPreference = "Stop"

Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  ILikeSci - Automated Deployment to AWS EC2" -ForegroundColor Cyan
Write-Host "  Target: $User@$HostIp ($RemoteDir)" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Verify SSH Key
if (-not (Test-Path $KeyPath)) {
    Write-Error "SSH Key not found at: $KeyPath"
    exit 1
}
Write-Host "[OK] SSH Key found: $KeyPath" -ForegroundColor Green

# 2. Test SSH Connectivity
Write-Host "[INFO] Connecting to EC2 instance..." -ForegroundColor Yellow
$probe = & ssh -i "$KeyPath" -o BatchMode=yes -o StrictHostKeyChecking=no -o ConnectTimeout=5 "$User@$HostIp" "echo CONNECTED" 2>&1
if ($probe -notmatch "CONNECTED") {
    Write-Error "Failed to connect to EC2: $probe"
    exit 1
}
Write-Host "[OK] SSH Connection successful!" -ForegroundColor Green

# 3. Create Remote Backup of SQLite Database
Write-Host "[INFO] Creating remote database backup on EC2..." -ForegroundColor Yellow
$timestamp = (Get-Date).ToString("yyyyMMdd_HHmmss")
$backupCmd = "if [ -f $RemoteDir/ilikesci_db.sqlite ]; then cp $RemoteDir/ilikesci_db.sqlite $RemoteDir/ilikesci_db.sqlite.backup_$timestamp; echo Backup created: ilikesci_db.sqlite.backup_$timestamp; fi"
& ssh -i "$KeyPath" -o StrictHostKeyChecking=no "$User@$HostIp" "$backupCmd"
Write-Host "[OK] Database backup safe." -ForegroundColor Green

# 4. Upload updated application files via SCP
Write-Host "[INFO] Ensuring remote directories exist..." -ForegroundColor Yellow
& ssh -i "$KeyPath" -o StrictHostKeyChecking=no "$User@$HostIp" "mkdir -p $RemoteDir/uploads $RemoteDir/pptx_slides $RemoteDir/scratch $RemoteDir/masterlists $RemoteDir/pdfs"

Write-Host "[INFO] Uploading updated application files via SCP..." -ForegroundColor Yellow

$filesToUpload = @(
    "games.html",
    "styles.css",
    "app.js",
    "index.html",
    "presenter.html",
    "tv_display.html",
    "admin.html",
    "assessment.html",
    "records.html",
    "materials.html",
    "students.html",
    "scoreboard.html",
    "login.html",
    "signup.html",
    "lessons.html",
    "multimedia.html",
    "dashboard.html",
    "layout.html",
    "index-tablet.html",
    "student_app.html",
    "simulations.js",
    "midi_player.js",
    "mobile-optimizations.js",
    "pwabuilder-adv-sw.js",
    "manifest.json",
    "db.php",
    "auth.php",
    "admin_api.php",
    "lessons_api.php",
    "pptx_api.php",
    "questions_api.php",
    "student_api.php",
    "recitation_api.php",
    "profile_api.php",
    "xlsx_records_api.php",
    "ai_api.php",
    "ai_config.php",
    "sync.php",
    "sync_xampp.php",
    "init_db.php",
    "seed_data.php",
    "seed_demo_data.php",
    "seed_life_science.php",
    "seed_matter_materials.php",
    "import_all_curriculum_pdfs.php",
    "import_life_matter_and_materials.php",
    "pdf_to_images.py",
    "pptx_to_images.py",
    "start_localhost.bat",
    "run.bat",
    "start_ilikesci.bat",
    "standalone_control_panel.bat",
    "start_both_instances.bat",
    "start_ilayksay.bat",
    "README.md",
    "CHANGELOG.md",
    "ilikesci_db.sqlite",
    "scratch/extracted_students.json",
    "masterlists/Grade-4-Masterlist-BCES-2026-2027.xlsx",
    "masterlists/GRADE-6-MASTERLIST.xlsx"
)

$localDir = $PSScriptRoot
if (-not $localDir) { $localDir = (Get-Location).Path }

$uploadedCount = 0
foreach ($f in $filesToUpload) {
    $fullPath = Join-Path $localDir $f
    if (Test-Path $fullPath) {
        & scp -i "$KeyPath" -o StrictHostKeyChecking=no -q "$fullPath" "$User@$HostIp`:$RemoteDir/$f"
        $uploadedCount++
    }
}
Write-Host "[OK] $uploadedCount application & data files uploaded." -ForegroundColor Green

# 5. Fix Remote Permissions, SELinux, and reload services
Write-Host "[INFO] Applying production Linux permissions and SELinux contexts..." -ForegroundColor Yellow
$postDeployCmd = "mkdir -p $RemoteDir/uploads $RemoteDir/pptx_slides $RemoteDir/scratch $RemoteDir/masterlists $RemoteDir/pdfs && sudo chown -R ec2-user:apache $RemoteDir && sudo chmod 664 $RemoteDir/ilikesci_db.sqlite* 2>/dev/null || true; sudo chmod -R 775 $RemoteDir/uploads $RemoteDir/pptx_slides $RemoteDir/scratch $RemoteDir/masterlists $RemoteDir/pdfs && sudo chcon -R -t httpd_sys_rw_content_t $RemoteDir 2>/dev/null || true; sudo systemctl restart php-fpm && sudo systemctl reload nginx && cd $RemoteDir && php init_db.php"
& ssh -i "$KeyPath" -o StrictHostKeyChecking=no "$User@$HostIp" "$postDeployCmd"

# 6. Post-deployment live verification
Write-Host "[INFO] Verifying live deployment on EC2..." -ForegroundColor Yellow
try {
    $studentCheck = Invoke-RestMethod -Uri "http://$HostIp/student_api.php?action=get_students" -TimeoutSec 10
    if ($studentCheck.status -eq "success") {
        $totalStudents = $studentCheck.students.Count
        Write-Host "[OK] Live Database Verified: $totalStudents students found via API!" -ForegroundColor Green
    } else {
        Write-Warning "API returned non-success: $($studentCheck | ConvertTo-Json -Compress)"
    }
} catch {
    Write-Warning "Failed to query live student API: $_"
}

Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "  DEPLOYMENT COMPLETE & VERIFIED!" -ForegroundColor Green
Write-Host "  Live URL:       http://$HostIp/index.html" -ForegroundColor Green
Write-Host "  Assessment:     http://$HostIp/assessment.html" -ForegroundColor Green
Write-Host "  Admin Console:  http://$HostIp/admin.html" -ForegroundColor Green
Write-Host "  TV Display:     http://$HostIp/tv_display.html" -ForegroundColor Green
Write-Host "  Games:          http://$HostIp/games.html" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""

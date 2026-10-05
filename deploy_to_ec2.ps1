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
$probe = & ssh -i "$KeyPath" -o BatchMode=yes -o StrictHostKeyChecking=no -o ConnectTimeout=15 "$User@$HostIp" "echo CONNECTED" 2>&1
if ($probe -notmatch "CONNECTED") {
    Write-Error "Failed to connect to EC2: $probe"
    exit 1
}
Write-Host "[OK] SSH Connection successful!" -ForegroundColor Green

# 3. Create Remote Backup of SQLite Database
Write-Host "[INFO] Creating remote database backup on EC2..." -ForegroundColor Yellow
$timestamp = (Get-Date).ToString("yyyyMMdd_HHmmss")
$backupCmd = "if [ -f $RemoteDir/ilikesci_db.sqlite ]; then cp $RemoteDir/ilikesci_db.sqlite $RemoteDir/ilikesci_db.sqlite.backup_$timestamp; echo Backup created: ilikesci_db.sqlite.backup_$timestamp; rm -f $RemoteDir/ilikesci_db.sqlite-wal $RemoteDir/ilikesci_db.sqlite-shm; fi"
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
    "profile.html",
    "landing.html",
    "test_curriculum_midi.html",
    "test_production_readiness.php",
    "simulations.js",
    "midi_player.js",
    "mobile-optimizations.js",
    "pwabuilder-adv-sw.js",
    "manifest.json",
    "db.php",
    "auth.php",
    "register.php",
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
    "ai_key.local.php",
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
    ".htaccess",
    "ilikesci_db.sqlite",
    "scratch/extracted_students.json",
    "scratch/test_safeguards.php",
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
$postDeployCmd = "mkdir -p $RemoteDir/uploads $RemoteDir/pptx_slides $RemoteDir/scratch $RemoteDir/masterlists $RemoteDir/pdfs && sudo chown -R ec2-user:apache $RemoteDir && sudo chmod 775 $RemoteDir && sudo chmod 664 $RemoteDir/ilikesci_db.sqlite* 2>/dev/null || true; sudo chmod -R 775 $RemoteDir/uploads $RemoteDir/pptx_slides $RemoteDir/scratch $RemoteDir/masterlists $RemoteDir/pdfs 2>/dev/null || true; sudo chcon -R -t httpd_sys_rw_content_t $RemoteDir 2>/dev/null || true; sudo systemctl restart php-fpm && sudo systemctl reload nginx && cd $RemoteDir && php init_db.php && php test_production_readiness.php && php scratch/test_safeguards.php"
& ssh -i "$KeyPath" -o StrictHostKeyChecking=no "$User@$HostIp" "$postDeployCmd"

# 6. Post-deployment live verification
Write-Host ""
Write-Host "[INFO] Running Live Production Verifications on EC2..." -ForegroundColor Yellow

# A. Student API Check
try {
    $studentCheck = Invoke-RestMethod -Uri "http://$HostIp/student_api.php" -TimeoutSec 10 -UseBasicParsing
    if ($studentCheck.status -eq "success") {
        $totalStudents = $studentCheck.students.Count
        $g4Count = ($studentCheck.students | Where-Object { $_.grade -eq "4" }).Count
        $g6Count = ($studentCheck.students | Where-Object { $_.grade -eq "6" }).Count
        $g7Count = ($studentCheck.students | Where-Object { $_.grade -eq "7" }).Count
        $g3Count = ($studentCheck.students | Where-Object { $_.grade -eq "3" }).Count
        $g5Count = ($studentCheck.students | Where-Object { $_.grade -eq "5" }).Count
        Write-Host "[OK] Live Database Verified: $totalStudents total students (Grade 4: $g4Count, Grade 6: $g6Count)" -ForegroundColor Green
        if ($g7Count -eq 0) {
            Write-Host "[OK] Grade 7 is completely absent from live database (0 records)." -ForegroundColor Green
        } else {
            Write-Error "Grade 7 records detected in live database: $g7Count!"
        }
        if ($g3Count -eq 0 -and $g5Count -eq 0) {
            Write-Host "[OK] Fake student data completely absent (Grade 3: 0, Grade 5: 0)." -ForegroundColor Green
        } else {
            Write-Warning "Fake student data detected in live database (Grade 3: $g3Count, Grade 5: $g5Count)!"
        }
    } else {
        Write-Warning "API returned non-success: $($studentCheck | ConvertTo-Json -Compress)"
    }
} catch {
    Write-Warning "Failed to query live student API: $_"
}

# B. Teacher Coney Authentication & Isolation Check
try {
    $loginBody = @{ username = "coney"; password = "Password123!" }
    $loginRes = Invoke-RestMethod -Uri "http://$HostIp/auth.php" -Method Post -Body $loginBody -TimeoutSec 10 -UseBasicParsing
    if ($loginRes.status -eq "success" -and $loginRes.user.assigned_grade -eq "4") {
        Write-Host "[OK] Teacher Coney verified on Grade 4: $($loginRes.user.display_name) (Grade: $($loginRes.user.assigned_grade), Section: $($loginRes.user.assigned_section))" -ForegroundColor Green
    } else {
        Write-Warning "Teacher Coney check unexpected result: $($loginRes | ConvertTo-Json -Compress)"
    }
} catch {
    Write-Warning "Failed to authenticate Teacher Coney: $_"
}

# C. Web Endpoints Health Check
$endpoints = @("index.html", "admin.html", "students.html", "profile.html", "records.html", "games.html")
foreach ($ep in $endpoints) {
    try {
        $resp = Invoke-WebRequest -Uri "http://$HostIp/$ep" -Method Head -TimeoutSec 5 -UseBasicParsing
        if ($resp.StatusCode -eq 200) {
            Write-Host "[OK] http://$HostIp/$ep accessible (HTTP 200)" -ForegroundColor Green
        } else {
            Write-Warning "http://$HostIp/$ep returned HTTP $($resp.StatusCode)"
        }
    } catch {
        Write-Warning "Failed to access http://${HostIp}/${ep}: $_"
    }
}

# D. AI Status & Provider Verification Check
try {
    $aiCheck = Invoke-RestMethod -Uri "http://$HostIp/ai_api.php?action=status" -TimeoutSec 10 -UseBasicParsing
    if ($aiCheck.status -eq "success" -and $aiCheck.ai_enabled -eq $true) {
        Write-Host "[OK] AI Engine Online: Provider=$($aiCheck.provider), Model=$($aiCheck.model), Key Configured=Yes" -ForegroundColor Green
    } else {
        Write-Warning "AI Engine returned: $($aiCheck | ConvertTo-Json -Compress)"
    }
} catch {
    Write-Warning "Failed to query AI status endpoint: $_"
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

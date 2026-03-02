# ========================================================
# AUTO SETUP SCRIPT - Nginx Optimization
# ========================================================
# Script ini akan otomatis setup optimasi Nginx
# untuk POS Cafe Prototype
# ========================================================

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  NGINX OPTIMIZATION AUTO-SETUP" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Define paths
$vhostDir = "C:\laragon\etc\nginx\sites-enabled"
$oldFile = "$vhostDir\auto.pos-cafe-prototype.test.conf"
$newFile = "$vhostDir\pos-cafe-prototype.test.conf"
$backupFile = "$vhostDir\pos-cafe-prototype.test.conf.BACKUP"
$templateFile = "C:\laragon\www\pos-cafe-prototype\nginx-vhost-optimized.conf"

# Step 1: Check if original file exists
Write-Host "Step 1: Checking files..." -ForegroundColor Yellow
if (Test-Path $oldFile) {
    Write-Host "  ✓ Found: auto.pos-cafe-prototype.test.conf" -ForegroundColor Green
} elseif (Test-Path $newFile) {
    Write-Host "  ✓ Found: pos-cafe-prototype.test.conf (already renamed)" -ForegroundColor Green
    $oldFile = $newFile
} else {
    Write-Host "  ✗ Error: Config file not found!" -ForegroundColor Red
    Write-Host "  Looking for: $oldFile" -ForegroundColor Red
    exit 1
}

# Step 2: Create backup
Write-Host "`nStep 2: Creating backup..." -ForegroundColor Yellow
if (Test-Path $newFile) {
    Copy-Item -Path $newFile -Destination $backupFile -Force
    Write-Host "  ✓ Backup created: $backupFile" -ForegroundColor Green
} elseif (Test-Path $oldFile) {
    Copy-Item -Path $oldFile -Destination $backupFile -Force
    Write-Host "  ✓ Backup created: $backupFile" -ForegroundColor Green
}

# Step 3: Rename file (remove auto. prefix)
Write-Host "`nStep 3: Renaming file..." -ForegroundColor Yellow
if (Test-Path $oldFile -and $oldFile -like "*auto.*") {
    Rename-Item -Path $oldFile -NewName "pos-cafe-prototype.test.conf" -Force
    Write-Host "  ✓ Renamed: removed 'auto.' prefix" -ForegroundColor Green
} else {
    Write-Host "  ℹ Already renamed (no 'auto.' prefix)" -ForegroundColor Cyan
}

# Step 4: Copy optimized config
Write-Host "`nStep 4: Applying optimized configuration..." -ForegroundColor Yellow
if (Test-Path $templateFile) {
    Copy-Item -Path $templateFile -Destination $newFile -Force
    Write-Host "  ✓ Configuration updated!" -ForegroundColor Green
} else {
    Write-Host "  ✗ Error: Template file not found!" -ForegroundColor Red
    Write-Host "  Looking for: $templateFile" -ForegroundColor Red
    exit 1
}

# Step 5: Instructions for Nginx restart
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  CONFIGURATION APPLIED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "  1. ⚠️  RESTART NGINX from Laragon menu" -ForegroundColor White
Write-Host "  2. 🔨 Build production assets:" -ForegroundColor White
Write-Host "     cd C:\laragon\www\pos-cafe-prototype" -ForegroundColor Gray
Write-Host "     npm run build" -ForegroundColor Gray
Write-Host "  3. 🌐 Hard refresh browser: Ctrl+Shift+R" -ForegroundColor White
Write-Host "  4. 📊 Check DevTools Network tab" -ForegroundColor White

Write-Host "`nExpected Results:" -ForegroundColor Yellow
Write-Host "  • Transfer size: 2.3 MB → 600 KB (-75%)" -ForegroundColor Cyan
Write-Host "  • Load time: 4.3s → 1.2s (-72%)" -ForegroundColor Cyan
Write-Host "  • Content-Encoding: gzip ✓" -ForegroundColor Cyan

Write-Host "`nBackup Location:" -ForegroundColor Yellow
Write-Host "  $backupFile" -ForegroundColor Gray

Write-Host "`n" -ForegroundColor White

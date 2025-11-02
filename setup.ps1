# Quick Setup Script untuk POS Cafe Inventory Prototype
# Jalankan dengan: .\setup.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "POS Cafe Inventory - Setup Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if .env exists
if (-not (Test-Path ".env")) {
    Write-Host "[*] Membuat file .env dari .env.example..." -ForegroundColor Yellow
    Copy-Item .env.example .env
    Write-Host "[OK] File .env berhasil dibuat" -ForegroundColor Green
    Write-Host "[!] PENTING: Edit file .env dan sesuaikan DB_* configuration untuk PostgreSQL!" -ForegroundColor Red
    Write-Host ""
    
    $continue = Read-Host "Apakah sudah mengatur database di .env? (y/n)"
    if ($continue -ne "y") {
        Write-Host "[X] Setup dibatalkan. Silakan edit .env terlebih dahulu." -ForegroundColor Red
        exit
    }
}

Write-Host ""
Write-Host "[*] Installing Composer dependencies..." -ForegroundColor Yellow
composer install

Write-Host ""
Write-Host "[*] Installing NPM dependencies..." -ForegroundColor Yellow
npm install

Write-Host ""
Write-Host "[*] Generating application key..." -ForegroundColor Yellow
php artisan key:generate

Write-Host ""
Write-Host "[*] Running migrations and seeders..." -ForegroundColor Yellow
php artisan migrate:fresh --seed

Write-Host ""
Write-Host "[*] Seeding demo data..." -ForegroundColor Yellow
php artisan db:seed --class=DemoDataSeeder

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "[OK] Setup selesai!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Langkah selanjutnya:" -ForegroundColor Cyan
Write-Host "1. Jalankan: php artisan serve" -ForegroundColor White
Write-Host "2. Di terminal baru, jalankan: npm run dev" -ForegroundColor White
Write-Host "3. Buka browser: http://localhost:8000/admin" -ForegroundColor White
Write-Host ""
Write-Host "Login credentials:" -ForegroundColor Cyan
Write-Host "  Email: admin@example.com" -ForegroundColor White
Write-Host "  Password: password" -ForegroundColor White
Write-Host ""
Write-Host "Happy coding!" -ForegroundColor Green

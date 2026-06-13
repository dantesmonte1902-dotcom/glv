Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "==> Local veritabani sifirlaniyor" -ForegroundColor Cyan
docker compose exec -T app php artisan migrate:fresh --seed --force

Write-Host ""
Write-Host "Sifirlama tamamlandi." -ForegroundColor Green
Write-Host "Demo sifre: password"
Write-Host "Admin:     admin@glv.local"
Write-Host "Restoran:  restaurant@glv.local"
Write-Host "Musteri:   customer@glv.local"
Write-Host "Kurye:     courier@glv.local"

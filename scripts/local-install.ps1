Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Write-Step {
    param([string] $Message)
    Write-Host ""
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Test-Command {
    param([string] $Name)
    return [bool](Get-Command $Name -ErrorAction SilentlyContinue)
}

if (-not (Test-Command "docker")) {
    throw "Docker bulunamadi. Once Docker Desktop kurup calistirin."
}

Write-Step "Docker Compose kontrol ediliyor"
docker compose version | Out-Host

if (-not (Test-Path ".env")) {
    Write-Step ".env dosyasi olusturuluyor"
    Copy-Item ".env.example" ".env"
}

Write-Step "Docker servisleri build edilip baslatiliyor"
docker compose up -d --build

Write-Step "Composer paketleri kuruluyor"
docker compose exec -T app composer install

Write-Step "Laravel APP_KEY uretiliyor"
docker compose exec -T app php artisan key:generate --force

Write-Step "Veritabani migration'lari calistiriliyor"
docker compose exec -T app php artisan migrate --force

Write-Step "Demo veriler seed ediliyor"
docker compose exec -T app php artisan db:seed --force

if (Test-Command "npm") {
    Write-Step "Frontend paketleri kuruluyor ve build aliniyor"
    npm ci
    npm run build
} else {
    Write-Host ""
    Write-Host "npm bulunamadi; frontend asset build atlandi. API yine calisir." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Kurulum tamamlandi." -ForegroundColor Green
Write-Host "Web/API:  http://localhost:8080"
Write-Host "Reverb:   http://localhost:8081"
Write-Host ""
Write-Host "Demo sifre: password"
Write-Host "Admin:     admin@glv.local"
Write-Host "Restoran:  restaurant@glv.local"
Write-Host "Musteri:   customer@glv.local"
Write-Host "Kurye:     courier@glv.local"
Write-Host ""
Write-Host "Test icin: docker compose exec app php artisan test"

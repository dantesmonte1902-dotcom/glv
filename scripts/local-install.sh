#!/usr/bin/env bash
set -euo pipefail

step() {
  printf '\n==> %s\n' "$1"
}

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker bulunamadi. Once Docker kurup calistirin." >&2
  exit 1
fi

step "Docker Compose kontrol ediliyor"
docker compose version

if [ ! -f ".env" ]; then
  step ".env dosyasi olusturuluyor"
  cp .env.example .env
fi

step "Docker servisleri build edilip baslatiliyor"
docker compose up -d --build

step "Composer paketleri kuruluyor"
docker compose exec -T app composer install

step "Laravel APP_KEY uretiliyor"
docker compose exec -T app php artisan key:generate --force

step "Veritabani migration'lari calistiriliyor"
docker compose exec -T app php artisan migrate --force

step "Demo veriler seed ediliyor"
docker compose exec -T app php artisan db:seed --force

if command -v npm >/dev/null 2>&1; then
  step "Frontend paketleri kuruluyor ve build aliniyor"
  npm ci
  npm run build
else
  printf '\nnpm bulunamadi; frontend asset build atlandi. API yine calisir.\n'
fi

printf '\nKurulum tamamlandi.\n'
printf 'Web/API:  http://localhost:8080\n'
printf 'Reverb:   http://localhost:8081\n'
printf '\nDemo sifre: password\n'
printf 'Admin:     admin@glv.local\n'
printf 'Restoran:  restaurant@glv.local\n'
printf 'Musteri:   customer@glv.local\n'
printf 'Kurye:     courier@glv.local\n'
printf '\nTest icin: docker compose exec app php artisan test\n'

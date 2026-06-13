#!/usr/bin/env bash
set -euo pipefail

printf '\n==> Local veritabani sifirlaniyor\n'
docker compose exec -T app php artisan migrate:fresh --seed --force

printf '\nSifirlama tamamlandi.\n'
printf 'Demo sifre: password\n'
printf 'Admin:     admin@glv.local\n'
printf 'Restoran:  restaurant@glv.local\n'
printf 'Musteri:   customer@glv.local\n'
printf 'Kurye:     courier@glv.local\n'

#!/usr/bin/env bash
# Atalho para rodar artisan no container do backend. Ex.: scripts/artisan.sh make:model Project
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose exec backend php artisan "$@"

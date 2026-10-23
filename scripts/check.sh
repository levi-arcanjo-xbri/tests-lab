#!/usr/bin/env bash
# Roda lint e testes dentro dos containers, igual ao CI.
# Uso: scripts/check.sh [backend|frontend|e2e|all]   (padrão: all)
set -euo pipefail

cd "$(dirname "$0")/.."

backend() {
    echo "==> Backend: Pint"
    docker compose run --rm --no-deps -e RUN_MIGRATIONS=false backend ./vendor/bin/pint --test
    echo "==> Backend: Pest"
    docker compose run --rm -e RUN_MIGRATIONS=false backend php artisan test
}

frontend() {
    echo "==> Frontend: ESLint"
    docker compose run --rm --no-deps frontend npm run lint
    echo "==> Frontend: testes unitários"
    docker compose run --rm --no-deps frontend npm run test:ci
}

e2e() {
    echo "==> E2E: Playwright"
    docker compose up -d --wait backend frontend
    docker compose --profile e2e run --rm e2e
}

case "${1:-all}" in
    backend) backend ;;
    frontend) frontend ;;
    e2e) e2e ;;
    all) backend; frontend; e2e ;;
    *) echo "Uso: $0 [backend|frontend|e2e|all]" >&2; exit 1 ;;
esac

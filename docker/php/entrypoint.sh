#!/usr/bin/env bash
# Prepara o backend na primeira subida: dependências, .env, chave, migrations e seed.
set -euo pipefail

cd /var/www/backend

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Sincroniza a senha do banco com a variável do compose, quando informada.
if [ -n "${DB_PASSWORD:-}" ]; then
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --no-interaction
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --seed
fi

exec "$@"

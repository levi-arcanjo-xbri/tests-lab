#!/usr/bin/env bash
# Instala as dependências do frontend na primeira subida.
set -euo pipefail

if [ ! -d node_modules ]; then
    npm ci
fi

exec "$@"

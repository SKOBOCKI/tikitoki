#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

# Ensure a local .env exists for builds and runtime.
if [ ! -f .env ]; then
  cp .env.example .env
fi

# If APP_KEY is supplied through the environment, preserve it to the generated .env.
if [ -n "${APP_KEY:-}" ]; then
  if grep -q '^APP_KEY=' .env; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
  else
    printf 'APP_KEY=%s\n' "$APP_KEY" >> .env
  fi
fi

# Generate an app key if no valid APP_KEY exists in .env.
if ! grep -qE '^APP_KEY=.+$' .env; then
  php artisan key:generate --force
fi

# Clear any stale cached configuration so the runtime can use the current environment.
php artisan config:clear

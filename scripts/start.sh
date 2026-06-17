#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

# Railway mounts volumes at runtime, so refresh the public storage link here too.
php artisan storage:link --force

php -S 0.0.0.0:${PORT:-8080} -t public public/index.php

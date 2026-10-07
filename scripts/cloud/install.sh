#!/usr/bin/env bash
# Idempotent repository bootstrap for the GeoS Ideal (dev.gi.by) WordPress app.
# Installs the PHP/MySQL/nginx toolchain and project dependencies. Safe to re-run.
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"

echo "[install] Installing system packages (PHP 8.3, MySQL, nginx, Composer)..."
sudo apt-get update -y
sudo apt-get install -y --no-install-recommends \
  ca-certificates curl git unzip openssl \
  php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl php8.3-sybase php8.3-sockets \
  composer mysql-server nginx

echo "[install] Installing WP-CLI..."
if ! command -v wp >/dev/null 2>&1; then
  sudo curl -fsSL -o /usr/local/bin/wp \
    https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  sudo chmod +x /usr/local/bin/wp
fi

echo "[install] Installing PHP (Composer) dependencies for the WebSocket app..."
cd "$ROOT/public"
composer install --no-interaction --no-progress

echo "[install] Installing root Node placeholder dependencies..."
cd "$ROOT"
npm install --no-audit --no-fund

echo "[install] Done."

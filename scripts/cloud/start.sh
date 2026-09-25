#!/usr/bin/env bash
# Idempotent per-boot startup for the GeoS Ideal (dev.gi.by) WordPress app.
# Brings up MySQL, PHP-FPM and nginx (TLS), provisions the local dev database
# to match public/wp-config.php, and installs WordPress core on first boot.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
DOCROOT="$ROOT/public"
SITE_URL="https://dev-local.gi.by"

echo "[start] Ensuring /etc/hosts entries..."
grep -q "mysql-8.0" /etc/hosts || echo "127.0.0.1 mysql-8.0" | sudo tee -a /etc/hosts >/dev/null
grep -q "dev-local.gi.by" /etc/hosts || echo "127.0.0.1 dev-local.gi.by" | sudo tee -a /etc/hosts >/dev/null

echo "[start] Ensuring nginx TLS certificate + vhost..."
sudo mkdir -p /etc/nginx/ssl
if [ ! -f /etc/nginx/ssl/dev-local.gi.by.crt ]; then
  sudo openssl req -x509 -nodes -newkey rsa:2048 -days 3650 \
    -keyout /etc/nginx/ssl/dev-local.gi.by.key \
    -out /etc/nginx/ssl/dev-local.gi.by.crt \
    -subj "/CN=dev-local.gi.by" -addext "subjectAltName=DNS:dev-local.gi.by"
fi
sed "s#__DOCROOT__#${DOCROOT}#g" "$ROOT/scripts/cloud/nginx-gi-by.conf" \
  | sudo tee /etc/nginx/sites-available/gi-by.conf >/dev/null
sudo ln -sf /etc/nginx/sites-available/gi-by.conf /etc/nginx/sites-enabled/gi-by.conf
sudo rm -f /etc/nginx/sites-enabled/default

echo "[start] Starting services..."
sudo service mysql start || true
sudo service php8.3-fpm restart 2>/dev/null || sudo service php8.3-fpm start || true

echo "[start] Waiting for MySQL..."
for _ in $(seq 1 30); do
  sudo mysqladmin ping >/dev/null 2>&1 && break
  sleep 1
done

echo "[start] Provisioning dev database + root user (matches wp-config.php)..."
sudo mysql <<'SQL'
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED WITH mysql_native_password BY '';
ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY '';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
CREATE DATABASE IF NOT EXISTS `dev-local.gi.by` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
FLUSH PRIVILEGES;
SQL

echo "[start] Installing WordPress core on first boot (idempotent)..."
cd "$DOCROOT"
if ! wp core is-installed --allow-root >/dev/null 2>&1; then
  wp core install --url="$SITE_URL" --title='GeoS Ideal (dev)' \
    --admin_user=admin --admin_password='Admin!2345' \
    --admin_email='admin@example.com' --skip-email --allow-root
  wp post create --post_title='Environment smoke test' \
    --post_content='Hello from the Cloud Agent environment.' \
    --post_status=publish --allow-root >/dev/null || true
fi

echo "[start] Reloading nginx..."
sudo nginx -t
sudo service nginx restart 2>/dev/null || sudo service nginx start

echo "[start] Ready: ${SITE_URL}/ (admin: ${SITE_URL}/wp-admin, user 'admin')"

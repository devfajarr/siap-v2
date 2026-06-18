#!/bin/sh
# =============================================================================
# SIAP - Sistem Informasi Akademik POLSA
# Docker Entrypoint Script
# =============================================================================
# Script ini dijalankan sebelum Supervisor start.
# Tugasnya: memastikan aplikasi Laravel siap sebelum menerima traffic.
# =============================================================================

set -e

echo "====================================================="
echo " SIAP - Sistem Informasi Akademik POLSA"
echo " Initializing container..."
echo "====================================================="

# ---------- Buat .env jika belum ada ----------
# Laravel membutuhkan file .env untuk artisan commands (key:generate, config:cache, dll).
# Nilai aktual tetap diambil dari environment variables docker-compose (lebih prioritas).
if [ ! -f /app/.env ]; then
    echo "[0/7] .env tidak ditemukan — membuat dari .env.example..."
    cp /app/.env.example /app/.env
    echo "  ✓ .env dibuat dari .env.example."
else
    echo "[0/7] ✓ .env sudah ada."
fi

# ---------- Wait for PostgreSQL ----------
echo "[1/6] Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT:-5432}..."
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-siap_user}" -d "${DB_DATABASE:-siakad}" 2>/dev/null; do
  echo "  PostgreSQL not ready yet, retrying in 3s..."
  sleep 3
done
echo "  ✓ PostgreSQL is ready."

# ---------- Laravel Key ----------
if [ -z "$APP_KEY" ]; then
  echo "[2/6] Generating application key..."
  php /app/artisan key:generate --force --no-interaction
else
  echo "[2/6] ✓ APP_KEY already set."
fi

# ---------- Laravel Storage Link ----------
echo "[3/6] Creating storage symlink..."
php /app/artisan storage:link --force --no-interaction 2>/dev/null || true
echo "  ✓ Storage link ready."

# ---------- Database Migrations ----------
echo "[4/6] Running database migrations..."
php /app/artisan migrate --force --no-interaction
echo "  ✓ Migrations complete."

# ---------- Laravel Cache Optimization ----------
echo "[5/6] Optimizing Laravel for production..."
php /app/artisan config:cache --no-interaction
php /app/artisan route:cache --no-interaction
php /app/artisan view:cache --no-interaction
php /app/artisan event:cache --no-interaction
echo "  ✓ Cache optimized."

# ---------- Fix Permissions ----------
echo "[6/6] Fixing storage permissions..."
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || true
echo "  ✓ Permissions set."

echo ""
echo "====================================================="
echo " Starting Supervisor (FrankenPHP + Queue + Scheduler)"
echo "====================================================="

# Jalankan Supervisor sebagai PID 1
exec /usr/bin/supervisord -c /etc/supervisord.conf

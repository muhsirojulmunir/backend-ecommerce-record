#!/bin/bash
# =====================================================================
# Script Deploy Otomatis - Backend Admin Seller Center
# Jalankan di Terminal cPanel: bash deploy.sh
# =====================================================================

echo "🚀 Memulai proses deploy backend..."

# --- 1. Konfigurasi Wajib ---
APP_URL="https://admin.recordshoes.com"
DB_DATABASE="recordsh_ecommerce"
DB_USERNAME="recordsh_dbuser"
DB_PASSWORD="ISI_PASSWORD_DATABASE_CPANEL_ANDA"

echo "⚙️  Menulis konfigurasi .env..."
cat > .env << EOF
APP_NAME="RECORD Seller Center"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=${APP_URL}

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=false
SESSION_COOKIE=admin_record_session

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@recordshoes.com"
MAIL_FROM_NAME="RECORD Seller Center"
EOF

echo "✅ File .env berhasil ditulis!"

# --- 2. Generate App Key ---
echo "🔑 Membuat App Key..."
php artisan key:generate --force

# --- 3. Buat Folder yang Dibutuhkan & Atur Izin ---
echo "📁 Menyiapkan folder storage..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 775 storage
chmod -R 775 bootstrap/cache

# --- 4. Hubungkan Storage (Untuk Gambar Produk) ---
echo "🔗 Menghubungkan storage..."
php artisan storage:link --force 2>/dev/null || true

# --- 5. Bersihkan Semua Cache ---
echo "🧹 Membersihkan cache..."
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear

# --- 6. Optimasi untuk Production ---
echo "⚡ Mengoptimasi untuk production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=============================================="
echo "✅ Deploy selesai!"
echo "🌐 Admin Panel: ${APP_URL}"
echo "🔐 Login: ${APP_URL}/login"
echo "=============================================="

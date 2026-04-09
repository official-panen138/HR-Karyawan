#!/bin/bash
# =============================================================================
# HR System — Server Setup Script (Ubuntu 22.04+)
# Run this ONCE on a fresh VPS to prepare the environment.
# =============================================================================

set -euo pipefail

APP_DIR="/var/www/hr-system"
DOMAIN="api.yourdomain.com"

echo "=== Installing system packages ==="
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx postgresql redis-server supervisor certbot python3-certbot-nginx \
    php8.3-fpm php8.3-pgsql php8.3-redis php8.3-zip php8.3-curl php8.3-xml php8.3-mbstring \
    php8.3-bcmath php8.3-pcntl php8.3-intl unzip git

echo "=== Installing Composer ==="
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

echo "=== Setting up PostgreSQL ==="
sudo -u postgres psql -c "CREATE USER hr_user WITH PASSWORD 'CHANGE_THIS_PASSWORD';"
sudo -u postgres psql -c "CREATE DATABASE hr_system OWNER hr_user;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE hr_system TO hr_user;"

echo "=== Cloning repository ==="
sudo mkdir -p "$APP_DIR"
sudo chown www-data:www-data "$APP_DIR"
sudo -u www-data git clone https://github.com/YOUR_ORG/hr-system.git "$APP_DIR"

echo "=== Setting up application ==="
cd "$APP_DIR"
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data cp .env.example .env
echo ">>> EDIT .env NOW with production values, then press Enter"
read -r

sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate --force --seed
sudo -u www-data php artisan storage:link

echo "=== Configuring Nginx ==="
sudo cp deployment/nginx.conf /etc/nginx/sites-available/hr-system
sudo ln -sf /etc/nginx/sites-available/hr-system /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

echo "=== Setting up SSL ==="
sudo certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m admin@yourdomain.com

echo "=== Configuring Supervisor ==="
sudo cp deployment/supervisor.conf /etc/supervisor/conf.d/hr-system.conf
sudo supervisorctl reread
sudo supervisorctl update

echo "=== Setting permissions ==="
sudo chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "=== Caching config ==="
cd "$APP_DIR"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

echo ""
echo "=== Setup complete! ==="
echo "Backend:   https://$DOMAIN"
echo "Telescope: https://$DOMAIN/telescope (local/staging only)"
echo ""
echo "Next steps:"
echo "1. Edit .env with production values (DB, R2, Telegram, Reverb, Sentry)"
echo "2. Setup GitHub Secrets for CI/CD"
echo "3. Deploy frontend to Cloudflare Pages"

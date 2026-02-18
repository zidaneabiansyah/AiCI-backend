#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
  echo "[bootstrap] vendor not found, running composer install..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

exec "$@"

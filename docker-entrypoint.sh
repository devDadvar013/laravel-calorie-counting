#!/bin/sh
set -e

# Render sets $PORT at runtime
PORT=${PORT:-80}

# Configure Apache to listen on Render's port
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Run database migrations at container start.
# Env vars (DB_URL, etc.) are available here, unlike at Docker build time.
echo "[entrypoint] running migrations…"
if php artisan migrate --force --no-interaction; then
    echo "[entrypoint] migrations OK"
else
    echo "[entrypoint] WARNING: migrations failed — see log above" >&2
fi

# Seed the demo user on first boot (DatabaseSeeder is idempotent)
if [ "${SEED_DEMO:-false}" = "true" ]; then
    echo "[entrypoint] seeding demo user…"
    php artisan db:seed --force --no-interaction \
        || echo "[entrypoint] WARNING: seed failed — see log above" >&2
fi

# Refresh config / route caches now that .env is fully resolved.
php artisan config:clear || true
php artisan route:clear  || true
php artisan view:clear   || true

exec "$@"

#!/bin/sh
set -e

cd /var/www/html

# .env de pruebas: se crea a partir de .env.example si no existe
if [ ! -f .env ]; then
    cp .env.example .env
fi

# APP_KEY y JWT_SECRET: se generan solo si no vienen por variable de entorno ni en el .env
if [ -z "$APP_KEY" ] && ! grep -qE '^APP_KEY=.+' .env; then
    php artisan key:generate --force --no-interaction
fi
if [ -z "$JWT_SECRET" ] && ! grep -qE '^JWT_SECRET=.+' .env; then
    php artisan jwt:secret --force --no-interaction
fi

chown -R www-data:www-data storage bootstrap/cache public/imports .env

# Espera a que MySQL acepte conexiones (la importación inicial del dump puede tardar)
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "Esperando a la base de datos ${DB_HOST:-db}:${DB_PORT:-3306}..."
    i=0
    until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306).";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit(0); } catch (Throwable $e) { exit(1); }'; do
        i=$((i + 1))
        if [ "$i" -ge 90 ]; then
            echo "La base de datos no respondió a tiempo" >&2
            break
        fi
        sleep 2
    done
fi

php artisan optimize:clear --no-interaction >/dev/null

exec "$@"

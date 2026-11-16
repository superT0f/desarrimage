#!/bin/sh
set -eu

export DESARRIMAGE_DB_HOST="${DESARRIMAGE_DB_HOST:-db}"
export DESARRIMAGE_DB_PORT="${DESARRIMAGE_DB_PORT:-3306}"
export DESARRIMAGE_DB_NAME="${DESARRIMAGE_DB_NAME:-desarrimage}"
export DESARRIMAGE_DB_USER="${DESARRIMAGE_DB_USER:-desarrimage}"
export DESARRIMAGE_DB_PASS="${DESARRIMAGE_DB_PASS:-desarrimage-dev}"

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

attempt=1
until php -r 'try { new PDO("mysql:host=".getenv("DESARRIMAGE_DB_HOST").";port=".getenv("DESARRIMAGE_DB_PORT").";dbname=".getenv("DESARRIMAGE_DB_NAME").";charset=utf8mb4", getenv("DESARRIMAGE_DB_USER"), getenv("DESARRIMAGE_DB_PASS")); } catch (Throwable $e) { exit(1); }'; do
    if [ "$attempt" -ge 60 ]; then
        echo 'MariaDB ne répond pas après 120 secondes.' >&2
        exit 1
    fi
    printf 'En attente de MariaDB (%s/60)...\n' "$attempt"
    attempt=$((attempt + 1))
    sleep 2
done

vendor/bin/phinx migrate --environment production
php scripts/seed.php

exec apache2-foreground

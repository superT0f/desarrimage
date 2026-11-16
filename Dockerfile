# syntax=docker/dockerfile:1

FROM node:22-alpine AS client
WORKDIR /app
COPY client/package.json client/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY client/ ./
COPY api/openapi.yaml /app/spec/openapi.yaml
COPY LICENSE ./LICENSE
EXPOSE 5173
CMD ["sh", "-c", "npm ci --no-audit --no-fund && npm run dev -- --host 0.0.0.0"]

FROM docker.io/library/composer:2 AS vendor
WORKDIR /app
COPY api/composer.json api/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader

FROM docker.io/library/php:8.4-apache AS api
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/api/public!g' \
        -e 's!AllowOverride None!AllowOverride All!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf
WORKDIR /var/www/html/api
COPY --from=docker.io/library/composer:2 /usr/bin/composer /usr/bin/composer
COPY api/ ./
COPY LICENSE /var/www/html/LICENSE
COPY --from=vendor /app/vendor ./vendor
COPY api/scripts/docker-entrypoint.sh /usr/local/bin/desarrimage-entrypoint
COPY data/ /var/www/html/data/
RUN chmod +x /usr/local/bin/desarrimage-entrypoint
ENV APP_ENV=development \
    DESARRIMAGE_DB_HOST=db \
    DESARRIMAGE_DB_PORT=3306 \
    DESARRIMAGE_DB_NAME=desarrimage \
    DESARRIMAGE_DB_USER=desarrimage \
    DESARRIMAGE_DB_PASS=desarrimage-dev \
    CORS_ORIGIN=http://localhost:5173
EXPOSE 80
ENTRYPOINT ["/usr/local/bin/desarrimage-entrypoint"]

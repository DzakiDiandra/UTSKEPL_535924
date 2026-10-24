# syntax=docker/dockerfile:1

FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev

FROM php:8.2-cli-alpine AS runtime

LABEL org.opencontainers.image.source="https://github.com/DzakiDiandra/UTSKEPL_535924"

RUN docker-php-ext-install pdo_mysql

RUN addgroup -g 1001 app \
    && adduser -D -u 1001 -G app app

WORKDIR /app

COPY --from=vendor --chown=app:app /app /app

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && touch database/database.sqlite \
    && chown -R app:app storage bootstrap/cache database

ENV APP_ENV=production \
    APP_DEBUG=false

USER app

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD wget -qO- http://127.0.0.1:8000/up || exit 1

CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000"]

# ── Stage 1: Build frontend assets ──────────────────────────────────────────
FROM node:16-alpine AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources/ ./resources/
COPY webpack.mix.js ./
COPY public/mix-manifest.json* ./public/
RUN npm run production

# ── Stage 2: PHP-FPM application ────────────────────────────────────────────
FROM php:7.4-fpm-alpine AS app

RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        bcmath \
        exif \
        gd \
        zip \
        xml \
        opcache \
        ftp

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

COPY . .

# Overlay compiled frontend assets
COPY --from=frontend /app/public/js  ./public/js
COPY --from=frontend /app/public/css ./public/css
COPY --from=frontend /app/public/mix-manifest.json ./public/mix-manifest.json

# Optimize autoloader now that all source files are present
RUN composer dump-autoload --optimize --no-dev \
    && composer run-script post-autoload-dump --no-interaction

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# ── Stage 3: Nginx (static assets only, no PHP) ──────────────────────────────
FROM nginx:alpine AS web

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Static files served directly by Nginx
COPY --from=frontend /app/public/js  /var/www/html/public/js
COPY --from=frontend /app/public/css /var/www/html/public/css
COPY --from=frontend /app/public/mix-manifest.json /var/www/html/public/mix-manifest.json
COPY public/favicon.ico \
     public/robots.txt \
     public/privacy_policy.html \
     public/slider.html \
     /var/www/html/public/
# Uploaded images are injected at runtime via a named volume

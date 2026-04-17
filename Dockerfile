# build frontend assets (vite + tailwind)
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* vite.config.js ./
COPY resources/ resources/

RUN npm ci && npm run build

# php application
FROM php:8.4-fpm-alpine

# install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libpq-dev \
    oniguruma-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev

# install php extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# configure php for production
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# custom php config
COPY docker/php/custom.ini $PHP_INI_DIR/conf.d/custom.ini

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# copy the rest of application
COPY . .

# copy built frontend assets
COPY --from=frontend /app/public/build public/build

RUN composer dump-autoload --optimize \
    && composer run-script post-autoload-dump 2>/dev/null || true

# permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Keep a copy of public/ inside the image so the entrypoint can sync it
# into the shared public-data volume on every container start
RUN mkdir -p /var/www/html-image && cp -a /var/www/html/public /var/www/html-image/public

# Entrypoint syncs public assets from image → volume on startup
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

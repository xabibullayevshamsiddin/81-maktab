FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip libpq-dev \
    libssl-dev ca-certificates \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Dependency install (dev tools yo'q)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Build vaqtida minimal .env bilan key yaratamiz
RUN echo "APP_KEY=" > .env && php artisan key:generate

EXPOSE 8080

# Ishga tushganda: storage link, migrate, keyin serve
CMD php artisan storage:link --force 2>/dev/null || true \
    && php artisan migrate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php -S 0.0.0.0:${PORT:-8080} -t public

FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip libpq-dev \
    libssl-dev ca-certificates \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# MUHIM: composer install dan oldin .env yaratish kerak, chunki
# post-autoload-dump scripti artisan package:discover ni chaqiradi
# va u .env faylisiz ishlamaydi.
# --no-scripts bilan composer install qilamiz, keyin scriptlarni o'zimiz ishga tushiramiz.
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Build vaqtida minimal .env yaratamiz va artisan scriptlarini ishga tushiramiz
RUN echo "APP_KEY=base64:dummykeyforbuildonly=" > .env \
    && php artisan package:discover --ansi \
    && php artisan vendor:publish --tag=laravel-assets --ansi --force || true

# Asl APP_KEY ni Render runtime da env orqali beradi (render.yaml dagi generateValue)
# Build env faylini o'chiramiz
RUN rm .env

EXPOSE 8080

# Ishga tushganda: storage link, migrate, keyin serve
CMD php artisan storage:link --force 2>/dev/null || true \
    && php artisan migrate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php -S 0.0.0.0:${PORT:-8080} -t public

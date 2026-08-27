FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip \
    libpq-dev libssl-dev ca-certificates nginx supervisor \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts \
    && echo "APP_KEY=base64:dummykeyforbuildonly=" > .env \
    && php artisan package:discover --ansi \
    && php artisan vendor:publish --tag=laravel-assets --ansi --force || true \
    && rm .env

# Supervisor config
RUN echo '[supervisord]\nnodaemon=true\n\n\
[program:php-fpm]\ncommand=php-fpm\nautostart=true\nautorestart=true\n\n\
[program:nginx]\ncommand=nginx -g "daemon off;"\nautostart=true\nautorestart=true\n' \
    > /etc/supervisor/conf.d/supervisord.conf

# Nginx config — write to sites-available AND create symlink to sites-enabled
RUN mkdir -p /etc/nginx/sites-enabled && \
    echo 'server { \
    listen 8080; \
    server_name _; \
    root /app/public; \
    index index.php; \
    client_max_body_size 64M; \
    \
    location /storage/ { \
        alias /app/storage/app/public/; \
        add_header Cache-Control "public, max-age=2592000"; \
        expires 30d; \
        access_log off; \
        try_files $uri $uri/ =404; \
    } \
    \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; \
        include fastcgi_params; \
        fastcgi_read_timeout 120; \
    } \
    \
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff2?)$ { \
        expires 30d; \
        add_header Cache-Control "public"; \
    } \
}' > /etc/nginx/sites-available/default && \
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default && \
    rm -f /etc/nginx/sites-enabled/default.conf 2>/dev/null || true

# Remove default nginx site config if it exists
RUN rm -f /etc/nginx/conf.d/default.conf 2>/dev/null || true

RUN mkdir -p storage/app/public/posts \
    && mkdir -p storage/app/public/books \
    && mkdir -p storage/app/public/courses \
    && mkdir -p storage/app/public/avatars \
    && mkdir -p storage/app/public/temp \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD ["/bin/sh", "-c", "\
    mkdir -p storage/app/public/posts && \
    mkdir -p storage/app/public/books && \
    mkdir -p storage/app/public/courses && \
    mkdir -p storage/app/public/avatars && \
    mkdir -p storage/app/public/temp && \
    mkdir -p storage/framework/cache/data && \
    mkdir -p storage/framework/sessions && \
    mkdir -p storage/framework/views && \
    mkdir -p storage/logs && \
    mkdir -p bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    php artisan storage:link --force 2>/dev/null || true && \
    php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan telegram:set-webhook 2>/dev/null || true && \
    php artisan telegram:set-commands && \
    /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]

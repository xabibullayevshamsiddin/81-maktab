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

# Nginx config
RUN echo 'server { \n\
    listen ${PORT:-8080}; \n\
    root /app/public; \n\
    index index.php; \n\
    client_max_body_size 64M; \n\
    location / { try_files $uri $uri/ /index.php?$query_string; } \n\
    location ~ \.php$ { \n\
        fastcgi_pass 127.0.0.1:9000; \n\
        fastcgi_index index.php; \n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; \n\
        include fastcgi_params; \n\
        fastcgi_read_timeout 120; \n\
    } \n\
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff2?)$ { expires 30d; add_header Cache-Control "public"; } \n\
}' > /etc/nginx/sites-available/default

# Supervisor config
RUN echo '[supervisord]\nnodaemon=true\n\n\
[program:php-fpm]\ncommand=php-fpm\nautostart=true\nautorestart=true\n\n\
[program:nginx]\ncommand=nginx -g "daemon off;"\nautostart=true\nautorestart=true\n' \
    > /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

CMD ["/bin/sh", "-c", "\
    php artisan storage:link --force 2>/dev/null || true && \
    php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    sed -i \"s/\\${PORT:-8080}/${PORT:-8080}/g\" /etc/nginx/sites-available/default && \
    /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]

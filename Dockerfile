# Admin ISP - Laravel en PHP 8.2 FPM
FROM php:8.2-fpm

# Dependencias del sistema y extensiones PHP para Laravel + DomPDF + RouterOS
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip \
    libzip-dev libpng-dev libonig-dev libxml2-dev libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip exif pcntl bcmath gd opcache sockets \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar solo dependencias primero (mejor caché de capas)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copiar el resto de la aplicación
COPY . .

# Completar instalación de Composer (autoload y scripts si los hay)
RUN composer dump-autoload --optimize

# Permisos para Laravel (PHP-FPM corre como root y workers como www-data)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]

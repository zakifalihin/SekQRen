FROM php:8.3-cli

# 1. Install dependencies sistem yang dibutuhkan library Excel/PDF
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libmagickwand-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 2. Install extension PHP (Lengkap untuk manipulasi file & data)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    gd \
    zip \
    pdo_mysql \
    bcmath \
    intl \
    mbstring \
    xml \
    pcntl \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# 3. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# 4. SET PERMISSION (Ini kunci agar tidak "Failed to Fetch")
# Railway butuh folder storage bisa ditulis oleh user server
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 5. Fix Symlink & Folder Khusus
RUN rm -rf public/storage \
    && php artisan storage:link || echo "Storage link exists"

# Buat folder khusus Export/Import agar terstruktur
RUN mkdir -p storage/app/Exports storage/app/Imports \
    && chown -R www-data:www-data storage/app/Exports storage/app/Imports \
    && chmod -R 775 storage/app/Exports storage/app/Imports

EXPOSE 8080

# 6. Optimasi Cache Laravel sebelum running
CMD php artisan config:cache && php artisan route:cache && php -S 0.0.0.0:8080 -t public
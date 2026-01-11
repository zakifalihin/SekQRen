FROM php:8.3-cli

# 1. Install dependencies sistem
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

# 2. Install extension PHP
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

# 4. SET PERMISSION (Optimalkan untuk Railway)
# Pastikan seluruh folder /app dimiliki oleh www-data
RUN chown -R www-data:www-data /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 5. Fix Symlink & Folder Khusus
RUN rm -rf public/storage \
    && php artisan storage:link || echo "Storage link exists"

# Pastikan folder Exports/Imports siap
RUN mkdir -p storage/app/Exports storage/app/Imports \
    && chown -R www-data:www-data storage/app/Exports storage/app/Imports

EXPOSE 8080

# 6. Jalankan server (Gunakan Clear daripada Cache untuk kestabilan Railway)
CMD php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php -S 0.0.0.0:8080 -t public
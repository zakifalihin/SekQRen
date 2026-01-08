FROM php:8.3-cli

# Install system dependencies
# Menambahkan libxml2, libicu, dan libonig untuk mendukung fitur Export/Import
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

# Install PHP extensions
# Menambahkan bcmath, intl, mbstring, dan xml yang wajib untuk pengolahan file Excel/PDF
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    gd \
    zip \
    pdo_mysql \
    bcmath \
    intl \
    mbstring \
    xml \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Set Permissions (Sangat Penting untuk Railway agar server bisa menulis file hasil Export)
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 🔹 Hapus public/storage lama & buat symlink baru
RUN rm -rf public/storage \
    && php artisan storage:link || echo "Storage link already exists"

# 🔹 Buat folder Exports & Imports di tempat yang benar
# Catatan: Folder ini sebaiknya ada di dalam storage agar bisa ditulis oleh server
RUN mkdir -p storage/app/Exports storage/app/Imports \
    && chown -R www-data:www-data storage/app/Exports storage/app/Imports \
    && chmod -R 775 storage/app/Exports storage/app/Imports

# Expose port
EXPOSE 8080

# Run Laravel
# Ditambahkan pembersihan cache agar konfigurasi Railway terbaca sempurna
CMD php artisan config:cache && php artisan route:cache && php -S 0.0.0.0:8080 -t public
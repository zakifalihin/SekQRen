FROM php:8.3-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libmagickwand-dev --no-install-recommends \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working dir
WORKDIR /var/www

# Copy composer files dulu (biar caching lebih efisien)
COPY composer.json composer.lock ./

# Install dependencies tanpa post-script artisan
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy semua file project
COPY . .

# Jalankan ulang script artisan setelah semua file ada
RUN composer run-script post-autoload-dump || true

# Permission untuk Laravel
RUN mkdir -p storage bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

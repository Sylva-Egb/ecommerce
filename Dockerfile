FROM php:8.2-apache

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installation des extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copie du projet
WORKDIR /var/www/html
COPY . .

# Installation des dépendances
RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run build

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD php artisan serve --host=0.0.0.0 --port=80
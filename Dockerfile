FROM php:8.2-cli

# 1. Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Installation de Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# 3. Configuration du projet
WORKDIR /var/www/html
COPY . .

# 4. Installation des dépendances
RUN composer install --optimize-autoloader --no-dev --no-interaction \
    && npm install && npm run build

# 5. Configuration de l'environnement
RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && php artisan key:generate --no-interaction || true

# 6. Créer la base de données SQLite
RUN mkdir -p database && touch database/database.sqlite

# 7. Gestion des permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# 8. Exposition du port
EXPOSE 80

# 9. Script de démarrage optimisé
RUN echo '#!/bin/sh\n\
php artisan migrate --force\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan route:clear\n\
php artisan config:cache\n\
php artisan serve --host=0.0.0.0 --port=80\n\
' > /start.sh && chmod +x /start.sh

CMD ["/bin/sh", "/start.sh"]
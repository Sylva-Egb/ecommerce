FROM php:8.2-cli

# 1. Installation des dépendances système + Node.js 20 via nodesource
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Installation de Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# 3. Configuration du projet
WORKDIR /var/www/html
COPY . .

# 4. Installation des dépendances PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction

# 5. Installation des dépendances Node et build
RUN npm ci --omit=dev && \
    npm install vite && \
    npm run build

# 6. Configuration de l'environnement
RUN if [ ! -f .env ]; then cp .env.example .env; fi && \
    php artisan key:generate --no-interaction || true

RUN php artisan storage:link

# 7. Gestion des permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache

# 8. Exposition du port
EXPOSE 80

# 9. Création du script de démarrage (version simplifiée)
RUN echo '#!/bin/sh\n\
php artisan migrate --force\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan serve --host=0.0.0.0 --port=80\n\
' > /start.sh && chmod +x /start.sh

CMD ["/bin/sh", "/start.sh"]
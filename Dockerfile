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
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Installation de Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# 3. Configuration du projet
WORKDIR /var/www/html
COPY . .

# 4. Installation des dépendances et build des assets
RUN composer install --optimize-autoloader --no-dev --no-interaction
RUN npm ci --only=production && npm run build

# 4.1. Vérification que les assets sont bien créés
RUN echo "=== Vérification des assets buildés ===" \
    && ls -la public/ \
    && ls -la public/build/ || echo "Dossier build non trouvé" \
    && ls -la public/build/assets/ || echo "Dossier assets non trouvé"

# 5. Configuration de l'environnement
RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && php artisan key:generate --no-interaction || true

# 6. Créer la base de données SQLite
RUN mkdir -p database && touch database/database.sqlite

# 7. Gestion des permissions (AJOUT des permissions pour public/build)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod -R 755 public \
    && chmod -R 755 public/build || echo "Dossier build inexistant"

# 8. Nettoyage du cache npm
RUN npm cache clean --force

# 9. Exposition du port
EXPOSE 80

# 10. Script de démarrage optimisé
RUN echo '#!/bin/sh\n\
echo "=== Démarrage de l'\''application ==="\n\
echo "Vérification des assets:"\n\
ls -la public/build/assets/ || echo "Assets non trouvés !"\n\
echo "Migration de la base de données..."\n\
php artisan migrate --force\n\
echo "Nettoyage des caches..."\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan route:clear\n\
echo "Création des caches optimisés..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
echo "Démarrage du serveur sur le port 80..."\n\
php artisan serve --host=0.0.0.0 --port=80\n\
' > /start.sh && chmod +x /start.sh

CMD ["/bin/sh", "/start.sh"]
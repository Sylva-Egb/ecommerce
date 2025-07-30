FROM php:8.2-cli

# 1. Installation des dépendances système (SANS SQLite)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
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

# 6. Gestion des permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod -R 755 public \
    && chmod -R 755 public/build || echo "Dossier build inexistant"

# 7. Nettoyage du cache npm
RUN npm cache clean --force

# 8. Exposition du port
EXPOSE 80

# 9. Script de démarrage sans migration automatique
RUN echo '#!/bin/sh\n\
echo "=== Démarrage de l'\''application ==="\n\
echo "Vérification des assets:"\n\
ls -la public/build/assets/ || echo "Assets non trouvés !"\n\
echo "Attente de la base de données..."\n\
until php artisan migrate:status 2>/dev/null; do\n\
  echo "Base de données non accessible, attente..."\n\
  sleep 2\n\
done\n\
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
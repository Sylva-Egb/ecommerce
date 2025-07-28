FROM php:8.2-cli

# Installation des dépendances système
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
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql pgsql pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie du projet
WORKDIR /var/www/html
COPY . .

# Installation des dépendances PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Installation des dépendances Node.js et build des assets
RUN npm install && npm run build

# Création du fichier .env si pas présent
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Génération de la clé APP_KEY si pas définie
RUN php artisan key:generate --no-interaction || true

# Permissions Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Configuration pour le port dynamique de Render
EXPOSE $PORT

# Script de démarrage
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Starting Laravel application..."\n\
\n\
# Vérification de la connexion DB\n\
echo "Testing database connection..."\n\
php artisan migrate:status || echo "Cannot check migration status"\n\
\n\
# Cache des configurations\n\
echo "Caching configurations..."\n\
php artisan config:clear\n\
php artisan config:cache\n\
\n\
# Migration de la base de données avec plus de détails\n\
echo "Running migrations..."\n\
php artisan migrate --force -v || {\n\
    echo "Migration failed, checking what went wrong:"\n\
    php artisan migrate:status\n\
    echo "Continuing without migrations..."\n\
}\n\
\n\
# Cache des routes et vues\n\
echo "Caching routes and views..."\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "Starting server on port $PORT"\n\
# Démarrage du serveur sur le port de Render\n\
php artisan serve --host=0.0.0.0 --port=$PORT' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]
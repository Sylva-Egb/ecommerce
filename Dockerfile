FROM php:8.2-cli

# 1. Installation des dépendances système (optimisée)
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
    # Installation des extensions PHP nécessaires
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Configuration du projet
WORKDIR /var/www/html
COPY . .

# 4. Installation des dépendances
RUN composer install --optimize-autoloader --no-dev --no-interaction \
    && npm install && npm run build

# 5. Configuration de l'environnement
RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && php artisan key:generate --no-interaction || true

# 6. Gestion des permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# 7. Exposition du port
EXPOSE $PORT

# 8. Script de démarrage amélioré
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "=== Démarrage de l\'application ==="\n\
\n\
# Attente connexion DB (10 essais max)\n\
echo "Vérification connexion MySQL..."\n\
for i in {1..10}; do\n\
    php artisan tinker --execute="\n\
        try {\n\
            DB::connection()->getPdo();\n\
            echo \"✓ Connexion MySQL établie\";\n\
            exit(0);\n\
        } catch (\\Exception \$e) {\n\
            echo \"✗ Tentative \$i/10: \" . \$e->getMessage();\n\
            if [ \$i -eq 10 ]; then exit(1); fi;\n\
            sleep 5;\n\
        }"\n\
    [ \$? -eq 0 ] && break\n\
done\n\
\n\
# Migrations\n\
echo "Exécution des migrations..."\n\
php artisan migrate --force\n\
\n\
# Optimisation\n\
echo "Optimisation des caches..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Démarrage\n\
echo "Lancement sur le port \$PORT"\n\
php artisan serve --host=0.0.0.0 --port=\$PORT\n\
' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]
#!/bin/bash

echo "🚀 Optimisation de l'application Laravel pour la production"

echo "1. Nettoyage des caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "2. Mise en cache des configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "3. Optimisation de Composer..."
composer install --optimize-autoloader --no-dev

echo "4. Compilation des assets..."
npm run build

echo "5. Optimisation de la base de données..."
php artisan migrate --force

echo "✅ Optimisation terminée!"
echo "💡 N'oubliez pas de :"
echo "   - Configurer Redis pour le cache"
echo "   - Activer OPcache sur votre serveur"
echo "   - Configurer un CDN pour les assets statiques"
echo "   - Activer la compression Gzip sur votre serveur web"

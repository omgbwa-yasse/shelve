# Shelve v2.2 - Notes de Release

## 🎯 Points clés

- Nouveau flux d'import par Glisser-Déposer (Drag & Drop) pour créer et déposer des archives
- Traitement IA plus robuste avec exceptions dédiées et meilleure extraction des pièces jointes
- Limitation de taille d'upload côté app + affichage des limites serveur (utile pour php.ini)
- Comptage des pages PDF optionnel et retour d'information à l'utilisateur
- Nouveau module Contacts pour les Organisations (modèle, migration, contrôleur, vues)
- Migration vers Vite pour les assets (première étape) et amélioration du layout

## 🆕 Nouvelles fonctionnalités

- Drag & Drop pour la création/dépôt d'archives avec traitement IA
- Limite d'upload dans l'UI + messages explicites
- Option de comptage des pages PDF et affichage des contraintes serveur
- Module Contacts Organisations (CRUD complet)

## 🛠️ Améliorations

- Extraction de pièces jointes plus fiable et logique IA durcie
- Gestion des erreurs mieux structurée via exceptions personnalisées
- Améliorations de l'UX (libellés, extensions supportées) en glisser-déposer
- Optimisations et correctifs sur les dépôts (compteurs, cohérence)
- Vite configuré pour les assets; stacking de styles dans le layout

## 🐞 Corrections

- Corrections sur le module de dépôts (affichages et comptages)
- Nettoyage de fichiers de test dépréciés

## ⚙️ Technique

- Exceptions personnalisées pour traitements IA
- Configuration initiale Vite

## 📦 Mise à jour

1. Mettre à jour les dépendances PHP et JS
2. Recompiler les assets (Vite) si utilisé
3. Vider puis reconstruire les caches

Étapes recommandées:

```powershell
composer install
npm install ; npm run build
php artisan config:clear ; php artisan route:clear ; php artisan view:clear
php artisan config:cache ; php artisan route:cache ; php artisan view:cache
```

## ❗ Points d'attention

- Si vous utilisez des limites d'upload personnalisées, synchronisez php.ini (post_max_size, upload_max_filesize) avec l'UI
- La migration vers Vite est amorcée, vérifiez votre pipeline d'assets si vous utilisiez Mix uniquement

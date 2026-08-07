# Shelve — Système de Gestion d'Archives Intelligentes

Application de gestion d'archives (SAE) : notices, courrier, thésaurus, OPAC, IA.

## Structure

| Dossier | Projet | Techno | Doc |
|---|---|---|---|
| **`laravel/`** | Backend historique (oracle) | PHP 8.2 · Laravel 12 · MySQL | [README Laravel](laravel/README.md) |
| **`backend/`** | Backend cible (phase 3) | Java 17 · Spring Boot 3.5 | [README backend](backend/README.md) |
| **`web/`** | Frontend cible | Next.js · TypeScript · Tailwind | [README web](web/README.md) |

```
shelve/
├── laravel/      # ancienne architecture (Blade + API v1) — oracle pendant la migration
├── backend/      # Spring Boot (phase 3) — réimplémentation équivalente de l'API
└── web/          # Next.js (phase 2) — nouveau frontend branché sur l'API
```

## Démarrage rapide

```bash
# Laravel (oracle) — port 8000
cd laravel && php artisan serve

# Backend Spring Boot — port 8080 (profil test : base shelve_test)
cd backend && mvn spring-boot:run -Dspring-boot.run.profiles=test

# Frontend Next.js
cd web && npm run dev
```

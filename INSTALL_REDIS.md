# 🔧 Guide d'installation Redis pour Windows

## Option 1: Installation via MSI (Recommandé)

1. **Télécharger Redis pour Windows**:
   - Aller sur: https://github.com/tporadowski/redis/releases
   - Télécharger le fichier `Redis-x64-5.0.14.1.msi`

2. **Installer Redis**:
   - Exécuter le fichier MSI en tant qu'administrateur
   - Suivre l'assistant d'installation
   - Cocher "Add Redis to the PATH environment variable"
   - Cocher "Run Redis as Service"

3. **Vérifier l'installation**:
   ```cmd
   redis-cli --version
   redis-server --version
   ```

## Option 2: Installation via Chocolatey (Mode Administrateur)

1. **Ouvrir PowerShell en tant qu'administrateur**
2. **Installer Redis**:
   ```powershell
   choco install redis-64 -y
   ```

## Option 3: Docker (Si Docker est installé)

```bash
docker run --name redis-server -p 6379:6379 -d redis:latest
```

## Configuration après installation

1. **Démarrer le service Redis**:
   ```cmd
   net start Redis
   ```

2. **Tester la connexion**:
   ```cmd
   redis-cli ping
   ```
   (Doit retourner "PONG")

3. **Mettre à jour votre .env**:
   ```env
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   REDIS_PASSWORD=null
   ```

## Après installation de Redis

Exécuter ces commandes dans votre projet:
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

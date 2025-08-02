# 🔧 Installation de l'extension PHP Redis pour WAMP

## Étapes d'installation

### 1. Identifier votre version PHP
```cmd
php -v
```

### 2. Télécharger l'extension Redis
- Aller sur: https://pecl.php.net/package/redis
- Ou utiliser: https://windows.php.net/downloads/pecl/releases/redis/
- Télécharger la version correspondant à votre PHP (ex: php_redis-5.3.7-8.2-ts-vs16-x64.zip)

### 3. Installation dans WAMP
1. **Extraire le fichier ZIP téléchargé**
2. **Copier `php_redis.dll`** dans le dossier des extensions PHP:
   ```
   C:\wamp64\bin\php\php8.x.x\ext\
   ```
3. **Éditer `php.ini`**:
   - Ouvrir: `C:\wamp64\bin\php\php8.x.x\php.ini`
   - Ajouter cette ligne:
   ```ini
   extension=redis
   ```

### 4. Redémarrer WAMP
- Redémarrer tous les services WAMP
- Vérifier avec: `php -m | findstr redis`

## Configuration alternative: Predis (Sans extension)

Si l'extension Redis pose problème, utilisez Predis:

### Installation via Composer:
```bash
composer require predis/predis
```

### Configuration dans .env:
```env
REDIS_CLIENT=predis
```

Cette solution fonctionne sans extension PHP Redis.

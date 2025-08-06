# Guide de Déploiement - MCP Shelve

## Validation pré-déploiement

Avant de déployer, validez que tout est en ordre :

```bash
# Validation automatique de la structure
npm run validate

# Installation des dépendances
npm install

# Vérification de la configuration
cp .env.example .env
# Éditer .env selon votre environnement
```

## Configuration d'environnement

### Variables essentielles (.env)

```env
# Serveur
PORT=3001
NODE_ENV=production

# Base de données
DB_HOST=localhost
DB_PORT=3306
DB_NAME=shelve_mcp
DB_USER=mcp_user
DB_PASSWORD=votre_mot_de_passe_securise

# Ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_DEFAULT_MODEL=llama3.2

# Logging
LOG_LEVEL=info
LOG_MAX_FILES=10
LOG_MAX_SIZE=10m
```

### Sécurité en production

1. **Base de données**
   - Créer un utilisateur dédié avec permissions limitées
   - Utiliser un mot de passe fort
   - Configurer les sauvegardes automatiques

2. **Serveur**
   - Utiliser HTTPS en production
   - Configurer un reverse proxy (nginx/Apache)
   - Limiter les requêtes (rate limiting)

3. **Ollama**
   - Sécuriser l'accès à l'API Ollama
   - Configurer les quotas de ressources
   - Monitoring des performances

## Déploiement sur serveur Linux

### 1. Installation des prérequis

```bash
# Node.js 16+
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt-get install -y nodejs

# MySQL/MariaDB
sudo apt-get install -y mysql-server
sudo mysql_secure_installation

# Ollama
curl https://ollama.ai/install.sh | sh
```

### 2. Configuration utilisateur système

```bash
# Créer utilisateur dédié
sudo useradd -m -s /bin/bash mcp-shelve
sudo su - mcp-shelve

# Cloner le projet
git clone [votre-repo] shelve-mcp
cd shelve-mcp/mcp
```

### 3. Installation et configuration

```bash
# Dépendances
npm install --production

# Configuration
cp .env.example .env
# Éditer .env

# Validation
npm run validate
```

### 4. Service systemd

Créer `/etc/systemd/system/mcp-shelve.service` :

```ini
[Unit]
Description=MCP Shelve Server
After=network.target mysql.service

[Service]
Type=simple
User=mcp-shelve
WorkingDirectory=/home/mcp-shelve/shelve-mcp/mcp
ExecStart=/usr/bin/node src/index.js
Restart=always
RestartSec=10
Environment=NODE_ENV=production

# Logs
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=mcp-shelve

[Install]
WantedBy=multi-user.target
```

### 5. Démarrage du service

```bash
# Activer et démarrer
sudo systemctl enable mcp-shelve
sudo systemctl start mcp-shelve

# Vérifier l'état
sudo systemctl status mcp-shelve

# Logs
sudo journalctl -u mcp-shelve -f
```

## Configuration Nginx (reverse proxy)

```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    
    location /api/ {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        
        # Timeouts pour IA
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 120s;
    }
}
```

## Monitoring et maintenance

### 1. Surveillance système

```bash
# Vérification automatique de l'API
npm run test:api

# Logs applicatifs
tail -f logs/app.log
tail -f logs/error.log

# Ressources système
htop
df -h
free -h
```

### 2. Métriques importantes

- **CPU** : < 70% en usage normal
- **RAM** : Surveiller les fuites mémoire Node.js
- **Disque** : Logs et base de données
- **Réseau** : Latence vers Ollama

### 3. Maintenance préventive

```bash
# Rotation des logs
logrotate -d /etc/logrotate.d/mcp-shelve

# Sauvegarde base de données
mysqldump shelve_mcp > backup_$(date +%Y%m%d).sql

# Mise à jour des modèles Ollama
ollama pull llama3.2
```

## Résolution de problèmes

### Service ne démarre pas

```bash
# Vérifier la configuration
npm run validate

# Tester manuellement
sudo su - mcp-shelve
cd shelve-mcp/mcp
npm start
```

### Problèmes de performance

1. **Ollama lent**
   - Vérifier les ressources GPU/CPU
   - Optimiser la configuration du modèle
   - Considérer un modèle plus petit

2. **Base de données lente**
   - Analyser les requêtes
   - Optimiser les index
   - Surveiller les connexions

### Logs d'erreur fréquents

```bash
# Analyser les patterns d'erreur
grep "ERROR" logs/error.log | tail -20

# Surveiller en temps réel
tail -f logs/error.log | grep -E "(ERROR|FATAL)"
```

## Sauvegarde et restauration

### Sauvegarde complète

```bash
#!/bin/bash
# Script de sauvegarde
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/mcp-shelve"

# Base de données
mysqldump shelve_mcp > "$BACKUP_DIR/db_$DATE.sql"

# Configuration
cp .env "$BACKUP_DIR/env_$DATE"

# Logs importants
tar -czf "$BACKUP_DIR/logs_$DATE.tar.gz" logs/

echo "Sauvegarde terminée : $DATE"
```

### Restauration

```bash
# Restaurer la base de données
mysql shelve_mcp < backup_db_YYYYMMDD.sql

# Restaurer la configuration
cp backup_env_YYYYMMDD .env

# Redémarrer le service
sudo systemctl restart mcp-shelve
```

## Mise à jour en production

1. **Arrêter le service**
   ```bash
   sudo systemctl stop mcp-shelve
   ```

2. **Sauvegarder l'état actuel**
   ```bash
   ./scripts/backup.sh
   ```

3. **Déployer la nouvelle version**
   ```bash
   git pull origin main
   npm install --production
   npm run validate
   ```

4. **Tests de validation**
   ```bash
   npm run test:api
   ```

5. **Redémarrer**
   ```bash
   sudo systemctl start mcp-shelve
   sudo systemctl status mcp-shelve
   ```

## Support et dépannage

En cas de problème :

1. Vérifier les logs système et applicatifs
2. Tester les composants individuellement (DB, Ollama, API)
3. Valider la configuration avec `npm run validate`
4. Consulter la documentation dans `/docs/`

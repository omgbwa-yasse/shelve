# 🚀 Guide de Démarrage Rapide - Module MCP

## Installation en 5 Minutes

### 1️⃣ Installer Ollama
```bash
# Windows (PowerShell Admin)
winget install ollama

# Ou télécharger depuis https://ollama.ai
# Démarrer Ollama
ollama serve
```

### 2️⃣ Télécharger les Modèles
```bash
ollama pull llama3.1:8b
ollama pull mistral:7b
```

### 3️⃣ Configuration Laravel
Ajoutez à votre `.env` :
```env
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MCP_TITLE_MODEL=llama3.1:8b
OLLAMA_MCP_THESAURUS_MODEL=mistral:7b
OLLAMA_MCP_SUMMARY_MODEL=llama3.1:8b
OLLAMA_MCP_TEMPERATURE=0.2
MCP_AUTO_PROCESS_CREATE=true
```

### 4️⃣ Test de l'Installation
```bash
php artisan mcp:test --skip-ollama  # Test sans Ollama
php artisan mcp:test                # Test complet avec Ollama
```

### 5️⃣ Premier Essai
```bash
# Trouver un ID de record
php artisan tinker
>>> App\Models\Record::first()->id

# Tester la reformulation de titre
php artisan mcp:process-record 123 --features=title --preview
```

## ⚡ Utilisation Immédiate

### Traitement d'un Record
```bash
# Reformulation de titre seulement
php artisan mcp:process-record 123 --features=title

# Toutes les fonctionnalités
php artisan mcp:process-record 123 --features=title,thesaurus,summary

# Mode prévisualisation (sans sauvegarde)
php artisan mcp:process-record 123 --preview
```

### Traitement par Lots
```bash
# 10 records avec indexation thésaurus
php artisan mcp:batch-process --limit=10 --features=thesaurus

# Traitement asynchrone (recommandé pour gros volumes)
php artisan mcp:batch-process --limit=50 --async --features=title
```

### API REST
```bash
# Test santé du système
curl http://your-domain/api/mcp/health

# Reformulation d'un titre
curl -X POST http://your-domain/api/mcp/records/123/title/reformulate

# Traitement complet
curl -X POST http://your-domain/api/mcp/records/123/process \
  -H "Content-Type: application/json" \
  -d '{"features": ["title", "thesaurus", "summary"]}'
```

## 🔧 Configuration Recommandée

### Pour la Performance
```env
MCP_CACHE_RESPONSES=true
MCP_BATCH_SIZE=5
OLLAMA_MCP_TEMPERATURE=0.1  # Plus déterministe
```

### Pour la Sécurité
```env
MCP_RATE_LIMIT_ENABLED=true
MCP_RATE_LIMIT_REQUESTS=30
```

## 📊 Monitoring

### Vérification du Statut
```bash
php artisan mcp:test
curl http://your-domain/api/mcp/health
```

### Surveillance des Jobs
```bash
php artisan queue:work --queue=mcp-light,mcp-medium,mcp-heavy
php artisan queue:monitor
```

## 🆘 Dépannage Express

### Ollama ne fonctionne pas
```bash
# Vérifier qu'Ollama est démarré
curl http://127.0.0.1:11434/api/tags

# Redémarrer si nécessaire
ollama serve

# Vérifier les modèles
ollama list
```

### Erreurs de Performance
```env
# Augmenter les timeouts
OLLAMA_CONNECTION_TIMEOUT=600

# Activer le cache
MCP_CACHE_RESPONSES=true
```

### Logs de Debug
```bash
tail -f storage/logs/laravel.log | grep MCP
```

## 🎯 Cas d'Usage Typiques

### 1. Améliorer les Titres Existants
```bash
php artisan mcp:batch-process --features=title --limit=20
```

### 2. Indexer le Thésaurus
```bash
php artisan mcp:batch-process --features=thesaurus --limit=50 --async
```

### 3. Générer les Résumés ISAD(G)
```bash
php artisan mcp:batch-process --features=summary --limit=10
```

### 4. Traitement par Organisation
```bash
php artisan mcp:batch-process --organisation_id=1 --features=title,summary
```

### 5. Test sur Records Spécifiques
```bash
php artisan mcp:process-record 123 --preview  # Voir avant/après
php artisan mcp:process-record 123            # Appliquer
```

## 📝 Notes Importantes

- ✅ **Toujours tester en prévisualisation d'abord**
- ✅ **Utiliser le mode asynchrone pour > 10 records**
- ✅ **Surveiller les logs pour les erreurs**
- ✅ **Faire des sauvegardes avant traitement en masse**

## 🔗 Liens Utiles

- **Documentation complète**: `README_MCP.md`
- **Configuration Ollama**: `config/ollama-mcp.php`
- **Routes API**: `routes/mcp.php`
- **Tests**: `php artisan mcp:test`

---

Votre module MCP est maintenant prêt ! 🎉

Pour des questions ou problèmes, consultez les logs Laravel et la documentation complète.
# 🤖 Guide Configuration API Anthropic (Claude)

## 📋 Étapes de configuration

### 1. **Obtenir une clé API Anthropic**
- Allez sur [console.anthropic.com](https://console.anthropic.com)
- Créez un compte ou connectez-vous
- Générez une clé API (format: `sk-ant-...`)

### 2. **Configuration via script (Recommandé)**

Éditez le fichier `set_anthropic_key.php` :
```php
// 🔑 METTEZ VOTRE CLÉ API ICI
$votreCleAPI = 'sk-ant-VOTRE_VRAIE_CLE_ICI';
```

Puis exécutez :
```bash
cd /c/wamp64/www/ICA/shelves
php set_anthropic_key.php
php artisan cache:clear
```

### 3. **Configuration manuelle en base**

Vous pouvez aussi configurer directement :

```sql
INSERT INTO ai_global_settings (setting_key, setting_value, setting_type) VALUES
('anthropic_enabled', 'true', 'string'),
('anthropic_api_key', 'sk-ant-VOTRE_CLE', 'string'),
('claude_api_key', 'sk-ant-VOTRE_CLE', 'string'),
('anthropic_model', 'claude-3-5-sonnet-20241022', 'string')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
```

### 4. **Changer le provider par défaut**

Pour utiliser Claude au lieu d'Ollama :
```sql
UPDATE ai_global_settings SET setting_value = 'claude' WHERE setting_key = 'default_provider';
UPDATE ai_global_settings SET setting_value = 'claude-3-5-sonnet-20241022' WHERE setting_key = 'default_model';
```

### 5. **Modèles Anthropic disponibles**

- `claude-3-5-sonnet-20241022` (Recommandé - plus récent)
- `claude-3-sonnet-20240229`
- `claude-3-haiku-20240307` (Plus rapide, moins cher)

### 6. **Vérification**

Pour vérifier la configuration :
```bash
php check_anthropic.php
```

### 7. **Test**

Une fois configuré :
1. Allez sur `/ai-search`
2. Testez une requête comme "Combien de documents ?"
3. Vérifiez que Claude répond au lieu d'Ollama

## 🔧 Paramètres disponibles

| Paramètre | Description | Valeur par défaut |
|-----------|-------------|-------------------|
| `anthropic_enabled` | Active/désactive Anthropic | `false` |
| `anthropic_api_key` | Votre clé API | - |
| `claude_api_key` | Alias pour compatibilité | - |
| `anthropic_model` | Modèle à utiliser | `claude-3-5-sonnet-20241022` |
| `anthropic_base_url` | URL API | `https://api.anthropic.com` |

## ⚠️ Sécurité

- Ne commitez jamais vos clés API
- Utilisez des variables d'environnement en production
- Surveillez votre usage sur console.anthropic.com

## 🆚 Claude vs Ollama

**Claude (Anthropic)** :
- ✅ Plus puissant et précis
- ✅ Meilleur pour le français
- ✅ Plus rapide pour des tâches complexes
- ❌ Coût par requête
- ❌ Nécessite connexion internet

**Ollama** :
- ✅ Gratuit et local
- ✅ Pas de dépendance internet
- ✅ Contrôle total
- ❌ Moins puissant
- ❌ Plus lent
# MCP Shelve - Reformulation de Noms d'Archives

## Description

Service de reformulation automatique de noms d'enregistrements d'archives selon les normes françaises. Le service reçoit un enregistrement avec son contexte et retourne le nom reformulé.

## 🚀 Démarrage rapide

1. **Démarrer Ollama**
   ```bash
   ollama serve
   ollama pull llama3.2
   ```

2. **Démarrer le serveur MCP**
   ```bash
   cd mcp
   npm install
   npm run dev
   ```

3. **Tester l'endpoint**
   ```bash
   npm run test:reformulation
   ```

## 📡 Endpoint principal

```
POST /api/records/reformulate
```

### Entrée (JSON)
```json
{
  "id": "identifiant_unique",
  "name": "nom_actuel_du_document", 
  "date": "période_optionnelle",
  "content": "description_contenu_optionnel",
  "author": {
    "name": "nom_auteur_optionnel"
  },
  "children": [
    {
      "name": "nom_sous_document",
      "date": "date_sous_document", 
      "content": "contenu_sous_document"
    }
  ]
}
```

### Sortie (JSON)
```json
{
  "id": "identifiant_unique",
  "new_name": "nom_reformulé_selon_normes_archivistiques"
}
```

## 📝 Exemples d'utilisation

### Curl
```bash
curl -X POST http://localhost:3001/api/records/reformulate \
  -H "Content-Type: application/json" \
  -d '{
    "id": "REC001",
    "name": "Documents travaux mairie",
    "date": "1920-1925",
    "content": "Travaux d agrandissement de la mairie"
  }'
```

### JavaScript/Node.js
```javascript
const response = await fetch('http://localhost:3001/api/records/reformulate', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    id: 'REC001',
    name: 'Documents travaux mairie',
    date: '1920-1925',
    content: 'Travaux d agrandissement de la mairie'
  })
});

const result = await response.json();
console.log(result); // { id: 'REC001', new_name: 'Mairie. — Agrandissement : plans, correspondance. 1920-1925' }
```

### Python
```python
import requests

data = {
    'id': 'REC001',
    'name': 'Documents travaux mairie',
    'date': '1920-1925',
    'content': 'Travaux d agrandissement de la mairie'
}

response = requests.post(
    'http://localhost:3001/api/records/reformulate',
    json=data,
    headers={'Content-Type': 'application/json'}
)

result = response.json()
print(f"ID: {result['id']}")
print(f"Nouveau nom: {result['new_name']}")
```

## 🎯 Normes appliquées

- **Structure** : `Objet. — Action : typologie. Dates`
- **Ponctuation** : Point-tiret (`. —`) après l'objet principal
- **Vocabulaire** : Termes archivistiques standardisés
- **Dates** : Harmonisation automatique des formats

## 🔧 Tests disponibles

```bash
# Test complet avec exemples multiples
npm run test:reformulation

# Test rapide avec curl (Windows)
scripts\test-curl.bat

# Validation de la structure
npm run validate

# Vérification état serveur
curl http://localhost:3001/api/health
```

## ⚙️ Configuration minimale

Fichier `.env` :
```env
PORT=3001
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_DEFAULT_MODEL=llama3.2
LOG_LEVEL=info
```

## 📊 Performance

- **Temps de réponse** : 1-3 secondes
- **Timeout** : 30 secondes maximum  
- **Limite contenu** : 10 000 caractères par champ

## 📚 Documentation

- [Guide détaillé](docs/SIMPLE_REFORMULATION.md) - Documentation complète de l'endpoint
- [Démarrage rapide](docs/QUICK_START.md) - Installation et configuration
- [Déploiement](docs/DEPLOYMENT.md) - Guide de mise en production

## 🛟 Support

En cas de problème :

1. Vérifier qu'Ollama est démarré : `ollama serve`
2. Vérifier le modèle : `ollama pull llama3.2`  
3. Tester la santé : `curl http://localhost:3001/api/health`
4. Consulter les logs : `logs/app.log` et `logs/error.log`

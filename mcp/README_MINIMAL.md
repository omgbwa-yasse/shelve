# MCP Shelve - Reformulation Simple

## Description

Service minimal de reformulation de noms d'archives. Un seul endpoint qui reçoit un JSON et renvoie le nom reformulé.

## 🚀 Démarrage

```bash
# 1. Démarrer Ollama
ollama serve
ollama pull llama3.2

# 2. Démarrer le serveur
cd mcp
npm install
npm run dev

# 3. Tester
npm run test:reformulation
```

## 📡 Endpoint unique

**URL:** `POST /api/records/reformulate`

**Entrée:**
```json
{
  "id": "REC001",
  "name": "Documents travaux mairie",
  "date": "1920-1925",
  "content": "Description du contenu",
  "author": { "name": "Nom auteur" },
  "children": [
    { "name": "Sous-document", "date": "1920", "content": "Description" }
  ]
}
```

**Sortie:**
```json
{
  "id": "REC001",
  "new_name": "Mairie. — Travaux : plans, correspondance. 1920-1925"
}
```

## 🧪 Test rapide

```bash
curl -X POST http://localhost:3001/api/records/reformulate \
  -H "Content-Type: application/json" \
  -d '{
    "id": "TEST001",
    "name": "Documents école",
    "date": "1958-1962",
    "content": "Construction école primaire"
  }'
```

**Champs obligatoires:** `id`, `name`  
**Champs optionnels:** `date`, `content`, `author`, `children`

Le service reformule automatiquement selon les normes archivistiques françaises.

## ⚙️ Configuration (.env)

```env
PORT=3001
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_DEFAULT_MODEL=llama3.2
```

C'est tout ! 🎯

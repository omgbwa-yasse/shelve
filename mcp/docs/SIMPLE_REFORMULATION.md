# Endpoint de Reformulation Simplifiée

## Description

Cet endpoint permet de reformuler le nom d'un enregistrement d'archive en utilisant toutes les données contextuelles fournies. Il respecte les normes archivistiques françaises.

## URL

```
POST /api/records/reformulate
```

## Format de la requête

```json
{
  "id": "string (requis)",
  "name": "string (requis)", 
  "date": "string (optionnel)",
  "content": "string (optionnel)",
  "author": {
    "name": "string (optionnel)"
  },
  "children": [
    {
      "name": "string (optionnel)",
      "date": "string (optionnel)", 
      "content": "string (optionnel)"
    }
  ]
}
```

## Format de la réponse

```json
{
  "id": "string",
  "new_name": "string"
}
```

## Exemples d'utilisation

### Exemple 1 : Document simple

**Requête :**
```json
{
  "id": "REC001",
  "name": "Documents travaux mairie",
  "date": "1920-1925",
  "content": "Dossier des travaux d'agrandissement de la mairie",
  "author": {
    "name": "Architecte Dubois"
  }
}
```

**Réponse :**
```json
{
  "id": "REC001", 
  "new_name": "Mairie. — Agrandissement : plans, correspondance. 1920-1925"
}
```

### Exemple 2 : Document avec enfants

**Requête :**
```json
{
  "id": "REC002",
  "name": "Dossier école construction",
  "content": "Construction nouvelle école primaire",
  "children": [
    {
      "name": "Plans architecte",
      "date": "1958",
      "content": "Plans de la nouvelle école"
    },
    {
      "name": "Devis travaux", 
      "date": "1959"
    }
  ]
}
```

**Réponse :**
```json
{
  "id": "REC002",
  "new_name": "École primaire. — Construction : plans, devis. 1958-1959"
}
```

### Exemple 3 : Document minimal

**Requête :**
```json
{
  "id": "REC003",
  "name": "Registre personnel"
}
```

**Réponse :**
```json
{
  "id": "REC003",
  "new_name": "Personnel. — Registre. [s.d.]"
}
```

## Règles de reformulation appliquées

1. **Structure archivistique** : `Objet. — Action : typologie. Dates`
2. **Contexte enrichi** : Utilise le contenu, l'auteur et les enfants pour améliorer la reformulation
3. **Normes françaises** : Respect de la ponctuation archivistique (point-tiret `. —`)
4. **Dates normalisées** : Harmonisation automatique des formats de dates
5. **Vocabulaire contrôlé** : Utilisation de termes archivistiques standardisés

## Test de l'endpoint

```bash
# Test simple
npm run test:reformulation

# Test avec curl
curl -X POST http://localhost:3001/api/records/reformulate \
  -H "Content-Type: application/json" \
  -d '{
    "id": "TEST001",
    "name": "Documents mairie",
    "date": "1950-1960",
    "content": "Correspondance administrative"
  }'
```

## Codes d'erreur

- **400** : Données invalides (ID ou nom manquant)
- **500** : Erreur de traitement IA ou serveur
- **503** : Service Ollama indisponible

## Performance

- **Temps de réponse moyen** : 1-3 secondes
- **Timeout** : 30 secondes maximum
- **Limitation** : Contenu maximum 10 000 caractères par champ

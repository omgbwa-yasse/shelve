# 🔌 SHELVE - Guide API Utilisateur

**Version**: 1.0  
**Date**: 7 novembre 2025  
**API Version**: v1

---

## 📚 Table des Matières

1. [Introduction](#introduction)
2. [Authentification](#authentification)
3. [Concepts de Base](#concepts-de-base)
4. [Endpoints Principaux](#endpoints-principaux)
5. [Exemples par Langage](#exemples-par-langage)
6. [Gestion des Erreurs](#gestion-des-erreurs)
7. [Rate Limiting](#rate-limiting)
8. [Webhooks](#webhooks)
9. [Bonnes Pratiques](#bonnes-pratiques)

---

## 🎯 Introduction

### Qu'est-ce que l'API SHELVE ?

L'API SHELVE est une **API REST** qui vous permet d'interagir programmatiquement avec le système SHELVE pour:

- 📥 **Créer, lire, mettre à jour, supprimer** des ressources (CRUD)
- 🔍 **Rechercher** dans les collections
- 📊 **Obtenir des statistiques** et rapports
- 🔔 **Recevoir des notifications** via webhooks
- 🔄 **Intégrer** SHELVE avec d'autres systèmes

### Prérequis

- **Token API** (voir [Authentification](#authentification))
- Connaissances de base en **HTTP/REST**
- Outil de requêtes HTTP (curl, Postman, code)

### URL de Base

```
https://votre-shelve.local/api/v1
```

### Documentation Interactive

**Swagger UI**: `https://votre-shelve.local/api/documentation`

---

## 🔐 Authentification

### Obtenir un Token API

**Via l'interface Web**:

1. Connectez-vous à SHELVE
2. Menu **Profil** > **API**
3. **"Générer Nouveau Token"**
4. Configurez:
   - **Nom**: Description du token
   - **Expiration**: 30j, 90j, 1an, jamais
   - **Permissions**: Lecture, Lecture/Écriture
5. **Copiez le token** (affiché une seule fois !)

### Utiliser le Token

**Header HTTP**:

```http
Authorization: Bearer VOTRE_TOKEN_ICI
```

**Exemple curl**:

```bash
curl -H "Authorization: Bearer sk_live_abc123..." \
  https://votre-shelve.local/api/v1/books
```

### Sécurité

⚠️ **Important**:
- Ne partagez JAMAIS votre token
- Stockez-le de manière sécurisée (variables d'environnement)
- Utilisez HTTPS uniquement
- Révoquez les tokens inutilisés

---

## 📖 Concepts de Base

### Format des Requêtes

**Content-Type**: `application/json`

```http
POST /api/v1/books
Content-Type: application/json
Authorization: Bearer TOKEN

{
  "title": "Les Misérables",
  "author": "Victor Hugo",
  "isbn": "978-2-07-036789-0"
}
```

### Format des Réponses

**Succès** (200-299):

```json
{
  "data": {
    "id": 123,
    "title": "Les Misérables",
    "author": "Victor Hugo"
  },
  "meta": {
    "timestamp": "2025-11-07T10:30:00Z"
  }
}
```

**Erreur** (400-599):

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Le champ 'title' est requis",
    "details": {
      "field": "title",
      "rule": "required"
    }
  }
}
```

### Pagination

**Paramètres**:
- `page`: Numéro de page (défaut: 1)
- `per_page`: Éléments par page (défaut: 25, max: 100)

**Requête**:

```bash
GET /api/v1/books?page=2&per_page=50
```

**Réponse**:

```json
{
  "data": [...],
  "meta": {
    "current_page": 2,
    "per_page": 50,
    "total": 1234,
    "last_page": 25
  },
  "links": {
    "first": "/api/v1/books?page=1",
    "prev": "/api/v1/books?page=1",
    "next": "/api/v1/books?page=3",
    "last": "/api/v1/books?page=25"
  }
}
```

### Filtrage

**Syntaxe**:

```
GET /api/v1/books?filter[author]=Hugo&filter[year]=1862
```

**Opérateurs**:
- `filter[field]=value` - Égalité
- `filter[field][gt]=100` - Supérieur à
- `filter[field][gte]=100` - Supérieur ou égal
- `filter[field][lt]=100` - Inférieur à
- `filter[field][lte]=100` - Inférieur ou égal
- `filter[field][like]=%hugo%` - Contient

### Tri

```
GET /api/v1/books?sort=-created_at,title
```

- `sort=field` - Ordre croissant
- `sort=-field` - Ordre décroissant
- Multiples champs séparés par `,`

### Inclusion de Relations

```
GET /api/v1/books/123?include=author,publisher,reviews
```

---

## 📍 Endpoints Principaux

### Books (Livres)

**Lister les livres**:

```http
GET /api/v1/books
```

**Obtenir un livre**:

```http
GET /api/v1/books/{id}
```

**Créer un livre**:

```http
POST /api/v1/books
Content-Type: application/json

{
  "title": "1984",
  "author": "George Orwell",
  "isbn": "978-0-452-28423-4",
  "publisher": "Penguin Books",
  "publication_year": 1949,
  "language": "en",
  "pages": 328
}
```

**Mettre à jour un livre**:

```http
PUT /api/v1/books/{id}
Content-Type: application/json

{
  "title": "1984 (Edition Annotée)"
}
```

ou

```http
PATCH /api/v1/books/{id}
Content-Type: application/json

{
  "pages": 350
}
```

**Supprimer un livre**:

```http
DELETE /api/v1/books/{id}
```

---

### Documents

**Lister les documents**:

```http
GET /api/v1/documents
```

**Créer un document**:

```http
POST /api/v1/documents
Content-Type: application/json

{
  "title": "Rapport Annuel 2025",
  "type": "report",
  "status": "draft",
  "folder_id": 42,
  "metadata": {
    "author": "John Doe",
    "department": "Finance"
  }
}
```

**Upload de fichier**:

```http
POST /api/v1/documents/{id}/files
Content-Type: multipart/form-data

file=@/path/to/document.pdf
```

**Workflow (Soumettre pour approbation)**:

```http
POST /api/v1/documents/{id}/submit
Content-Type: application/json

{
  "message": "Prêt pour révision"
}
```

**Approuver un document**:

```http
POST /api/v1/documents/{id}/approve
Content-Type: application/json

{
  "comment": "Approuvé"
}
```

---

### Digital Folders

**Arborescence complète**:

```http
GET /api/v1/folders/tree
```

**Créer un dossier**:

```http
POST /api/v1/folders
Content-Type: application/json

{
  "name": "Rapports 2025",
  "parent_id": 10,
  "description": "Tous les rapports de 2025"
}
```

**Déplacer des ressources**:

```http
POST /api/v1/folders/{id}/move
Content-Type: application/json

{
  "resource_type": "document",
  "resource_ids": [123, 456, 789],
  "target_folder_id": 42
}
```

---

### Artifacts (Objets)

**Lister les artifacts**:

```http
GET /api/v1/artifacts
```

**Créer un artifact**:

```http
POST /api/v1/artifacts
Content-Type: application/json

{
  "name": "Vase Grec Antique",
  "type": "pottery",
  "acquisition_date": "2020-05-15",
  "dimensions": {
    "height": 35,
    "width": 20,
    "depth": 20,
    "unit": "cm"
  },
  "material": "Céramique",
  "period": "Antiquité",
  "condition": "bon"
}
```

**Enregistrer un prêt**:

```http
POST /api/v1/artifacts/{id}/loans
Content-Type: application/json

{
  "borrower": "Musée du Louvre",
  "start_date": "2025-12-01",
  "end_date": "2026-03-31",
  "purpose": "Exposition temporaire"
}
```

---

### Periodicals (Périodiques)

**Créer un périodique**:

```http
POST /api/v1/periodicals
Content-Type: application/json

{
  "title": "Nature",
  "issn": "0028-0836",
  "publisher": "Nature Publishing Group",
  "frequency": "weekly"
}
```

**Ajouter un numéro**:

```http
POST /api/v1/periodicals/{id}/issues
Content-Type: application/json

{
  "volume": 615,
  "issue": 7954,
  "publication_date": "2025-03-15",
  "pages": "1-250"
}
```

**Ajouter un article**:

```http
POST /api/v1/periodicals/{periodical_id}/issues/{issue_id}/articles
Content-Type: application/json

{
  "title": "Discovery of a new exoplanet",
  "authors": ["Smith J.", "Doe A."],
  "pages": "45-52",
  "doi": "10.1038/nature12345",
  "abstract": "We report the discovery of..."
}
```

---

### Recherche

**Recherche globale**:

```http
GET /api/v1/search?q=rapport+2025
```

**Recherche par type**:

```http
GET /api/v1/search?q=rapport&type=document&type=book
```

**Recherche avancée**:

```http
POST /api/v1/search/advanced
Content-Type: application/json

{
  "query": "rapport financier",
  "filters": {
    "type": ["document"],
    "date_range": {
      "from": "2025-01-01",
      "to": "2025-12-31"
    },
    "status": ["approved"],
    "folder_id": 42
  },
  "sort": "-created_at",
  "per_page": 50
}
```

---

## 💻 Exemples par Langage

### PHP

```php
<?php

// Configuration
$baseUrl = 'https://votre-shelve.local/api/v1';
$token = 'sk_live_abc123...';

// Client HTTP (avec Guzzle)
$client = new \GuzzleHttp\Client([
    'base_uri' => $baseUrl,
    'headers' => [
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ]
]);

// Lister les livres
try {
    $response = $client->get('/books', [
        'query' => [
            'page' => 1,
            'per_page' => 25,
            'filter' => [
                'author' => 'Hugo'
            ]
        ]
    ]);
    
    $books = json_decode($response->getBody(), true);
    
    foreach ($books['data'] as $book) {
        echo $book['title'] . "\n";
    }
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage();
}

// Créer un livre
$newBook = [
    'title' => 'Notre-Dame de Paris',
    'author' => 'Victor Hugo',
    'isbn' => '978-2-07-036790-6',
    'publication_year' => 1831
];

try {
    $response = $client->post('/books', [
        'json' => $newBook
    ]);
    
    $created = json_decode($response->getBody(), true);
    echo "Livre créé avec ID: " . $created['data']['id'];
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

### Python

```python
import requests
import json

# Configuration
BASE_URL = 'https://votre-shelve.local/api/v1'
TOKEN = 'sk_live_abc123...'

headers = {
    'Authorization': f'Bearer {TOKEN}',
    'Content-Type': 'application/json'
}

# Lister les livres
response = requests.get(
    f'{BASE_URL}/books',
    headers=headers,
    params={
        'page': 1,
        'per_page': 25,
        'filter[author]': 'Hugo'
    }
)

if response.status_code == 200:
    books = response.json()
    for book in books['data']:
        print(book['title'])
else:
    print(f"Erreur: {response.status_code}")

# Créer un livre
new_book = {
    'title': 'Les Travailleurs de la Mer',
    'author': 'Victor Hugo',
    'isbn': '978-2-07-036791-3',
    'publication_year': 1866
}

response = requests.post(
    f'{BASE_URL}/books',
    headers=headers,
    json=new_book
)

if response.status_code == 201:
    created = response.json()
    print(f"Livre créé avec ID: {created['data']['id']}")
else:
    print(f"Erreur: {response.text}")

# Fonction helper pour pagination
def get_all_books():
    all_books = []
    page = 1
    
    while True:
        response = requests.get(
            f'{BASE_URL}/books',
            headers=headers,
            params={'page': page, 'per_page': 100}
        )
        
        data = response.json()
        all_books.extend(data['data'])
        
        if page >= data['meta']['last_page']:
            break
        
        page += 1
    
    return all_books
```

### JavaScript (Node.js)

```javascript
const axios = require('axios');

// Configuration
const BASE_URL = 'https://votre-shelve.local/api/v1';
const TOKEN = 'sk_live_abc123...';

const client = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Authorization': `Bearer ${TOKEN}`,
    'Content-Type': 'application/json'
  }
});

// Lister les livres
async function getBooks() {
  try {
    const response = await client.get('/books', {
      params: {
        page: 1,
        per_page: 25,
        'filter[author]': 'Hugo'
      }
    });
    
    response.data.data.forEach(book => {
      console.log(book.title);
    });
  } catch (error) {
    console.error('Erreur:', error.response?.data || error.message);
  }
}

// Créer un livre
async function createBook() {
  const newBook = {
    title: 'L\'Homme qui Rit',
    author: 'Victor Hugo',
    isbn: '978-2-07-036792-0',
    publication_year: 1869
  };
  
  try {
    const response = await client.post('/books', newBook);
    console.log(`Livre créé avec ID: ${response.data.data.id}`);
    return response.data.data;
  } catch (error) {
    console.error('Erreur:', error.response?.data || error.message);
  }
}

// Helper pour pagination
async function getAllBooks() {
  const allBooks = [];
  let page = 1;
  let lastPage = 1;
  
  do {
    const response = await client.get('/books', {
      params: { page, per_page: 100 }
    });
    
    allBooks.push(...response.data.data);
    lastPage = response.data.meta.last_page;
    page++;
  } while (page <= lastPage);
  
  return allBooks;
}

// Exécution
(async () => {
  await getBooks();
  await createBook();
})();
```

### cURL

```bash
#!/bin/bash

BASE_URL="https://votre-shelve.local/api/v1"
TOKEN="sk_live_abc123..."

# Lister les livres
curl -X GET "${BASE_URL}/books?page=1&per_page=25" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json"

# Créer un livre
curl -X POST "${BASE_URL}/books" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Quatrevingt-Treize",
    "author": "Victor Hugo",
    "isbn": "978-2-07-036793-7",
    "publication_year": 1874
  }'

# Mettre à jour un livre
curl -X PATCH "${BASE_URL}/books/123" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "pages": 420
  }'

# Supprimer un livre
curl -X DELETE "${BASE_URL}/books/123" \
  -H "Authorization: Bearer ${TOKEN}"

# Upload de fichier
curl -X POST "${BASE_URL}/documents/456/files" \
  -H "Authorization: Bearer ${TOKEN}" \
  -F "file=@/path/to/document.pdf"
```

---

## ⚠️ Gestion des Erreurs

### Codes HTTP Standard

| Code | Signification | Action |
|------|---------------|--------|
| 200 | OK | Succès |
| 201 | Created | Ressource créée |
| 204 | No Content | Succès sans contenu |
| 400 | Bad Request | Vérifier les données |
| 401 | Unauthorized | Token invalide/expiré |
| 403 | Forbidden | Permissions insuffisantes |
| 404 | Not Found | Ressource introuvable |
| 422 | Unprocessable Entity | Validation échouée |
| 429 | Too Many Requests | Rate limit dépassé |
| 500 | Internal Server Error | Erreur serveur |

### Format des Erreurs

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Les données fournies sont invalides",
    "details": {
      "title": ["Le champ title est requis"],
      "isbn": ["Format ISBN invalide"]
    },
    "trace_id": "abc123def456"
  }
}
```

### Gestion en PHP

```php
try {
    $response = $client->post('/books', ['json' => $data]);
    $book = json_decode($response->getBody(), true);
} catch (\GuzzleHttp\Exception\ClientException $e) {
    $error = json_decode($e->getResponse()->getBody(), true);
    
    switch ($e->getResponse()->getStatusCode()) {
        case 401:
            // Token invalide
            renewToken();
            break;
        case 422:
            // Erreurs de validation
            foreach ($error['error']['details'] as $field => $messages) {
                echo "$field: " . implode(', ', $messages) . "\n";
            }
            break;
        case 429:
            // Rate limit
            sleep(60);
            retry();
            break;
        default:
            echo "Erreur: " . $error['error']['message'];
    }
}
```

### Gestion en Python

```python
try:
    response = requests.post(f'{BASE_URL}/books', json=data, headers=headers)
    response.raise_for_status()
    book = response.json()
except requests.exceptions.HTTPError as e:
    if e.response.status_code == 401:
        # Token invalide
        renew_token()
    elif e.response.status_code == 422:
        # Validation errors
        errors = e.response.json()['error']['details']
        for field, messages in errors.items():
            print(f"{field}: {', '.join(messages)}")
    elif e.response.status_code == 429:
        # Rate limit
        time.sleep(60)
        retry()
    else:
        print(f"Erreur: {e.response.json()['error']['message']}")
```

---

## ⏱️ Rate Limiting

### Limites

**Par Token**:
- **60 requêtes/minute** (standard)
- **300 requêtes/minute** (premium)

**Par IP** (sans token):
- **30 requêtes/minute**

### Headers de Réponse

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699876543
```

### Bonnes Pratiques

```python
import time

def api_call_with_retry(url, max_retries=3):
    for attempt in range(max_retries):
        response = requests.get(url, headers=headers)
        
        if response.status_code == 429:
            # Rate limit atteint
            reset_time = int(response.headers.get('X-RateLimit-Reset', 0))
            wait_time = reset_time - time.time()
            
            if wait_time > 0:
                print(f"Rate limit atteint. Attente de {wait_time}s...")
                time.sleep(wait_time + 1)
                continue
        
        return response
    
    raise Exception("Max retries atteint")
```

---

## 🔔 Webhooks

### Configuration

**Via l'interface**:
1. Menu **Profil** > **API** > **Webhooks**
2. **"+ Nouveau Webhook"**
3. Configurez:
   - **URL**: Endpoint à appeler
   - **Événements**: book.created, document.approved, etc.
   - **Secret**: Pour signature HMAC

### Événements Disponibles

| Événement | Description |
|-----------|-------------|
| `book.created` | Nouveau livre créé |
| `book.updated` | Livre modifié |
| `book.deleted` | Livre supprimé |
| `document.created` | Nouveau document |
| `document.submitted` | Document soumis |
| `document.approved` | Document approuvé |
| `document.rejected` | Document rejeté |
| `artifact.loaned` | Objet prêté |
| `artifact.returned` | Objet retourné |

### Format du Payload

```json
{
  "event": "book.created",
  "timestamp": "2025-11-07T10:30:00Z",
  "data": {
    "id": 123,
    "title": "Nouveau Livre",
    "author": "Auteur"
  }
}
```

### Vérification de Signature

```python
import hmac
import hashlib

def verify_webhook(request, secret):
    signature = request.headers.get('X-Webhook-Signature')
    payload = request.body
    
    computed = hmac.new(
        secret.encode(),
        payload,
        hashlib.sha256
    ).hexdigest()
    
    return hmac.compare_digest(signature, computed)
```

---

## ✅ Bonnes Pratiques

### 1. Sécurité

```python
# ✅ BON - Token dans variable d'environnement
import os
TOKEN = os.getenv('SHELVE_API_TOKEN')

# ❌ MAUVAIS - Token en dur dans le code
TOKEN = 'sk_live_abc123...'
```

### 2. Gestion des Erreurs

```javascript
// ✅ BON - Gestion complète
try {
  const response = await api.get('/books/123');
  return response.data;
} catch (error) {
  if (error.response) {
    // Erreur serveur
    console.error('Status:', error.response.status);
    console.error('Data:', error.response.data);
  } else if (error.request) {
    // Pas de réponse
    console.error('Pas de réponse du serveur');
  } else {
    // Erreur de configuration
    console.error('Erreur:', error.message);
  }
  throw error;
}
```

### 3. Pagination

```php
// ✅ BON - Récupérer toutes les pages
function getAllBooks($client) {
    $allBooks = [];
    $page = 1;
    
    do {
        $response = $client->get('/books', [
            'query' => ['page' => $page, 'per_page' => 100]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $allBooks = array_merge($allBooks, $data['data']);
        $lastPage = $data['meta']['last_page'];
        $page++;
    } while ($page <= $lastPage);
    
    return $allBooks;
}
```

### 4. Caching

```python
import requests_cache

# Cache les réponses pendant 1 heure
requests_cache.install_cache('shelve_cache', expire_after=3600)

# Les requêtes identiques utilisent le cache
response = requests.get(f'{BASE_URL}/books', headers=headers)
```

### 5. Retry Logic

```javascript
const retry = require('async-retry');

await retry(async () => {
  const response = await api.get('/books/123');
  return response.data;
}, {
  retries: 3,
  factor: 2,
  minTimeout: 1000,
  onRetry: (error, attempt) => {
    console.log(`Tentative ${attempt} échouée:`, error.message);
  }
});
```

---

## 📚 Ressources

**Documentation**:
- Swagger UI: `/api/documentation`
- OpenAPI Spec: `/api-docs/openapi.yaml`
- Postman Collection: Disponible sur demande

**Support**:
- Email: api-support@shelve.local
- GitHub Issues: (si applicable)

**Changelog**:
- API v1.0: Version initiale (Nov 2025)

---

**Version**: 1.0  
**Dernière Mise à Jour**: 7 novembre 2025  
**Prochaine Révision**: Décembre 2025

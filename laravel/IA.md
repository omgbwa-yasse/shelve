# Module IA — Description détaillée du fonctionnement

## Vue d'ensemble

Le module IA de **Shelve** est un assistant de recherche en langage naturel intégré à l'application de gestion d'archives. Il permet aux utilisateurs de poser des questions en français (ou toute autre langue) et d'obtenir des résultats précis extraits directement de la base de données, sans connaître la structure technique du système.

L'interface est accessible via la route `/ai-search` et requiert une authentification.

---

## Architecture générale

Le module repose sur un pipeline en **3 étapes** orchestré par `AiSearchController` :

```
Utilisateur
    │
    ▼
[1] QueryAnalyzerService   ← envoie la requête texte à un LLM
    │ retourne des instructions JSON
    ▼
[2] QueryExecutorService   ← exécute la requête SQL/Eloquent
    │ retourne des données brutes
    ▼
[3] ResponseFormatterService ← formate la réponse pour l'interface
    │
    ▼
Interface chat (JSON → HTML)
```

---

## Étape 1 — Analyse de la requête (`QueryAnalyzerService`)

### Rôle
Transforme une phrase en langage naturel en un objet JSON d'instructions structurées.

### Fonctionnement
1. Récupère le **provider IA par défaut** depuis les paramètres de la base de données (`ai_default_provider`).  
2. Configure ce provider via `ProviderRegistry::ensureConfigured()`.  
3. Construit un **prompt système** spécialisé selon le type de recherche (`records`, `mails`, `communications`, `slips`, `authors`).  
4. Envoie au LLM le message système + la requête utilisateur.  
5. Extrait et parse la réponse JSON produite par le LLM.

### Format JSON retourné par le LLM
```json
{
  "action": "filter",
  "keywords": ["Martin", "urgent"],
  "filters": {
    "author": "Martin",
    "year": 2024,
    "priority": "urgent"
  },
  "fields": ["name", "code", "created_at"],
  "limit": 10,
  "order": "desc",
  "table": "records"
}
```

### Actions reconnues
| Action       | Comportement                                         |
|--------------|------------------------------------------------------|
| `search`     | Recherche full-text sur des champs précis            |
| `count`      | Compte le nombre d'éléments (sans lister)            |
| `filter`     | Filtre par critères et retourne la liste             |
| `list`       | Liste les éléments récents ou tous                   |
| `show`       | Affiche un élément identifié par son ID              |
| `date_range` | Filtre sur une plage de dates et retourne la liste   |
| `advanced`   | Combinaison de critères multiples complexes          |

### Prompt système — règles d'interprétation
- **`count`** est utilisé **uniquement** si l'utilisateur pose explicitement la question "combien".  
- Dans tous les autres cas, `filter` ou `date_range` sont utilisés pour retourner une liste.  
- Les expressions temporelles sont automatiquement converties en dates concrètes :  
  - "hier" → `date_from = date_to = J-1`  
  - "cette semaine" → 7 derniers jours  
  - "ce mois" → mois en cours  
  - "en 2024" → `year: 2024`  
- Le prompt inclut les champs disponibles par table et leurs noms exacts.

### Modèles LLM supportés
La sélection du modèle est gérée par `ProviderRegistry` et `DefaultValueService`. Les providers configurables sont :

| Provider        | Modèle par défaut            | Clé stockée en BDD              |
|-----------------|------------------------------|---------------------------------|
| `ollama`        | `gemma3:4b` (ou configuré)   | `ollama_base_url`, activé par `ollama_enabled` |
| `mistral`       | `mistral-large-latest`       | `mistral_default_model`         |
| `openai`        | `gpt-4`                      | `openai_api_key`                |
| `claude`        | `claude-3-5-sonnet-20241022` | `claude_api_key`                |
| `gemini`        | —                            | `gemini_api_key`                |
| `openrouter`    | —                            | `openrouter_api_key`            |
| `grok`          | —                            | `grok_api_key`                  |
| `onn`           | —                            | (On-device)                     |
| `openai_custom` | —                            | `openai_custom_*`               |

Pour **Ollama** (mode local), le service vérifie d'abord que le provider est activé en base, puis teste la connectivité HTTP avant d'enregistrer le provider via l'API compatible OpenAI (`/v1/chat/completions`).

---

## Étape 2 — Exécution de la requête (`QueryExecutorService`)

### Rôle
Reçoit les instructions JSON et les traduit en requêtes Eloquent/DB sur les tables correspondantes du système.

### Tables gérées

| Identifiant       | Table / Modèle          | Description                    |
|-------------------|-------------------------|--------------------------------|
| `records`         | `RecordPhysical`        | Documents et archives physiques |
| `mails`           | `Mail`                  | Courriers entrants et sortants  |
| `communications`  | `Communication`         | Échanges et consultations       |
| `slips`           | `Slip`                  | Bordereaux de transfert         |
| `authors`         | (table `authors`)       | Auteurs et créateurs            |

### Filtres appliqués dynamiquement
Par table, les filtres suivants sont reconnus :

**Records :**
- `author` — rejoint la table `authors` (nom ou prénom)  
- `activity` — rejoint la table `activities`  
- `status` — rejoint la table `record_statuses`  
- `term` — recherche dans les mots-clés indexés  
- `container` — rejoint la table `containers`  
- `year`, `month`, `date_from`, `date_to` — sur `date_start`, `date_end`, `created_at`  

**Mails :**
- `priority` — rejoint la table `mail_priorities`  
- `typology` — rejoint la table `mail_typologies`  
- `mail_type` — entrant/sortant  
- `author` — expéditeur ou destinataire  
- `date_from`, `date_to` — sur `received_date` ou `created_at`  

**Communications :**
- `status` — en cours / terminé / annulé  
- `operator` — agent responsable  
- `user` — demandeur  
- `return_date` — date de retour prévue  

**Slips / Bordereaux :**
- `slip_status` — reçu / approuvé / intégré  
- `officer` — agent de transfert  
- `user` — initiateur  
- `received_date`, `approved_date`, `integrated_date`  

### Résultat retourné
```php
[
    'success' => true,
    'action'  => 'filter',
    'data'    => [...],  // tableau d'enregistrements
    'count'   => 12,
    'message' => "Trouvé 12 résultat(s) avec les filtres appliqués"
]
```

---

## Étape 3 — Formatage de la réponse (`ResponseFormatterService`)

### Rôle
Transforme les données brutes en une réponse structurée prête à être affichée dans l'interface chat.

### Comportement par action

| Action    | Réponse produite                                              |
|-----------|---------------------------------------------------------------|
| `count`   | Message textuel uniquement : "Il y a X élément(s)"            |
| `search`  | Liste de résultats avec titre, URL directe et description     |
| `filter`  | Idem `search`                                                 |
| `list`    | Idem `search`                                                 |
| `show`    | Fiche détaillée d'un seul élément                             |

Chaque résultat inclut :
- **titre** — nom ou code de l'enregistrement  
- **url** — lien direct vers la fiche dans l'application  
- **description** — informations contextuelles (dates, auteur, priorité…)  
- **type** — catégorie de l'élément (`records`, `mails`…)  

---

## Auto-détection du type de recherche

Avant même de contacter le LLM, `AiSearchController::detectSearchType()` analyse les mots-clés de la requête pour orienter automatiquement la recherche vers la bonne table :

| Mots-clés détectés                                       | Type sélectionné   |
|----------------------------------------------------------|--------------------|
| auteur, auteurs, écrivain, rédacteur                     | `authors`          |
| mail, email, courrier, correspondance, message           | `mails`            |
| communication, échange, dialogue                         | `communications`   |
| bordereau, transfert, borderaux, slip, envoi             | `slips`            |
| (aucun mot-clé spécifique)                               | valeur par défaut  |

L'utilisateur peut aussi forcer le type via le sélecteur graphique de l'interface.

---

## Interface utilisateur

### Saisie
- **Champ texte** — saisie libre en langage naturel  
- **Reconnaissance vocale** — bouton micro (`Ctrl+Shift+V`) utilisant l'API Web Speech du navigateur  
  - Option d'envoi automatique après la reconnaissance  
  - Indicateur visuel animé pendant l'enregistrement  
- **Bouton Envoyer** — soumission du formulaire  

### Sélecteur de type
Barre de boutons permettant de cibler :
- Documents / Archives (`records`)  
- Courriers (`mails`)  
- Communications  
- Transferts (`slips`)  

### Affichage des résultats
Les réponses de l'IA s'affichent dans une interface de type **chat** avec :
- Bulles "IA" (côté gauche, avatar robot)  
- Bulles "utilisateur" (côté droit)  
- Animation d'entrée (`messageSlideIn`)  
- Horodatage de chaque message  
- Liens cliquables dans les résultats  

---

## Fonctionnalité de résumé IA des courriers (`AiMailController`)

En complément de la recherche, le module propose la **génération automatique d'un résumé** pour chaque courrier (`/api/mails/{id}/ai/summarize`) :

1. Charge le courrier avec toutes ses relations (expéditeur, destinataire, typologie, pièces jointes…).  
2. Construit un contexte textuel structuré via `AiMessageBuilder`.  
3. Analyse les pièces jointes avec priorité :  
   - Contenu texte déjà extrait (`content_text`)  
   - Fichiers texte bruts  
   - PDF (extraction via `pdftotext`)  
   - Images (description du type uniquement)  
4. Génère un résumé narratif basé sur les données réelles du courrier.  
5. Propose des **mots-clés archivistiques** par catégorie (Typologie, Action, Priorité…).  
6. Le résumé peut être **sauvegardé** dans le champ `description` du courrier (`POST /api/mails/{id}/ai/save-summary`).

---

## Traçabilité (`PromptTransactionService`)

Chaque interaction avec un LLM est enregistrée dans la table `prompt_transactions` avec :
- le statut (`started`, `succeeded`, `failed`, `cancelled`)  
- le provider et le modèle utilisés  
- les messages envoyés et la réponse reçue  
- les métadonnées de timing  

Ce service gère la compatibilité avec différents schémas de table (colonnes variables selon les migrations) via une introspection dynamique.

---

## Extraction de la réponse LLM (`ResponseTextExtractor`)

La classe `ResponseTextExtractor` normalise les réponses de tous les providers, qui utilisent des formats différents :

| Provider     | Format de réponse                                      |
|--------------|--------------------------------------------------------|
| Claude       | `content[].text`                                       |
| OpenAI       | `choices[0].message.content`                           |
| Ollama       | `message.content`                                      |
| Autres       | `text`, `response`, ou réponse brute sous forme string |

---

## Sécurité et limitations

- **Accès authentifié uniquement** — toutes les routes sont sous le middleware `auth`.  
- **Lecture seule** — le module ne peut pas créer, modifier ou supprimer des données.  
- **Clés API chiffrées** — les clés des providers cloud sont stockées chiffrées en base de données (via `Crypt`).  
- **Timeout Ollama** — le service vérifie la connectivité avant d'enregistrer le provider local.  
- **Limite de résultats** — par défaut 10 résultats pour `search`/`filter`, 20 pour `date_range`.

---

## Fichiers clés du module

| Fichier                                              | Rôle                                                  |
|------------------------------------------------------|-------------------------------------------------------|
| `app/Http/Controllers/AiSearchController.php`        | Controller principal — orchestre le pipeline          |
| `app/Http/Controllers/Api/AiMailController.php`      | Résumé IA des courriers                               |
| `app/Services/AI/QueryAnalyzerService.php`           | Analyse NLP → JSON d'instructions                     |
| `app/Services/AI/QueryExecutorService.php`           | Exécution des requêtes base de données                |
| `app/Services/AI/ResponseFormatterService.php`       | Formatage des résultats pour l'interface              |
| `app/Services/AI/ProviderRegistry.php`               | Enregistrement et configuration des providers LLM    |
| `app/Services/AI/DefaultValueService.php`            | Lecture des paramètres IA depuis la base de données   |
| `app/Services/AI/SearchActionService.php`            | Actions de recherche bas niveau (Eloquent)            |
| `app/Services/AI/AiMessageBuilder.php`               | Construction des messages et contexte pour le LLM    |
| `app/Services/AI/AiRecordContextBuilder.php`         | Contexte archivistique pour les enregistrements       |
| `app/Services/AI/ResponseTextExtractor.php`          | Normalisation des réponses multi-providers            |
| `app/Services/AI/PromptTransactionService.php`       | Traçabilité des interactions LLM                      |
| `app/Services/AI/ActionMixerService.php`             | Post-traitement et extraction d'actions depuis l'IA   |
| `resources/views/ai-search/index.blade.php`          | Vue principale de l'interface chat                    |
| `resources/views/ai-search/documentation.blade.php`  | Documentation intégrée pour les utilisateurs          |
| `routes/web.php` (lignes 954–964)                    | Déclaration des routes du module                      |

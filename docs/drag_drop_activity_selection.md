# Sélection d'Activité dans le Drag & Drop

## Vue d'ensemble

Le système de Drag & Drop utilise maintenant une logique intelligente pour sélectionner les activités, en priorisant les activités liées à l'organisation de l'utilisateur connecté et en incluant leurs descendants récursifs.

## Processus de sélection d'activité

### 1. Activités proposées à l'IA

Lorsque l'IA analyse les fichiers uploadés, elle reçoit **uniquement** les activités autorisées pour l'organisation de l'utilisateur :

```php
// Restreindre aux activités de l'organisation courante (parents + enfants récursifs)
$orgId = optional(Auth::user())->current_organisation_id;
$allowedActivities = $this->getOrganisationActivitiesWithDescendants($orgId);
```

La fonction `getOrganisationActivitiesWithDescendants()` :
1. Récupère les activités directement associées à l'organisation
2. Récupère **récursivement** tous les descendants de ces activités
3. Retourne la liste complète (code + nom)

### 2. Recherche de l'activité suggérée par l'IA

Lorsque l'IA suggère une activité, le système la recherche dans la base de données :

1. **Recherche par code** : Priorité à la recherche par code dans les activités de l'organisation
2. **Recherche par nom** : Si pas trouvé par code, recherche par nom dans les activités de l'organisation

```php
if ($activitySuggestion && isset($activitySuggestion['code'])) {
    $orgId = optional(Auth::user())->current_organisation_id;
    $db = Activity::where('code', $activitySuggestion['code'])
        ->when($orgId, function ($q) use ($orgId) {
            $q->whereHas('organisations', function ($q2) use ($orgId) {
                $q2->where('organisations.id', $orgId);
            });
        })
        ->first();
    // ...
}
```

### 3. Activité par défaut

Si aucune activité n'est trouvée ou suggérée, le système utilise une activité par défaut avec l'ordre de priorité suivant :

1. **Première activité de l'organisation** (triée par code)
2. **Première activité disponible** (si l'organisation n'a pas d'activité)

```php
private function getDefaultActivityId(): int
{
    $orgId = optional(Auth::user())->current_organisation_id;
    
    if ($orgId) {
        // Chercher la première activité de l'organisation
        $activityId = Activity::query()
            ->whereHas('organisations', function ($q) use ($orgId) {
                $q->where('organisations.id', $orgId);
            })
            ->orderBy('code')
            ->value('id');
            
        if ($activityId) {
            return (int) $activityId;
        }
    }
    
    // Fallback : première activité disponible
    return (int) Activity::query()->orderBy('code')->value('id');
}
```

## Affichage dans l'interface

### Activité trouvée par l'IA

Quand l'IA trouve une activité correspondante :
- ✅ Affichage du nom et du code de l'activité
- ✅ Barre de confiance indiquant le niveau de certitude (0-100%)
- ✅ Pas d'avertissement

### Activité par défaut

Quand aucune activité n'est trouvée :
- ⚠️ Bordure jaune autour du bloc d'activité
- ⚠️ Icône d'avertissement
- ⚠️ Message informatif : "L'IA n'a pas trouvé d'activité correspondante dans les activités de votre organisation. L'activité ci-dessus sera utilisée par défaut. Vous pourrez la modifier après création."

## Structure des données

### Réponse JSON

```json
{
  "success": true,
  "record_id": 123,
  "ai_suggestions": {
    "title": "Titre du document",
    "content": "Description générée",
    "keywords": ["mot1", "mot2"],
    "activity_suggestion": {
      "id": 45,
      "code": "DF-01110",
      "name": "COLLECTE DES PRÉVISIONS BUDGÉTAIRES",
      "confidence": 0.85
    },
    "is_default_activity": false
  },
  "attachments": [...]
}
```

### Champs de l'activité suggérée

- `id` : ID de l'activité dans la base de données
- `code` : Code de l'activité (ex: "DF-01110")
- `name` : Nom complet de l'activité
- `confidence` : Niveau de confiance de 0 à 1 (0 = activité par défaut, >0 = suggérée par l'IA)
- `is_default_activity` : Booléen indiquant si c'est une activité par défaut

## Hiérarchie des activités

Le système gère une **hiérarchie récursive** d'activités :

```
Organisation A
  ├─ Activité Parent 1
  │   ├─ Activité Enfant 1.1
  │   │   └─ Activité Petit-enfant 1.1.1
  │   └─ Activité Enfant 1.2
  └─ Activité Parent 2
      └─ Activité Enfant 2.1
```

Si l'organisation A est associée à "Activité Parent 1", l'IA peut suggérer :
- Activité Parent 1
- Activité Enfant 1.1
- Activité Petit-enfant 1.1.1
- Activité Enfant 1.2

Mais **PAS** :
- Activité Parent 2 (sauf si associée directement à l'organisation)
- Activité Enfant 2.1

## Avantages

1. **Sécurité** : L'IA ne peut pas suggérer d'activités en dehors du périmètre de l'organisation
2. **Pertinence** : Les suggestions sont contextualisées à l'organisation
3. **Hiérarchie** : Support complet des sous-activités
4. **Transparence** : L'utilisateur est informé quand une activité par défaut est utilisée
5. **Flexibilité** : L'utilisateur peut toujours modifier l'activité après création du record

## Modifications de fichiers

### Contrôleur (`RecordDragDropController.php`)

Nouvelles méthodes :
- `getDefaultActivityId()` : Récupère l'activité par défaut de l'organisation
- `getOrganisationActivitiesWithDescendants()` : Récupère les activités avec descendants récursifs
- `getAllDescendantActivityIds()` : Récupère les IDs des descendants récursivement

Modifications :
- `processDragDrop()` : Ajout de la logique d'activité par défaut
- `persistRecord()` : Utilisation de `getDefaultActivityId()`
- `buildAiMessages()` : Limitation des activités proposées à l'IA

### Vue (`drag-drop.blade.php`)

Modifications :
- Affichage du code de l'activité
- Message d'avertissement pour les activités par défaut
- Affichage du pourcentage de confiance
- Bordure jaune pour les activités par défaut

## Cas d'usage

### Cas 1 : L'IA trouve l'activité

**Entrée** : Document de facture
**IA suggère** : "GESTION DES FACTURES" (code: FIN-0123, confiance: 92%)
**Résultat** : ✅ Activité trouvée et utilisée

### Cas 2 : L'IA ne trouve pas d'activité

**Entrée** : Document sans contexte clair
**IA suggère** : Rien ou activité non trouvée
**Résultat** : ⚠️ Activité par défaut de l'organisation (ex: première activité par code)

### Cas 3 : L'organisation n'a pas d'activité

**Entrée** : N'importe quel document
**Organisation** : Aucune activité associée
**Résultat** : ⚠️ Première activité disponible dans le système (fallback)

## Dépannage

### L'IA ne suggère jamais d'activité

**Cause possible** : Les activités de l'organisation ne sont pas bien configurées
**Solution** :
1. Vérifier que l'organisation a des activités associées
2. Vérifier que les codes/noms des activités sont clairs
3. Augmenter le contexte fourni à l'IA (plus de texte des fichiers)

### L'activité suggérée n'est pas pertinente

**Cause possible** : Mauvaise qualité du texte extrait ou modèle IA inapproprié
**Solution** :
1. Vérifier la qualité de l'extraction de texte
2. Changer le modèle IA (provider/modèle)
3. Améliorer le prompt système

### L'utilisateur voit toujours "Activité par défaut"

**Cause possible** : L'organisation n'a pas assez d'activités ou les codes ne sont pas standards
**Solution** :
1. Ajouter plus d'activités à l'organisation
2. Utiliser des codes et noms d'activités standards (ex: nomenclature d'archives)
3. Vérifier que les activités parentes sont bien associées à l'organisation

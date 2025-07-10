# Exemples d'utilisation de l'API Configuration Workflow

Ce document présente des exemples pratiques d'utilisation de l'API de gestion de la configuration JSON des templates de workflow.

## Configuration requise

- Authentification : Token Sanctum
- Headers : `Content-Type: application/json`, `Accept: application/json`
- Base URL : `/api/workflows/templates`

## 1. Récupération de la configuration

### Obtenir la configuration complète d'un template

```bash
curl -X GET "http://localhost/api/workflows/templates/1/configuration" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "template_id": 1,
    "template_name": "Traitement courrier entrant",
    "configuration": [
      {
        "id": "reception",
        "name": "Réception du courrier",
        "organisation_id": 1,
        "action_id": 1,
        "ordre": 1,
        "auto_assign": true,
        "timeout_hours": 24,
        "conditions": {
          "require_scan": true
        },
        "metadata": {
          "priority": "normal",
          "category": "reception"
        }
      },
      {
        "id": "classification",
        "name": "Classification",
        "organisation_id": 2,
        "action_id": 2,
        "ordre": 2,
        "auto_assign": false,
        "timeout_hours": 48,
        "conditions": {
          "require_keywords": true
        },
        "metadata": {
          "priority": "high"
        }
      }
    ]
  }
}
```

## 2. Mise à jour de la configuration complète

### Remplacer toute la configuration

```bash
curl -X PUT "http://localhost/api/workflows/templates/1/configuration" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "configuration": [
      {
        "id": "step_1",
        "name": "Réception",
        "organisation_id": 1,
        "action_id": 1,
        "ordre": 1,
        "auto_assign": true,
        "timeout_hours": 24
      },
      {
        "id": "step_2",
        "name": "Traitement",
        "organisation_id": 1,
        "action_id": 2,
        "ordre": 2,
        "auto_assign": false,
        "timeout_hours": 72
      }
    ]
  }'
```

## 3. Gestion des étapes individuelles

### Ajouter une nouvelle étape

```bash
curl -X POST "http://localhost/api/workflows/templates/1/configuration/steps" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "id": "validation",
    "name": "Validation juridique",
    "organisation_id": 3,
    "action_id": 5,
    "ordre": 3,
    "auto_assign": false,
    "timeout_hours": 120,
    "conditions": {
      "require_legal_review": true,
      "minimum_experience_years": 5
    },
    "metadata": {
      "department": "legal",
      "complexity": "high",
      "requires_signature": true
    }
  }'
```

### Modifier une étape existante

```bash
curl -X PUT "http://localhost/api/workflows/templates/1/configuration/steps/validation" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Validation juridique approfondie",
    "timeout_hours": 168,
    "conditions": {
      "require_legal_review": true,
      "minimum_experience_years": 7,
      "require_manager_approval": true
    }
  }'
```

### Supprimer une étape

```bash
curl -X DELETE "http://localhost/api/workflows/templates/1/configuration/steps/validation" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

## 4. Réorganisation des étapes

### Modifier l'ordre des étapes

```bash
curl -X PUT "http://localhost/api/workflows/templates/1/configuration/reorder" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "step_orders": [
      {"id": "classification", "ordre": 1},
      {"id": "reception", "ordre": 2},
      {"id": "validation", "ordre": 3}
    ]
  }'
```

## 5. Validation de la configuration

### Valider sans modifier

```bash
curl -X POST "http://localhost/api/workflows/templates/1/configuration/validate" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Réponse avec erreurs :**
```json
{
  "success": true,
  "data": {
    "template_id": 1,
    "is_valid": false,
    "errors": [
      "Des ordres d'étapes sont dupliqués",
      "L'étape 'step_invalid' n'a pas d'action définie"
    ],
    "warnings": [
      "Les ordres des étapes ne sont pas continus"
    ],
    "steps_count": 3
  }
}
```

## 6. Gestion des erreurs

### Erreur de validation

```json
{
  "success": false,
  "message": "Données de l'étape invalides",
  "errors": {
    "ordre": ["Le champ ordre est obligatoire"],
    "action_id": ["L'action sélectionnée n'existe pas"]
  }
}
```

### Erreur métier

```json
{
  "success": false,
  "message": "L'ordre de l'étape doit être unique"
}
```

### Étape non trouvée

```json
{
  "success": false,
  "message": "Étape non trouvée"
}
```

## 7. Exemples JavaScript (Frontend)

### Classe utilitaire pour l'API

```javascript
class WorkflowConfigAPI {
  constructor(baseUrl, token) {
    this.baseUrl = baseUrl;
    this.token = token;
  }

  async getConfiguration(templateId) {
    const response = await fetch(`${this.baseUrl}/workflows/templates/${templateId}/configuration`, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  }

  async addStep(templateId, stepData) {
    const response = await fetch(`${this.baseUrl}/workflows/templates/${templateId}/configuration/steps`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(stepData)
    });
    return response.json();
  }

  async updateStep(templateId, stepId, stepData) {
    const response = await fetch(`${this.baseUrl}/workflows/templates/${templateId}/configuration/steps/${stepId}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(stepData)
    });
    return response.json();
  }

  async deleteStep(templateId, stepId) {
    const response = await fetch(`${this.baseUrl}/workflows/templates/${templateId}/configuration/steps/${stepId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  }

  async reorderSteps(templateId, stepOrders) {
    const response = await fetch(`${this.baseUrl}/workflows/templates/${templateId}/configuration/reorder`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ step_orders: stepOrders })
    });
    return response.json();
  }

  async validateConfiguration(templateId) {
    const response = await fetch(`${this.baseUrl}/workflows/templates/${templateId}/configuration/validate`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  }
}
```

### Utilisation de la classe

```javascript
const api = new WorkflowConfigAPI('/api', 'your-token-here');

// Ajouter une étape
try {
  const result = await api.addStep(1, {
    id: 'review_step',
    name: 'Étape de révision',
    organisation_id: 2,
    action_id: 3,
    ordre: 2,
    auto_assign: true,
    timeout_hours: 48
  });
  
  if (result.success) {
    console.log('Étape ajoutée:', result.data.step);
  } else {
    console.error('Erreur:', result.message);
  }
} catch (error) {
  console.error('Erreur réseau:', error);
}

// Réorganiser les étapes
await api.reorderSteps(1, [
  { id: 'step_1', ordre: 2 },
  { id: 'step_2', ordre: 1 },
  { id: 'step_3', ordre: 3 }
]);

// Valider la configuration
const validation = await api.validateConfiguration(1);
if (!validation.data.is_valid) {
  console.warn('Erreurs de configuration:', validation.data.errors);
}
```

## 8. Cas d'usage avancés

### Configuration d'un workflow complexe

```json
{
  "configuration": [
    {
      "id": "intake",
      "name": "Réception initiale",
      "organisation_id": 1,
      "action_id": 1,
      "ordre": 1,
      "auto_assign": true,
      "timeout_hours": 2,
      "conditions": {
        "business_hours_only": true,
        "auto_categorize": true
      },
      "metadata": {
        "sla_priority": "immediate",
        "notification_channels": ["email", "sms"],
        "escalation_level": 1
      }
    },
    {
      "id": "triage",
      "name": "Tri et classification",
      "organisation_id": 1,
      "action_id": 2,
      "ordre": 2,
      "auto_assign": false,
      "timeout_hours": 24,
      "conditions": {
        "require_experience": "classification",
        "min_confidence_score": 0.8
      },
      "metadata": {
        "requires_training": "document_classification",
        "quality_check": true
      }
    },
    {
      "id": "assignment",
      "name": "Attribution responsable",
      "organisation_id": null,
      "action_id": 3,
      "ordre": 3,
      "auto_assign": true,
      "timeout_hours": 4,
      "conditions": {
        "load_balancing": true,
        "skill_matching": true,
        "availability_check": true
      },
      "metadata": {
        "algorithm": "weighted_round_robin",
        "fallback_assignment": "supervisor"
      }
    }
  ]
}
```

Cette API offre une flexibilité complète pour la gestion programmatique des configurations de workflow, avec une validation robuste et une gestion d'erreurs détaillée.

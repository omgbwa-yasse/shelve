# Analyse du Module Workflow

## Vue d'ensemble

Le module workflow est un système complet de gestion des flux de travail intégré à l'application Laravel Shelves. Il permet de créer, gérer et suivre des processus métier structurés, principalement pour la gestion des courriers et documents.

## Architecture du module

### 1. Structure MVC

#### Modèles principaux
- **WorkflowTemplate** : Modèles de workflows réutilisables
- **WorkflowInstance** : Instances actives de workflows
- **WorkflowStep** : Étapes des modèles de workflows
- **WorkflowStepInstance** : Étapes en cours d'exécution
- **WorkflowStepAssignment** : Assignations des étapes
- **Task** : Système de tâches intégré
- **TaskAssignment** : Assignations de tâches
- **TaskComment** : Commentaires sur les tâches

#### Contrôleurs
- **WorkflowTemplateController** : Gestion des modèles
- **WorkflowInstanceController** : Gestion des instances + Dashboard
- **WorkflowStepController** : Gestion des étapes
- **WorkflowStepInstanceController** : Gestion des étapes en cours
- **TaskController** : Gestion des tâches
- **TaskCommentController** : Commentaires
- **TaskAssignmentController** : Assignations
- **NotificationController** : Notifications système
- **SystemNotificationController** : Notifications système avancées

#### Vues (Blade Templates)
```
resources/views/workflow/
├── dashboard.blade.php              # Tableau de bord principal
├── templates/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── instances/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── steps/
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── step-instances/
│   └── show.blade.php
├── assigned-to-me.blade.php
├── assignments.blade.php
├── assign-modal.blade.php
├── audit-trail.blade.php
└── overdue.blade.php
```

### 2. Base de données

#### Tables principales
```sql
-- Templates et structure
workflow_templates
workflow_steps
workflow_step_assignments

-- Instances en cours
workflow_instances
workflow_step_instances

-- Système de tâches
task_categories
tasks
task_assignments
task_assignment_history
task_comments
task_dependencies

-- Notifications et suivi
mail_events
notifications
notification_subscriptions
organisation_delegations

-- Métriques
mail_metrics
email_templates
```

### 3. Énumérations (Enums)

#### WorkflowInstanceStatus
- `PENDING` : En attente
- `IN_PROGRESS` : En cours
- `COMPLETED` : Terminé
- `CANCELLED` : Annulé
- `ON_HOLD` : En pause

#### WorkflowStepType
- `manual` : Étape manuelle
- `automatic` : Étape automatique
- `approval` : Étape d'approbation
- `notification` : Étape de notification
- `conditional` : Étape conditionnelle

#### TaskStatus et TaskPriority
- Statuts : `todo`, `in_progress`, `review`, `done`, `cancelled`
- Priorités : `low`, `medium`, `high`, `urgent`

## Fonctionnalités principales

### 1. Gestion des templates de workflow
- Création de modèles réutilisables
- Configuration d'étapes séquentielles
- Assignation flexible (utilisateur/organisation)
- Conditions et règles métier
- Activation/désactivation des templates

### 2. Exécution des workflows
- Instanciation à partir de templates
- Suivi en temps réel des étapes
- Gestion des assignations dynamiques
- Historique complet des actions
- Métriques de performance

### 3. Système de tâches intégré
- Création de tâches liées aux workflows
- Assignation multiple (utilisateur + organisation)
- Suivi du temps et progression
- Commentaires et pièces jointes
- Hiérarchie (tâches parentes/enfants)
- Dépendances entre tâches

### 4. Notifications et suivi
- Notifications en temps réel
- Abonnements personnalisables
- Tableaux de bord personnalisés
- Alertes d'échéances
- Audit trail complet

### 5. Integration avec le système de courriers
- Workflows déclenchés par courriers
- Liaison mail ↔ workflow
- Traçabilité complète
- Métriques de traitement

## Routes et URLs

### Structure des routes
```php
Route::prefix('workflows')->name('workflows.')->group(function () {
    // Templates
    Route::resource('templates', WorkflowTemplateController::class);
    Route::post('templates/{template}/toggle-active', ...);
    Route::post('templates/{template}/duplicate', ...);
    
    // Étapes
    Route::resource('templates.steps', WorkflowStepController::class)->shallow();
    Route::post('steps/{step}/assignments', ...);
    
    // Instances
    Route::resource('instances', WorkflowInstanceController::class);
    Route::post('instances/{instance}/start', ...);
    Route::post('instances/{instance}/cancel', ...);
    
    // Étapes d'instances
    Route::resource('step-instances', WorkflowStepInstanceController::class);
    Route::post('step-instances/{stepInstance}/complete', ...);
    
    // Dashboard
    Route::get('dashboard', [WorkflowInstanceController::class, 'dashboard']);
    
    // Tâches
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::get('/my', [TaskController::class, 'myTasks']);
        // ... autres routes tâches
    });
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // ... routes notifications
    });
});
```

## Système d'autorisation

### Politiques (Policies)
- **WorkflowTemplatePolicy** : Gestion des templates
- **WorkflowInstancePolicy** : Gestion des instances
- **WorkflowStepPolicy** : Gestion des étapes
- **WorkflowStepInstancePolicy** : Gestion des étapes en cours

### Permissions principales
- `workflow_template_view` / `workflow_template_create` / `workflow_template_update`
- `workflow_instance_view` / `workflow_instance_create` / `workflow_instance_manage`
- `workflow_dashboard` : Accès au tableau de bord
- `task_view` / `task_create` / `task_update`

## Intégrations

### 1. Avec le système de courriers (Mails)
- Chaque workflow peut être lié à un courrier
- Déclenchement automatique de workflows
- Suivi du traitement des courriers
- Métriques de performance

### 2. Avec le système d'organisations
- Assignation par organisation
- Délégations hiérarchiques
- Gestion des permissions organisationnelles

### 3. Avec le système de notifications
- Notifications temps réel
- Emails automatiques
- Alertes d'échéances

## Tableaux de bord et métriques

### Dashboard principal
- Statistiques globales (workflows actifs, complétés, tâches)
- Mes workflows assignés
- Mes tâches en cours
- Activités récentes
- Workflows par template

### Métriques suivies
- Temps de traitement
- Temps de réponse
- Nombre de workflows par type
- Performance par utilisateur/organisation
- Respect des échéances

## Points techniques notables

### 1. Flexibilité d'assignation
- Support assignation utilisateur ET organisation simultanément
- Délégations temporaires
- Réassignation dynamique
- Historique complet des changements

### 2. Architecture modulaire
- Séparation claire entre templates et instances
- Réutilisabilité des modèles
- Configuration flexible via JSON

### 3. Performance
- Index optimisés sur les requêtes fréquentes
- Pagination intelligente
- Chargement des relations optimisé

### 4. Extensibilité
- Champs personnalisables (custom_fields)
- Configuration JSON flexible
- Hooks pour extensions futures

## Améliorations possibles

1. **Interface utilisateur**
   - Builder graphique de workflows
   - Glisser-déposer pour réorganiser les étapes
   - Visualisation temps réel des flux

2. **Fonctionnalités avancées**
   - Workflows conditionnels complexes
   - Escalade automatique
   - SLA (Service Level Agreements)
   - Intégration calendrier

3. **Reporting**
   - Tableaux de bord avancés
   - Export de métriques
   - Analyse prédictive

4. **API et intégrations**
   - API REST complète
   - Webhooks
   - Intégrations externes (Slack, Teams, etc.)

## Structure JSON pour la création de workflows

### Format de configuration JSON

Selon les spécifications du système, la configuration des workflows est stockée en JSON avec la structure suivante :

#### 1. Structure du template de workflow
```json
{
  "id": "workflow_template_id",
  "name": "Nom du workflow",
  "description": "Description du processus",
  "category": "mail_processing",
  "organisation_id": 1,
  "created_by": 1,
  "is_active": true,
  "configuration": {
    "auto_start": true,
    "escalation_enabled": true,
    "notification_settings": {
      "on_start": true,
      "on_complete": true,
      "on_overdue": true
    }
  }
}
```

#### 2. Structure des étapes de workflow
```json
{
  "steps": [
    {
      "id": "step_1",
      "name": "Réception et validation",
      "description": "Première validation du courrier",
      "ordre": 1,
      "step_type": "manual",
      "organisation_id": 1,
      "action_id": 3,
      "estimated_duration": 60,
      "is_required": true,
      "can_be_skipped": false,
      "configuration": {
        "auto_assign": false,
        "requires_approval": false,
        "timeout_hours": 24
      },
      "conditions": {
        "mail_priority": ["high", "urgent"],
        "mail_typology": ["CORR", "DEM"]
      },
      "assignments": [
        {
          "assignee_type": "organisation",
          "assignee_organisation_id": 1,
          "assignee_user_id": null,
          "role": "assignee",
          "assignment_rules": {
            "auto_assign_to_least_busy": true,
            "skill_requirements": ["mail_processing"]
          }
        }
      ]
    },
    {
      "id": "step_2", 
      "name": "Traitement et action",
      "description": "Traitement selon l'action requise",
      "ordre": 2,
      "step_type": "conditional",
      "organisation_id": 1,
      "action_id": null,
      "estimated_duration": 180,
      "is_required": true,
      "can_be_skipped": false,
      "configuration": {
        "conditional_logic": {
          "if": {
            "mail.action_id": 1
          },
          "then": {
            "assignee_type": "user",
            "assignee_user_id": "supervisor"
          },
          "else": {
            "assignee_type": "organisation",
            "assignee_organisation_id": 1
          }
        }
      },
      "conditions": {
        "previous_step_completed": true
      }
    }
  ]
}
```

#### 3. Structure complète d'un workflow JSON
```json
{
  "workflow": {
    "template": {
      "id": "courrier_entrant_standard",
      "name": "Traitement courrier entrant standard",
      "description": "Processus de traitement des courriers entrants standards",
      "category": "mail_processing",
      "organisation_id": 1,
      "created_by": 1,
      "is_active": true,
      "configuration": {
        "auto_start_conditions": {
          "mail_type": "incoming",
          "typology_codes": ["CORR", "DEM", "INFO"]
        },
        "escalation": {
          "enabled": true,
          "timeout_hours": 48,
          "escalate_to_role": "supervisor"
        },
        "notifications": {
          "email_enabled": true,
          "sms_enabled": false,
          "in_app_enabled": true
        }
      }
    },
    "steps": [
      {
        "id": "reception",
        "name": "Réception et enregistrement",
        "description": "Enregistrement initial du courrier dans le système",
        "ordre": 1,
        "step_type": "automatic",
        "organisation_id": 1,
        "action_id": null,
        "estimated_duration": 5,
        "is_required": true,
        "can_be_skipped": false,
        "configuration": {
          "auto_complete": true,
          "generate_code": true,
          "scan_required": false
        },
        "conditions": {},
        "assignments": []
      },
      {
        "id": "triage",
        "name": "Tri et classification",
        "description": "Classification et assignation selon la typologie",
        "ordre": 2,
        "step_type": "manual",
        "organisation_id": 1,
        "action_id": 1,
        "estimated_duration": 30,
        "is_required": true,
        "can_be_skipped": false,
        "configuration": {
          "requires_supervisor_approval": false,
          "allow_reassignment": true
        },
        "conditions": {
          "mail_status": "received",
          "has_attachments": "any"
        },
        "assignments": [
          {
            "assignee_type": "organisation",
            "assignee_organisation_id": 1,
            "assignee_user_id": null,
            "role": "assignee",
            "assignment_rules": {
              "department": "secretariat",
              "skill_level": "basic"
            }
          }
        ]
      },
      {
        "id": "traitement",
        "name": "Traitement principal",
        "description": "Traitement selon l'action définie",
        "ordre": 3,
        "step_type": "manual",
        "organisation_id": 1,
        "action_id": "dynamic",
        "estimated_duration": 240,
        "is_required": true,
        "can_be_skipped": false,
        "configuration": {
          "action_based_assignment": true,
          "requires_approval": {
            "if_action": [2, 3, 4],
            "approval_level": "supervisor"
          }
        },
        "conditions": {
          "previous_step_status": "completed",
          "action_defined": true
        },
        "assignments": [
          {
            "assignee_type": "both",
            "assignee_organisation_id": 1,
            "assignee_user_id": "dynamic",
            "role": "assignee",
            "assignment_rules": {
              "based_on_action": {
                "action_1": {"user_role": "clerk"},
                "action_2": {"user_role": "analyst"},
                "action_3": {"user_role": "manager"},
                "action_4": {"user_role": "director"}
              }
            }
          }
        ]
      },
      {
        "id": "validation",
        "name": "Validation et transmission",
        "description": "Validation finale et transmission si nécessaire",
        "ordre": 4,
        "step_type": "approval",
        "organisation_id": 1,
        "action_id": null,
        "estimated_duration": 60,
        "is_required": false,
        "can_be_skipped": true,
        "configuration": {
          "approval_required": true,
          "can_reject": true,
          "rejection_returns_to_step": 3
        },
        "conditions": {
          "action_requires_approval": true,
          "mail_priority": ["high", "urgent"]
        },
        "assignments": [
          {
            "assignee_type": "user",
            "assignee_organisation_id": 1,
            "assignee_user_id": "supervisor",
            "role": "reviewer",
            "assignment_rules": {
              "hierarchy_level": "n+1",
              "approval_authority": true
            }
          }
        ]
      },
      {
        "id": "archivage",
        "name": "Archivage",
        "description": "Archivage final du dossier traité",
        "ordre": 5,
        "step_type": "automatic",
        "organisation_id": 1,
        "action_id": null,
        "estimated_duration": 5,
        "is_required": true,
        "can_be_skipped": false,
        "configuration": {
          "auto_complete": true,
          "archive_attachments": true,
          "retention_period": "5_years"
        },
        "conditions": {
          "all_previous_completed": true,
          "approval_status": "approved"
        },
        "assignments": []
      }
    ],
    "metadata": {
      "version": "1.0",
      "created_at": "2025-07-10T10:00:00Z",
      "last_modified": "2025-07-10T10:00:00Z",
      "author": "System Administrator",
      "tags": ["courrier", "standard", "automatique"],
      "estimated_total_duration": 520
    }
  }
}
```

#### 4. Propriétés définies pour la structure JSON

**Propriétés principales :**
- `id` : Identifiant unique du workflow/étape
- `name` : Nom descriptif 
- `organisation_id` : Organisation impliquée dans l'étape
- `action_id` : Action associée (référence table mail_actions)
- `ordre` : Ordre d'exécution des étapes
- `step_type` : Type d'étape (manual, automatic, approval, notification, conditional)
- `estimated_duration` : Durée estimée en minutes
- `is_required` : Étape obligatoire
- `can_be_skipped` : Peut être ignorée
- `assignee_type` : Type d'assignation (user, organisation, both)
- `assignee_user_id` : Utilisateur assigné
- `assignee_organisation_id` : Organisation assignée
- `conditions` : Conditions d'exécution
- `configuration` : Configuration avancée flexible
- `assignment_rules` : Règles d'assignation automatique

**Propriétés métadonnées :**
- `version` : Version du template
- `created_at/last_modified` : Horodatage
- `author` : Créateur
- `tags` : Étiquettes de classification
- `estimated_total_duration` : Durée totale estimée

Cette structure JSON permet une définition complète et flexible des workflows, avec une intégration native au système de courriers existant via les `action_id` et les références aux organisations et utilisateurs.

### 5. Clarifications sur les organisations

**Important :** Dans la structure JSON, `organisation_id` fait référence à l'organisation **impliquée dans l'étape spécifique**, pas nécessairement à l'organisation propriétaire du workflow complet. Cela permet :

- Une étape peut être assignée à une organisation différente de celle qui a créé le workflow
- Gestion de workflows inter-organisationnels 
- Flexibilité dans l'assignation des responsabilités par étape
- Support des processus de validation hiérarchique entre organisations

**Exemples d'usage :**
- Étape 1 : `organisation_id: 1` (Service courrier)
- Étape 2 : `organisation_id: 2` (Service juridique) 
- Étape 3 : `organisation_id: 1` (Retour service courrier)

Cette approche permet une granularité fine dans la gestion des responsabilités organisationnelles à chaque étape du processus.

## 6. API de gestion de la configuration JSON

Le contrôleur `WorkflowTemplateController` a été enrichi avec des méthodes dédiées à la gestion CRUD de la configuration JSON :

### Endpoints API disponibles

#### Configuration complète
- **GET** `/api/workflows/templates/{template}/configuration`
  - Récupère la configuration JSON complète d'un template
  - Retourne : template_id, template_name, configuration

- **PUT** `/api/workflows/templates/{template}/configuration`
  - Met à jour la configuration JSON complète
  - Validation : structure, unicité des IDs/ordres, existence des actions
  - Retourne : configuration mise à jour

- **POST** `/api/workflows/templates/{template}/configuration/validate`
  - Valide la configuration sans la modifier
  - Vérifie : unicité IDs/ordres, continuité, actions existantes
  - Retourne : is_valid, errors, warnings, steps_count

#### Gestion des étapes individuelles
- **POST** `/api/workflows/templates/{template}/configuration/steps`
  - Ajoute une nouvelle étape à la configuration
  - Validation : structure, unicité ID/ordre
  - Retourne : étape créée + configuration complète

- **PUT** `/api/workflows/templates/{template}/configuration/steps/{stepId}`
  - Met à jour une étape spécifique par son ID
  - Validation : unicité ordre (exclut l'étape courante)
  - Retourne : étape modifiée + configuration complète

- **DELETE** `/api/workflows/templates/{template}/configuration/steps/{stepId}`
  - Supprime une étape de la configuration
  - Réindexe automatiquement le tableau
  - Retourne : configuration mise à jour

#### Réorganisation
- **PUT** `/api/workflows/templates/{template}/configuration/reorder`
  - Réorganise les ordres des étapes existantes
  - Paramètres : step_orders (array avec id + ordre)
  - Trie automatiquement par ordre après mise à jour
  - Retourne : configuration réorganisée

### Fonctionnalités de validation

Toutes les méthodes incluent des validations robustes :

1. **Validation de structure** : Vérification des champs requis et types
2. **Validation métier** : 
   - Unicité des IDs d'étapes
   - Unicité des ordres
   - Existence des actions (`action_id`)
   - Existence des organisations (`organisation_id`)
3. **Validation de cohérence** : 
   - Continuité des ordres (warning)
   - Intégrité référentielle

### Format des réponses API

```json
{
  "success": true,
  "message": "Message descriptif",
  "data": {
    "template_id": 1,
    "configuration": [...],
    "step": {...}  // pour les opérations sur étapes individuelles
  },
  "errors": {...}  // en cas d'erreur de validation
}
```

### Exemples d'utilisation

#### Ajouter une étape
```javascript
POST /api/workflows/templates/1/configuration/steps
{
  "id": "step_review",
  "name": "Révision du document",
  "organisation_id": 2,
  "action_id": 3,
  "ordre": 2,
  "auto_assign": true,
  "timeout_hours": 48,
  "conditions": {
    "require_signature": true
  },
  "metadata": {
    "priority": "high"
  }
}
```

#### Réorganiser les étapes
```javascript
PUT /api/workflows/templates/1/configuration/reorder
{
  "step_orders": [
    {"id": "step_1", "ordre": 2},
    {"id": "step_2", "ordre": 1},
    {"id": "step_3", "ordre": 3}
  ]
}
```

Cette API offre une interface complète et sécurisée pour manipuler la configuration JSON des workflows, avec des validations métier appropriées et une gestion d'erreurs détaillée.

## Conclusion

Le module workflow de Shelves est un système robuste et bien architecturé qui offre une base solide pour la gestion des processus métier. Il combine flexibilité, performance et facilité d'utilisation, avec une intégration native au système de gestion documentaire existant. La structure JSON proposée permet une configuration avancée tout en maintenant la compatibilité avec l'architecture existante.

La nouvelle API de gestion de la configuration JSON ajoute une couche de flexibilité supplémentaire, permettant une manipulation fine et sécurisée des workflows via des interfaces programmatiques modernes.

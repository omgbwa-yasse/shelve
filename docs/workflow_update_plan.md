# Plan de mise à jour du module Workflow

Basé sur le fichier `Workflow_new_.md`, voici un plan détaillé pour mettre à jour le module de workflow.

## 1. Modèles (Models) - COMPLÉTÉ

### WorkflowTemplate
- ✅ Simplification du modèle
- ✅ Suppression des champs: category, configuration

### WorkflowStep
- ✅ Simplification du modèle
- ✅ Renommage du champ estimated_duration en estimated_hours
- ✅ Suppression des champs: configuration, can_be_skipped, conditions

### WorkflowStepAssignment
- ✅ Simplification du modèle
- ✅ Suppression des champs: assignee_type, assignee_role_id, assignment_rules, allow_reassignment

### WorkflowInstance
- ✅ Simplification du modèle
- ✅ Suppression des champs: context_data, notes

### WorkflowStepInstance
- ✅ Simplification du modèle
- ✅ Suppression des champs: assignment_type, input_data, output_data, failure_reason, assignment_notes, due_date

### Task
- ✅ Simplification du modèle
- ✅ Suppression des champs: start_date, actual_hours, progress_percentage, assignment_type, parent_task_id, tags, custom_fields, completion_notes, assignment_notes

### TaskComment
- ✅ Simplification du modèle
- ✅ Suppression des champs: type, metadata

## 2. Contrôleurs (Controllers) - EN COURS

### WorkflowTemplateController
- ✅ Simplification de la méthode store
- ❌ Mise à jour de la méthode update
- ❌ Simplification des méthodes de validation

### WorkflowStepController
- ❌ Simplification des méthodes store et update
- ❌ Adaptation pour la simplification des étapes

### WorkflowInstanceController
- ❌ Simplification des méthodes store et update
- ❌ Adaptation pour refléter le nouveau schéma

### WorkflowStepInstanceController
- ❌ Simplification des méthodes store et update
- ❌ Adaptation pour la gestion simplifiée des étapes

### TaskController
- ❌ Simplification des méthodes store et update
- ❌ Suppression de la gestion des fonctionnalités complexes

### TaskCommentController
- ❌ Simplification des méthodes store et update
- ❌ Suppression des types de commentaires

## 3. Vues (Views) - À FAIRE

### Templates de Workflow
- ❌ Mise à jour des formulaires create/edit
- ❌ Simplification de la vue show
- ❌ Adaptation de l'index

### Étapes de Workflow
- ❌ Mise à jour des formulaires create/edit
- ❌ Simplification de la vue show
- ❌ Adaptation de l'index

### Instances de Workflow
- ❌ Mise à jour des formulaires create/edit
- ❌ Simplification de la vue show
- ❌ Adaptation de l'index

### Tâches
- ❌ Mise à jour des formulaires create/edit
- ❌ Simplification de la vue show
- ❌ Adaptation de l'index et my_tasks

### Commentaires de Tâches
- ❌ Simplification de l'affichage des commentaires
- ❌ Mise à jour du formulaire d'ajout de commentaire

## 4. Migration - À FAIRE

- ❌ Créer une migration pour transformer la structure actuelle vers la structure simplifiée
- ❌ Assurer la conservation des données importantes
- ❌ Plan de rollback en cas de problème

## 5. Tests - À FAIRE

- ❌ Mettre à jour les tests unitaires
- ❌ Mettre à jour les tests fonctionnels
- ❌ Vérifier le bon fonctionnement du module complet

## 6. Documentation - À FAIRE

- ❌ Mettre à jour la documentation pour refléter les changements
- ❌ Fournir des exemples d'utilisation du nouveau système simplifié

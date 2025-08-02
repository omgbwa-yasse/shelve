<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepAssignment;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Workflow de Validation de Courrier Entrant Important
        $workflow1 = WorkflowTemplate::create([
            'name' => 'Traitement d\'un Courrier Entrant Important',
            'description' => 'Processus de traitement et validation des courriers entrants sensibles ou stratégiques',
            'category' => 'mail',
            'is_active' => true,
            'created_by' => 1, // Utilisateur Super Admin
            'configuration' => json_encode([
                'trigger' => 'Un nouveau courrier est marqué comme "sensible" ou "stratégique"',
                'object_type' => 'mail',
                'estimated_duration' => 3, // En jours
                'roles' => ['Agent du Courrier', 'Chef de Service', 'Agent spécifique']
            ])
        ]);

        // Étapes du workflow 1
        $steps1 = [
            [
                'workflow_template_id' => $workflow1->id,
                'name' => 'Numérisation & Qualification',
                'description' => 'Numérisation du courrier et qualification initiale',
                'order_index' => 1,
                'step_type' => 'manual',
                'estimated_duration' => 60, // En minutes (1 jour = 8 heures = 480 minutes)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Agent du Courrier'])
            ],
            [
                'workflow_template_id' => $workflow1->id,
                'name' => 'Analyse & Attribution',
                'description' => 'Analyse du contenu et attribution à un agent',
                'order_index' => 2,
                'step_type' => 'manual',
                'estimated_duration' => 60, // En minutes
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Chef de Service'])
            ],
            [
                'workflow_template_id' => $workflow1->id,
                'name' => 'Préparation de la Réponse',
                'description' => 'Rédaction d\'une réponse appropriée',
                'order_index' => 3,
                'step_type' => 'manual',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Agent spécifique'])
            ],
            [
                'workflow_template_id' => $workflow1->id,
                'name' => 'Validation de la Réponse',
                'description' => 'Vérification et validation de la réponse',
                'order_index' => 4,
                'step_type' => 'approval',
                'estimated_duration' => 60, // En minutes
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Chef de Service'])
            ],
            [
                'workflow_template_id' => $workflow1->id,
                'name' => 'Envoi & Archivage',
                'description' => 'Envoi de la réponse et archivage du courrier',
                'order_index' => 5,
                'step_type' => 'manual',
                'estimated_duration' => 60, // En minutes
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Agent du Courrier'])
            ]
        ];

        foreach ($steps1 as $step) {
            WorkflowStep::create($step);
        }

        // 2. Workflow de Validation d'un Nouveau Document Officiel
        $workflow2 = WorkflowTemplate::create([
            'name' => 'Validation d\'un Nouveau Document Officiel',
            'description' => 'Processus de création, validation et publication d\'un document officiel',
            'category' => 'document',
            'is_active' => true,
            'created_by' => 1, // Utilisateur Super Admin
            'configuration' => json_encode([
                'trigger' => 'Un utilisateur soumet un nouveau document pour publication',
                'object_type' => 'document',
                'estimated_duration' => 5, // En jours
                'roles' => ['Rédacteur', 'Service Juridique', 'Manager', 'Directeur', 'Communication Interne']
            ])
        ]);

        // Étapes du workflow 2
        $steps2 = [
            [
                'workflow_template_id' => $workflow2->id,
                'name' => 'Rédaction & Soumission',
                'description' => 'Rédaction initiale du document et soumission',
                'order_index' => 1,
                'step_type' => 'manual',
                'estimated_duration' => 240, // En minutes (4 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Rédacteur'])
            ],
            [
                'workflow_template_id' => $workflow2->id,
                'name' => 'Relecture Juridique',
                'description' => 'Vérification juridique du contenu',
                'order_index' => 2,
                'step_type' => 'manual',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Service Juridique'])
            ],
            [
                'workflow_template_id' => $workflow2->id,
                'name' => 'Validation Managériale',
                'description' => 'Validation par le manager',
                'order_index' => 3,
                'step_type' => 'approval',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Manager'])
            ],
            [
                'workflow_template_id' => $workflow2->id,
                'name' => 'Approbation Finale',
                'description' => 'Approbation finale par le directeur',
                'order_index' => 4,
                'step_type' => 'approval',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Directeur'])
            ],
            [
                'workflow_template_id' => $workflow2->id,
                'name' => 'Publication & Diffusion',
                'description' => 'Publication et diffusion du document',
                'order_index' => 5,
                'step_type' => 'manual',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Communication Interne'])
            ]
        ];

        foreach ($steps2 as $step) {
            WorkflowStep::create($step);
        }

        // 3. Workflow de Demande d'Élimination d'Archives
        $workflow3 = WorkflowTemplate::create([
            'name' => 'Processus de Demande d\'Élimination d\'Archives',
            'description' => 'Processus d\'élimination des archives ayant atteint leur durée de conservation',
            'category' => 'archive',
            'is_active' => true,
            'created_by' => 1, // Utilisateur Super Admin
            'configuration' => json_encode([
                'trigger' => 'Un archiviste initie une demande de destruction pour des documents ayant atteint leur durée de conservation',
                'object_type' => 'record',
                'estimated_duration' => 7, // En jours
                'roles' => ['Archiviste', 'Chef du Service Archives', 'Service Juridique', 'Directeur Général']
            ])
        ]);

        // Étapes du workflow 3
        $steps3 = [
            [
                'workflow_template_id' => $workflow3->id,
                'name' => 'Création du Bordereau d\'Élimination',
                'description' => 'Préparation du bordereau listant les documents à éliminer',
                'order_index' => 1,
                'step_type' => 'manual',
                'estimated_duration' => 240, // En minutes (4 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Archiviste'])
            ],
            [
                'workflow_template_id' => $workflow3->id,
                'name' => 'Validation du Responsable des Archives',
                'description' => 'Vérification et validation par le chef du service',
                'order_index' => 2,
                'step_type' => 'approval',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Chef du Service Archives'])
            ],
            [
                'workflow_template_id' => $workflow3->id,
                'name' => 'Vérification de Conformité',
                'description' => 'Vérification de la conformité légale de l\'élimination',
                'order_index' => 3,
                'step_type' => 'manual',
                'estimated_duration' => 240, // En minutes (4 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Service Juridique'])
            ],
            [
                'workflow_template_id' => $workflow3->id,
                'name' => 'Autorisation de Destruction',
                'description' => 'Autorisation finale pour procéder à la destruction',
                'order_index' => 4,
                'step_type' => 'approval',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Directeur Général'])
            ],
            [
                'workflow_template_id' => $workflow3->id,
                'name' => 'Exécution et Preuve de Destruction',
                'description' => 'Destruction effective et documentation du processus',
                'order_index' => 5,
                'step_type' => 'manual',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Archiviste'])
            ]
        ];

        foreach ($steps3 as $step) {
            WorkflowStep::create($step);
        }

        // 4. Workflow de Gestion d'une Demande de Document Public
        $workflow4 = WorkflowTemplate::create([
            'name' => 'Gestion d\'une Demande de Document Public',
            'description' => 'Processus de traitement des demandes de documents par le public',
            'category' => 'public',
            'is_active' => true,
            'created_by' => 1, // Utilisateur Super Admin
            'configuration' => json_encode([
                'trigger' => 'Une nouvelle demande est créée depuis le portail public',
                'object_type' => 'public_request',
                'estimated_duration' => 5, // En jours
                'roles' => ['Responsable du Portail Public', 'Archiviste', 'Chef du Service Archives']
            ])
        ]);

        // Étapes du workflow 4
        $steps4 = [
            [
                'workflow_template_id' => $workflow4->id,
                'name' => 'Accusé de Réception & Analyse',
                'description' => 'Confirmation de réception et analyse initiale',
                'order_index' => 1,
                'step_type' => 'manual',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Responsable du Portail Public'])
            ],
            [
                'workflow_template_id' => $workflow4->id,
                'name' => 'Recherche du Document',
                'description' => 'Localisation et préparation du document demandé',
                'order_index' => 2,
                'step_type' => 'manual',
                'estimated_duration' => 180, // En minutes (3 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Archiviste'])
            ],
            [
                'workflow_template_id' => $workflow4->id,
                'name' => 'Validation de la Communicabilité',
                'description' => 'Vérification du droit de communication',
                'order_index' => 3,
                'step_type' => 'approval',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Chef du Service Archives'])
            ],
            [
                'workflow_template_id' => $workflow4->id,
                'name' => 'Préparation de la Réponse',
                'description' => 'Préparation du document et de la réponse',
                'order_index' => 4,
                'step_type' => 'manual',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Archiviste'])
            ],
            [
                'workflow_template_id' => $workflow4->id,
                'name' => 'Envoi au Demandeur',
                'description' => 'Envoi de la réponse et du document au demandeur',
                'order_index' => 5,
                'step_type' => 'manual',
                'estimated_duration' => 30, // En minutes (30 minutes)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Responsable du Portail Public'])
            ]
        ];

        foreach ($steps4 as $step) {
            WorkflowStep::create($step);
        }

        // 5. Workflow de Demande d'Achat
        $workflow5 = WorkflowTemplate::create([
            'name' => 'Demande d\'Achat',
            'description' => 'Processus de traitement des demandes d\'achat supérieures à un certain montant',
            'category' => 'purchase',
            'is_active' => true,
            'created_by' => 1, // Utilisateur Super Admin
            'configuration' => json_encode([
                'trigger' => 'Un employé remplit un formulaire de demande d\'achat',
                'object_type' => 'purchase_request',
                'estimated_duration' => 5, // En jours
                'roles' => ['Employé', 'Manager', 'Bureau du Budget', 'Directeur Financier', 'Bureau des Achats']
            ])
        ]);

        // Étapes du workflow 5
        $steps5 = [
            [
                'workflow_template_id' => $workflow5->id,
                'name' => 'Soumission de la Demande',
                'description' => 'Création et soumission de la demande d\'achat',
                'order_index' => 1,
                'step_type' => 'manual',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Employé'])
            ],
            [
                'workflow_template_id' => $workflow5->id,
                'name' => 'Approbation du Manager',
                'description' => 'Validation par le manager du demandeur',
                'order_index' => 2,
                'step_type' => 'approval',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Manager'])
            ],
            [
                'workflow_template_id' => $workflow5->id,
                'name' => 'Vérification Budgétaire',
                'description' => 'Vérification de la disponibilité budgétaire',
                'order_index' => 3,
                'step_type' => 'manual',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Bureau du Budget'])
            ],
            [
                'workflow_template_id' => $workflow5->id,
                'name' => 'Approbation Finale',
                'description' => 'Validation finale par le directeur financier',
                'order_index' => 4,
                'step_type' => 'approval',
                'estimated_duration' => 60, // En minutes (1 heure)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Directeur Financier'])
            ],
            [
                'workflow_template_id' => $workflow5->id,
                'name' => 'Passation de la Commande',
                'description' => 'Traitement de la commande et achat',
                'order_index' => 5,
                'step_type' => 'manual',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Bureau des Achats'])
            ]
        ];

        foreach ($steps5 as $step) {
            WorkflowStep::create($step);
        }

        // 6. Workflow d'Intégration d'un Nouvel Employé
        $workflow6 = WorkflowTemplate::create([
            'name' => 'Intégration d\'un Nouvel Employé (Onboarding)',
            'description' => 'Processus d\'accueil et d\'intégration des nouveaux employés',
            'category' => 'onboarding',
            'is_active' => true,
            'created_by' => 1, // Utilisateur Super Admin
            'configuration' => json_encode([
                'trigger' => 'Le service RH enregistre une nouvelle embauche',
                'object_type' => 'employee',
                'estimated_duration' => 5, // En jours
                'roles' => ['Service IT', 'Services Généraux', 'RH', 'Manager']
            ])
        ]);

        // Étapes du workflow 6
        $steps6 = [
            [
                'workflow_template_id' => $workflow6->id,
                'name' => 'Création des Accès Informatiques',
                'description' => 'Préparation des comptes et accès informatiques',
                'order_index' => 1,
                'step_type' => 'manual',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Service IT'])
            ],
            [
                'workflow_template_id' => $workflow6->id,
                'name' => 'Préparation du Matériel',
                'description' => 'Préparation du poste de travail et du matériel',
                'order_index' => 2,
                'step_type' => 'manual',
                'estimated_duration' => 240, // En minutes (4 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Services Généraux'])
            ],
            [
                'workflow_template_id' => $workflow6->id,
                'name' => 'Création du Dossier Administratif',
                'description' => 'Finalisation du dossier administratif',
                'order_index' => 3,
                'step_type' => 'manual',
                'estimated_duration' => 120, // En minutes (2 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'RH'])
            ],
            [
                'workflow_template_id' => $workflow6->id,
                'name' => 'Planification de l\'Accueil',
                'description' => 'Organisation de l\'accueil et du parcours d\'intégration',
                'order_index' => 4,
                'step_type' => 'manual',
                'estimated_duration' => 180, // En minutes (3 heures)
                'is_required' => true,
                'can_be_skipped' => false,
                'configuration' => json_encode(['role' => 'Manager'])
            ]
        ];

        foreach ($steps6 as $step) {
            WorkflowStep::create($step);
        }

        // Pour chaque étape, on pourrait aussi créer des assignations par défaut
        // Exemple : (non implémenté ici pour simplifier)
        // WorkflowStepAssignment::create([
        //     'workflow_step_id' => $stepId,
        //     'assignee_type' => 'role',
        //     'assignment_rules' => json_encode(['role' => 'Chef de Service']),
        //     'allow_reassignment' => true
        // ]);
    }
}

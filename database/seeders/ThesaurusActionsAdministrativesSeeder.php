<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insérer le schéma de thésaurus basé sur le référentiel des Archives de France
        $schemeId = DB::table('thesaurus_schemes')->insertGetId([
            'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives',
            'identifier' => 'SIAF-ACTIONS-2011',
            'title' => 'Thésaurus pour la description et l\'indexation des archives locales anciennes, modernes et contemporaines - Liste d\'autorité Actions',
            'description' => 'Thésaurus officiel du Service Interministériel des Archives de France pour l\'indexation des actions administratives dans les archives publiques',
            'language' => 'fr-fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Concepts principaux avec leurs relations EM/EP/TA
        $concepts = [
            // Groupe A
            'ABOLITION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/abolition',
                'notation' => 'A001',
                'prefLabel' => 'ABOLITION',
                'definition' => 'Action de supprimer, d\'annuler un acte, une loi, une institution',
                'narrower' => ['abrogation']
            ],

            'ABSTENTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/abstention',
                'notation' => 'A002',
                'prefLabel' => 'ABSTENTION',
                'definition' => 'Action de s\'abstenir de participer à un vote ou une décision'
            ],

            'ACQUISITION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/acquisition',
                'notation' => 'A003',
                'prefLabel' => 'ACQUISITION',
                'definition' => 'Action d\'acquérir un bien, un droit ou une propriété'
            ],

            'ADHESION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/adhesion',
                'notation' => 'A004',
                'prefLabel' => 'ADHÉSION',
                'definition' => 'Action d\'adhérer à une organisation ou un accord'
            ],

            'ADJUDICATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/adjudication',
                'notation' => 'A005',
                'prefLabel' => 'ADJUDICATION',
                'definition' => 'Procédure d\'attribution d\'un marché public au plus offrant'
            ],

            'AGREMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/agrement',
                'notation' => 'A006',
                'prefLabel' => 'AGRÉMENT',
                'definition' => 'Autorisation administrative accordée après vérification de conditions',
                'altLabels' => ['approbation', 'habilitation', 'homologation', 'labellisation']
            ],

            'AMENAGEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/amenagement',
                'notation' => 'A007',
                'prefLabel' => 'AMÉNAGEMENT',
                'definition' => 'Action de modifier, d\'adapter un espace ou une réglementation',
                'altLabels' => ['extension', 'rénovation'],
                'related' => ['CONSTRUCTION']
            ],

            'APPEL_OFFRES' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/appel-offres',
                'notation' => 'A008',
                'prefLabel' => 'APPEL D\'OFFRES',
                'definition' => 'Procédure de mise en concurrence pour l\'attribution d\'un marché public'
            ],

            'AUTORISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/autorisation',
                'notation' => 'A009',
                'prefLabel' => 'AUTORISATION',
                'definition' => 'Permission accordée par l\'administration pour exercer une activité',
                'altLabels' => ['dérogation', 'dispense', 'interdiction']
            ],

            // Groupe C
            'CAUTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/caution',
                'notation' => 'C001',
                'prefLabel' => 'CAUTION',
                'definition' => 'Garantie financière exigée dans certaines procédures administratives'
            ],

            'CESSION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/cession',
                'notation' => 'C002',
                'prefLabel' => 'CESSION',
                'definition' => 'Action de céder, de transférer un bien ou un droit'
            ],

            'CLASSEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/classement',
                'notation' => 'C003',
                'prefLabel' => 'CLASSEMENT',
                'definition' => 'Action de classer selon des critères réglementaires (installations, établissements)'
            ],

            'COMMISSIONNEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/commissionnement',
                'notation' => 'C004',
                'prefLabel' => 'COMMISSIONNEMENT',
                'definition' => 'Action de nommer quelqu\'un dans une fonction par commission'
            ],

            'COMMUNICATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/communication',
                'notation' => 'C005',
                'prefLabel' => 'COMMUNICATION',
                'definition' => 'Action de transmettre des informations au public ou aux administrés',
                'altLabels' => ['information']
            ],

            'CONCESSION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/concession',
                'notation' => 'C006',
                'prefLabel' => 'CONCESSION',
                'definition' => 'Convention par laquelle une collectivité confie à un tiers la gestion d\'un service'
            ],

            'CONCILIATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/conciliation',
                'notation' => 'C007',
                'prefLabel' => 'CONCILIATION',
                'definition' => 'Procédure amiable de règlement des différends',
                'altLabels' => ['arbitrage', 'médiation']
            ],

            'CONSTRUCTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/construction',
                'notation' => 'C008',
                'prefLabel' => 'CONSTRUCTION',
                'definition' => 'Action de construire un bâtiment ou une infrastructure publique',
                'related' => ['AMENAGEMENT']
            ],

            'CONTROLE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle',
                'notation' => 'C009',
                'prefLabel' => 'CONTRÔLE',
                'definition' => 'Action de vérifier la conformité à des règles ou normes',
                'narrower' => ['CONTROLE_BUDGETAIRE', 'CONTROLE_GESTION', 'CONTROLE_LEGALITE', 'CONTROLE_SECURITE', 'CONTROLE_FISCAL', 'CONTROLE_SANITAIRE']
            ],

            'COOPERATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/cooperation',
                'notation' => 'C010',
                'prefLabel' => 'COOPÉRATION',
                'definition' => 'Action de collaborer entre collectivités ou organismes'
            ],

            'COORDINATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/coordination',
                'notation' => 'C011',
                'prefLabel' => 'COORDINATION',
                'definition' => 'Action d\'organiser la cohérence entre différentes actions ou services'
            ],

            // Groupe D-E
            'DELEGATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/delegation',
                'notation' => 'D001',
                'prefLabel' => 'DÉLÉGATION',
                'definition' => 'Délégation de service public confiée à un tiers'
            ],

            'DENOMINATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/denomination',
                'notation' => 'D002',
                'prefLabel' => 'DÉNOMINATION',
                'definition' => 'Action de donner un nom à une rue, place, bâtiment public'
            ],

            'DESIGNATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/designation',
                'notation' => 'D003',
                'prefLabel' => 'DÉSIGNATION',
                'definition' => 'Action de désigner les membres d\'un organisme consultatif ou délibérant'
            ],

            'DESTRUCTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/destruction',
                'notation' => 'D004',
                'prefLabel' => 'DESTRUCTION',
                'definition' => 'Action de détruire un bien ou document selon une procédure administrative'
            ],

            'DISSOLUTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/dissolution',
                'notation' => 'D005',
                'prefLabel' => 'DISSOLUTION',
                'definition' => 'Action de dissoudre une association ou un organisme'
            ],

            'ENQUETE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/enquete',
                'notation' => 'E001',
                'prefLabel' => 'ENQUÊTE',
                'definition' => 'Procédure d\'investigation administrative',
                'narrower' => ['ENQUETE_PUBLIQUE']
            ],

            'ENTRETIEN' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/entretien',
                'notation' => 'E002',
                'prefLabel' => 'ENTRETIEN',
                'definition' => 'Action de maintenir en bon état un équipement ou infrastructure'
            ],

            'EQUIPEMENT_MATERIEL' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/equipement-materiel',
                'notation' => 'E003',
                'prefLabel' => 'ÉQUIPEMENT MATÉRIEL',
                'definition' => 'Action d\'équiper en matériel, armement ou habillement',
                'altLabels' => ['armement', 'habillement'],
                'related' => ['INFORMATISATION']
            ],

            'EVACUATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/evacuation',
                'notation' => 'E004',
                'prefLabel' => 'ÉVACUATION',
                'definition' => 'Action administrative d\'évacuation pour raisons de sécurité'
            ],

            'EVALUATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/evaluation',
                'notation' => 'E005',
                'prefLabel' => 'ÉVALUATION',
                'definition' => 'Action d\'évaluer la performance, la qualité ou la valeur',
                'altLabels' => ['audit']
            ],

            // Groupe F-G
            'FINANCEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/financement',
                'notation' => 'F001',
                'prefLabel' => 'FINANCEMENT',
                'definition' => 'Action de financer un projet ou une activité',
                'altLabels' => ['dotation', 'subvention']
            ],

            'FONCTIONNEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/fonctionnement',
                'notation' => 'F002',
                'prefLabel' => 'FONCTIONNEMENT',
                'definition' => 'Organisation du fonctionnement d\'un service ou organisme'
            ],

            'GARANTIE_EMPRUNT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/garantie-emprunt',
                'notation' => 'G001',
                'prefLabel' => 'GARANTIE D\'EMPRUNT',
                'definition' => 'Garantie accordée par une collectivité pour un emprunt'
            ],

            'GESTION_COMPTABLE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/gestion-comptable',
                'notation' => 'G002',
                'prefLabel' => 'GESTION COMPTABLE',
                'definition' => 'Ensemble des opérations de gestion financière et comptable',
                'altLabels' => ['apurement', 'engagement', 'liquidation comptable', 'mandatement', 'ordonnancement', 'paiement'],
                'related' => ['RECOUVREMENT']
            ],

            'GESTION_DU_PERSONNEL' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/gestion-personnel',
                'notation' => 'G003',
                'prefLabel' => 'GESTION DU PERSONNEL',
                'definition' => 'Ensemble des actions relatives à la gestion des ressources humaines',
                'altLabels' => ['affectation', 'avancement', 'destitution', 'détachement', 'intégration', 'licenciement', 'mise à disposition', 'mise à la retraite', 'mise en disponibilité', 'nomination', 'notation', 'promotion professionnelle', 'suspension', 'titularisation'],
                'related' => ['RECRUTEMENT']
            ],

            // Groupe I-L
            'IMMATRICULATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/immatriculation',
                'notation' => 'I001',
                'prefLabel' => 'IMMATRICULATION',
                'definition' => 'Action d\'inscrire sur un registre officiel (véhicules, commerce, etc.)',
                'altLabels' => ['affiliation', 'francisation']
            ],

            'INDEMNISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/indemnisation',
                'notation' => 'I002',
                'prefLabel' => 'INDEMNISATION',
                'definition' => 'Action d\'indemniser un préjudice ou des frais'
            ],

            'INFORMATISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/informatisation',
                'notation' => 'I003',
                'prefLabel' => 'INFORMATISATION',
                'definition' => 'Action de mettre en place des systèmes informatiques',
                'related' => ['EQUIPEMENT_MATERIEL', 'NUMERISATION']
            ],

            'INSPECTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/inspection',
                'notation' => 'I004',
                'prefLabel' => 'INSPECTION',
                'definition' => 'Action d\'inspecter, de contrôler par un corps d\'inspection'
            ],

            'LOCATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/location',
                'notation' => 'L001',
                'prefLabel' => 'LOCATION',
                'definition' => 'Action de louer ou prendre en location un bien'
            ],

            // Groupe N-O
            'NUMERISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/numerisation',
                'notation' => 'N001',
                'prefLabel' => 'NUMÉRISATION',
                'definition' => 'Action de numériser des documents ou processus'
            ],

            'ORGANISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/organisation',
                'notation' => 'O001',
                'prefLabel' => 'ORGANISATION',
                'definition' => 'Action d\'organiser, de restructurer une administration',
                'altLabels' => ['modernisation', 'réforme administrative']
            ],

            // Groupe P
            'PLACEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/placement',
                'notation' => 'P001',
                'prefLabel' => 'PLACEMENT',
                'definition' => 'Action de placer (emploi, aide sociale, personnes vulnérables)'
            ],

            'PREPARATION_BUDGETAIRE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/preparation-budgetaire',
                'notation' => 'P002',
                'prefLabel' => 'PRÉPARATION BUDGÉTAIRE',
                'definition' => 'Action de préparer le budget annuel'
            ],

            'PREVENTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/prevention',
                'notation' => 'P003',
                'prefLabel' => 'PRÉVENTION',
                'definition' => 'Actions de prévention des risques ou de la délinquance'
            ],

            'PROGRAMMATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/programmation',
                'notation' => 'P004',
                'prefLabel' => 'PROGRAMMATION',
                'definition' => 'Action de programmer des activités ou investissements'
            ],

            'PROMOTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/promotion',
                'notation' => 'P005',
                'prefLabel' => 'PROMOTION',
                'definition' => 'Action de promouvoir une activité, un territoire, un produit'
            ],

            'PROTECTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/protection',
                'notation' => 'P006',
                'prefLabel' => 'PROTECTION',
                'definition' => 'Protection du patrimoine (sites, monuments, archives, secteurs sauvegardés)'
            ],

            // Groupe R
            'RECENSEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/recensement',
                'notation' => 'R001',
                'prefLabel' => 'RECENSEMENT',
                'definition' => 'Opération de comptage et dénombrement administratif'
            ],

            'RECOURS_HIERARCHIQUE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/recours-hierarchique',
                'notation' => 'R002',
                'prefLabel' => 'RECOURS HIÉRARCHIQUE',
                'definition' => 'Procédure de contestation dans un contexte administratif'
            ],

            'RECOUVREMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/recouvrement',
                'notation' => 'R003',
                'prefLabel' => 'RECOUVREMENT',
                'definition' => 'Action de recouvrer des créances, impôts ou taxes',
                'related' => ['GESTION_COMPTABLE']
            ],

            'RECRUTEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/recrutement',
                'notation' => 'R004',
                'prefLabel' => 'RECRUTEMENT',
                'definition' => 'Action de recruter du personnel',
                'related' => ['GESTION_DU_PERSONNEL']
            ],

            'REGLEMENTATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/reglementation',
                'notation' => 'R005',
                'prefLabel' => 'RÉGLEMENTATION',
                'definition' => 'Action d\'élaborer ou modifier la réglementation',
                'altLabels' => ['abrogation']
            ],

            'REQUISITION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/requisition',
                'notation' => 'R006',
                'prefLabel' => 'RÉQUISITION',
                'definition' => 'Action de réquisitionner des biens ou personnes'
            ],

            'RESTAURATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/restauration',
                'notation' => 'R007',
                'prefLabel' => 'RESTAURATION',
                'definition' => 'Restauration du patrimoine mobilier et immobilier'
            ],

            // Groupe T
            'TARIFICATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/tarification',
                'notation' => 'T001',
                'prefLabel' => 'TARIFICATION',
                'definition' => 'Action de fixer les tarifs des services publics'
            ],

            'TUTELLE_ADMINISTRATIVE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/tutelle-administrative',
                'notation' => 'T002',
                'prefLabel' => 'TUTELLE ADMINISTRATIVE',
                'definition' => 'Exercice d\'une tutelle administrative sur un organisme'
            ],

            'TUTELLE_FINANCIERE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/tutelle-financiere',
                'notation' => 'T003',
                'prefLabel' => 'TUTELLE FINANCIÈRE',
                'definition' => 'Exercice d\'une tutelle financière sur un organisme'
            ]
        ];

        $conceptIds = [];

        // Insérer tous les concepts principaux
        foreach ($concepts as $key => $concept) {
            $conceptIds[$key] = DB::table('thesaurus_concepts')->insertGetId([
                'scheme_id' => $schemeId,
                'uri' => $concept['uri'],
                'notation' => $concept['notation'],
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ajouter le label préféré
            DB::table('thesaurus_labels')->insert([
                'concept_id' => $conceptIds[$key],
                'type' => 'prefLabel',
                'literal_form' => $concept['prefLabel'],
                'language' => 'fr-fr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ajouter la définition
            if (isset($concept['definition'])) {
                DB::table('thesaurus_concept_notes')->insert([
                    'concept_id' => $conceptIds[$key],
                    'type' => 'definition',
                    'note' => $concept['definition'],
                    'language' => 'fr-fr',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Ajouter les labels alternatifs (EP - Employé pour)
            if (isset($concept['altLabels'])) {
                foreach ($concept['altLabels'] as $altLabel) {
                    DB::table('thesaurus_labels')->insert([
                        'concept_id' => $conceptIds[$key],
                        'type' => 'altLabel',
                        'literal_form' => $altLabel,
                        'language' => 'fr-fr',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Créer les concepts spécialisés (sous-concepts)
        $subConcepts = [
            'abrogation' => [
                'parent' => 'REGLEMENTATION',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/abrogation',
                'notation' => 'R005.01',
                'prefLabel' => 'abrogation',
                'definition' => 'Action d\'abroger un texte réglementaire'
            ],
            'ENQUETE_PUBLIQUE' => [
                'parent' => 'ENQUETE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/enquete-publique',
                'notation' => 'E001.01',
                'prefLabel' => 'ENQUÊTE PUBLIQUE',
                'definition' => 'Procédure de consultation du public sur un projet'
            ],
            'CONTROLE_BUDGETAIRE' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-budgetaire',
                'notation' => 'C009.01',
                'prefLabel' => 'CONTRÔLE BUDGÉTAIRE',
                'definition' => 'Contrôle de l\'exécution budgétaire'
            ],
            'CONTROLE_GESTION' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-gestion',
                'notation' => 'C009.02',
                'prefLabel' => 'CONTRÔLE DE GESTION',
                'definition' => 'Contrôle de la gestion administrative et financière'
            ],
            'CONTROLE_LEGALITE' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-legalite',
                'notation' => 'C009.03',
                'prefLabel' => 'CONTRÔLE DE LÉGALITÉ',
                'definition' => 'Contrôle de la conformité des actes administratifs'
            ],
            'CONTROLE_SECURITE' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-securite',
                'notation' => 'C009.04',
                'prefLabel' => 'CONTRÔLE DE SÉCURITÉ',
                'definition' => 'Contrôle des mesures de sécurité'
            ],
            'CONTROLE_FISCAL' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-fiscal',
                'notation' => 'C009.05',
                'prefLabel' => 'CONTRÔLE FISCAL',
                'definition' => 'Contrôle de la situation fiscale'
            ],
            'CONTROLE_SANITAIRE' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-sanitaire',
                'notation' => 'C009.06',
                'prefLabel' => 'CONTRÔLE SANITAIRE',
                'definition' => 'Contrôle des conditions sanitaires'
            ]
        ];

        // Insérer les sous-concepts et créer les relations hiérarchiques
        foreach ($subConcepts as $key => $subConcept) {
            $subConceptId = DB::table('thesaurus_concepts')->insertGetId([
                'scheme_id' => $schemeId,
                'uri' => $subConcept['uri'],
                'notation' => $subConcept['notation'],
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Labels et définitions
            DB::table('thesaurus_labels')->insert([
                'concept_id' => $subConceptId,
                'type' => 'prefLabel',
                'literal_form' => $subConcept['prefLabel'],
                'language' => 'fr-fr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('thesaurus_concept_notes')->insert([
                'concept_id' => $subConceptId,
                'type' => 'definition',
                'note' => $subConcept['definition'],
                'language' => 'fr-fr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Relations hiérarchiques avec le concept parent
            $parentId = $conceptIds[$subConcept['parent']];

            // Relation broader (le sous-concept a un terme plus large)
            DB::table('thesaurus_concept_relations')->insert([
                'concept_id' => $subConceptId,
                'related_concept_id' => $parentId,
                'relation_type' => 'broader',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Relation narrower (le concept parent a un terme plus spécifique)
            DB::table('thesaurus_concept_relations')->insert([
                'concept_id' => $parentId,
                'related_concept_id' => $subConceptId,
                'relation_type' => 'narrower',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Créer les relations associées (TA - Terme associé) basées sur le thésaurus
        $relatedConcepts = [
            ['AMENAGEMENT', 'CONSTRUCTION'],
            ['GESTION_COMPTABLE', 'RECOUVREMENT'],
            ['GESTION_DU_PERSONNEL', 'RECRUTEMENT'],
            ['EQUIPEMENT_MATERIEL', 'INFORMATISATION']
        ];

        foreach ($relatedConcepts as $relation) {
            if (isset($conceptIds[$relation[0]]) && isset($conceptIds[$relation[1]])) {
                // Relation bidirectionnelle
                DB::table('thesaurus_concept_relations')->insert([
                    'concept_id' => $conceptIds[$relation[0]],
                    'related_concept_id' => $conceptIds[$relation[1]],
                    'relation_type' => 'related',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('thesaurus_concept_relations')->insert([
                    'concept_id' => $conceptIds[$relation[1]],
                    'related_concept_id' => $conceptIds[$relation[0]],
                    'relation_type' => 'related',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Ajouter l'organisation responsable
        $orgId = DB::table('thesaurus_organizations')->insertGetId([
            'name' => 'Service Interministériel des Archives de France',
            'homepage' => 'https://archives.gouv.fr',
            'email' => 'contact@archives.gouv.fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ajouter les namespaces SKOS
        DB::table('thesaurus_namespaces')->insert([
            [
                'prefix' => 'skos',
                'namespace_uri' => 'http://www.w3.org/2004/02/skos/core#',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'siaf',
                'namespace_uri' => 'https://archives.gouv.fr/thesaurus/',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Ajouter des notes d'application spécifiques du thésaurus
        $scopeNotes = [
            'CLASSEMENT' => 'S\'emploie pour les installations classées, les bâtiments recevant du public, les hôtels et les campings... Ne s\'emploie pas pour le patrimoine (voir PROTECTION).',
            'CONCESSION' => 'Voir note sur DELEGATION.',
            'CONSTRUCTION' => 'Le terme s\'emploie quand l\'État, une collectivité territoriale ou un organisme public sont maîtres d\'ouvrage (construction d\'un bâtiment administratif ou de toute infrastructure publique).',
            'DELEGATION' => 'S\'emploie pour désigner exclusivement les délégations de service public. Le terme CONCESSION sera choisi pour tout autre cas de convention (ex: concession de travaux, concession d\'aménagement).',
            'DENOMINATION' => 'S\'emploie pour la dénomination des noms de rues, de places, de bâtiments...',
            'DESIGNATION' => 'Le terme s\'emploie pour les dossiers traitant de la composition et de la représentation des membres d\'un organisme consultatif, d\'une association, d\'un organisme délibérant.',
            'EVACUATION' => 'Il s\'agit bien là d\'une action résultant d\'une décision administrative.',
            'IMMATRICULATION' => 'S\'emploie pour toute sorte d\'immatriculation : immatriculation de voiture, inscription au registre du commerce...',
            'ORGANISATION' => 'Terme à associer avec le mot objet STRUCTURE ADMINISTRATIVE pour tout ce qui touche à l\'évaluation, la modernisation et aux réformes des structures administratives.',
            'PLACEMENT' => 'S\'emploie notamment à propos de l\'EMPLOI, de l\'AIDE SOCIALE A L\'ENFANCE et des TRAVAILLEURS(S) HANDICAPE(S), des PERSONNES AGEES et des MALADES MENTAUX.',
            'PROTECTION' => 'S\'emploie pour les sites naturels, les sites archéologiques, les monuments historiques, les antiquités et objets d\'art, les archives privées, les secteurs sauvegardés, les ZPPAUP.',
            'RECENSEMENT' => 'S\'emploie pour toute opération permanente, régulière, occasionnelle, de comptage, quel que soit l\'objet du dénombrement, exception faite des recensements de population (descripteur autorisé par le thesaurus).',
            'RECOURS_HIERARCHIQUE' => 'S\'emploie quand la contestation fait l\'objet d\'un règlement ou d\'une tentative de règlement dans un contexte purement administratif.',
            'RESTAURATION' => 'S\'emploie pour la restauration du patrimoine mobilier et immobilier.'
        ];

        foreach ($scopeNotes as $conceptKey => $note) {
            if (isset($conceptIds[$conceptKey])) {
                DB::table('thesaurus_concept_notes')->insert([
                    'concept_id' => $conceptIds[$conceptKey],
                    'type' => 'scopeNote',
                    'note' => $note,
                    'language' => 'fr-fr',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer le thésaurus des Archives de France
        $scheme = DB::table('thesaurus_schemes')
            ->where('uri', 'https://archives.gouv.fr/thesaurus/actions-administratives')
            ->first();

        if ($scheme) {
            // Les relations CASCADE s'occuperont de supprimer les concepts et leurs dépendances
            DB::table('thesaurus_schemes')->where('id', $scheme->id)->delete();
        }

        // Supprimer l'organisation ajoutée
        DB::table('thesaurus_organizations')
            ->where('name', 'Service Interministériel des Archives de France')
            ->delete();

        // Supprimer les namespaces ajoutés
        DB::table('thesaurus_namespaces')
            ->whereIn('prefix', ['skos', 'siaf'])
            ->delete();
    }
};

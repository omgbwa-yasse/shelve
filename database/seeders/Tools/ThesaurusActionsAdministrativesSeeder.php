<?php

namespace Database\Seeders\Tools;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThesaurusActionsAdministrativesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Encapsulation de toutes les opÃ©rations dans une transaction
        DB::beginTransaction();

        try {
            // VÃ©rifier si le thÃ©saurus existe dÃ©jÃ 
            $existingScheme = DB::table('thesaurus_schemes')
                ->where('uri', 'https://archives.gouv.fr/thesaurus/actions-administratives')
                ->first();

            if ($existingScheme) {
                $this->command->info('Le thÃ©saurus des actions administratives existe dÃ©jÃ . Seeder ignorÃ©.');
                return;
            }

            $schemeId = $this->createThesaurusScheme();
            $conceptIds = $this->createConcepts($schemeId);
            $this->createSubConcepts($schemeId, $conceptIds);
            $this->createRelatedConcepts($conceptIds);
            $this->createOrganizationAndNamespaces();
            $this->addScopeNotes($conceptIds);

            DB::commit();
            $this->command->info('ThÃ©saurus des actions administratives importÃ© avec succÃ¨s.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Erreur lors de l\'import du thÃ©saurus: ' . $e->getMessage());
        }
    }

    /**
     * Create the thesaurus scheme
     *
     * @return int The ID of the created scheme
     */
    private function createThesaurusScheme(): int
    {
        return DB::table('thesaurus_schemes')->insertGetId([
            'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives',
            'identifier' => 'SIAF-ACTIONS-2011',
            'title' => 'ThÃ©saurus pour la description et l\'indexation des archives locales anciennes, modernes et contemporaines - Liste d\'autoritÃ© Actions',
            'description' => 'ThÃ©saurus officiel du Service InterministÃ©riel des Archives de France pour l\'indexation des actions administratives dans les archives publiques',
            'language' => 'fr-fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }

    /**
     * Create all main concepts
     * @param int $schemeId
     * @return array IDs of created concepts
     */
    private function createConcepts(int $schemeId): array
    {
        $concepts = $this->getConceptsDefinition();
        return $this->insertConcepts($schemeId, $concepts);
    }

    /**
     * Get the definition of all concepts
     *
     * @return array Array of concept definitions
     */
    private function getConceptsDefinition(): array
    {
        // Concepts principaux avec leurs relations EM/EP/TA
        return [
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
                'definition' => 'Action de s\'abstenir de participer Ã  un vote ou une dÃ©cision'
            ],

            'ACQUISITION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/acquisition',
                'notation' => 'A003',
                'prefLabel' => 'ACQUISITION',
                'definition' => 'Action d\'acquÃ©rir un bien, un droit ou une propriÃ©tÃ©'
            ],

            'ADHESION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/adhesion',
                'notation' => 'A004',
                'prefLabel' => 'ADHÃ‰SION',
                'definition' => 'Action d\'adhÃ©rer Ã  une organisation ou un accord'
            ],

            'ADJUDICATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/adjudication',
                'notation' => 'A005',
                'prefLabel' => 'ADJUDICATION',
                'definition' => 'ProcÃ©dure d\'attribution d\'un marchÃ© public au plus offrant'
            ],

            'AGREMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/agrement',
                'notation' => 'A006',
                'prefLabel' => 'AGRÃ‰MENT',
                'definition' => 'Autorisation administrative accordÃ©e aprÃ¨s vÃ©rification de conditions',
                'altLabels' => ['approbation', 'habilitation', 'homologation', 'labellisation']
            ],

            'AMENAGEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/amenagement',
                'notation' => 'A007',
                'prefLabel' => 'AMÃ‰NAGEMENT',
                'definition' => 'Action de modifier, d\'adapter un espace ou une rÃ©glementation',
                'altLabels' => ['extension', 'rÃ©novation'],
                'related' => ['CONSTRUCTION']
            ],

            'APPEL_OFFRES' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/appel-offres',
                'notation' => 'A008',
                'prefLabel' => 'APPEL D\'OFFRES',
                'definition' => 'ProcÃ©dure de mise en concurrence pour l\'attribution d\'un marchÃ© public'
            ],

            'AUTORISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/autorisation',
                'notation' => 'A009',
                'prefLabel' => 'AUTORISATION',
                'definition' => 'Permission accordÃ©e par l\'administration pour exercer une activitÃ©',
                'altLabels' => ['dÃ©rogation', 'dispense', 'interdiction']
            ],

            // Groupe C
            'CAUTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/caution',
                'notation' => 'C001',
                'prefLabel' => 'CAUTION',
                'definition' => 'Garantie financiÃ¨re exigÃ©e dans certaines procÃ©dures administratives'
            ],

            'CESSION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/cession',
                'notation' => 'C002',
                'prefLabel' => 'CESSION',
                'definition' => 'Action de cÃ©der, de transfÃ©rer un bien ou un droit'
            ],

            'CLASSEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/classement',
                'notation' => 'C003',
                'prefLabel' => 'CLASSEMENT',
                'definition' => 'Action de classer selon des critÃ¨res rÃ©glementaires (installations, Ã©tablissements)'
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
                'definition' => 'Action de transmettre des informations au public ou aux administrÃ©s',
                'altLabels' => ['information']
            ],

            'CONCESSION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/concession',
                'notation' => 'C006',
                'prefLabel' => 'CONCESSION',
                'definition' => 'Convention par laquelle une collectivitÃ© confie Ã  un tiers la gestion d\'un service'
            ],

            'CONCILIATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/conciliation',
                'notation' => 'C007',
                'prefLabel' => 'CONCILIATION',
                'definition' => 'ProcÃ©dure amiable de rÃ¨glement des diffÃ©rends',
                'altLabels' => ['arbitrage', 'mÃ©diation']
            ],

            'CONSTRUCTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/construction',
                'notation' => 'C008',
                'prefLabel' => 'CONSTRUCTION',
                'definition' => 'Action de construire un bÃ¢timent ou une infrastructure publique',
                'related' => ['AMENAGEMENT']
            ],

            'CONTROLE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle',
                'notation' => 'C009',
                'prefLabel' => 'CONTRÃ”LE',
                'definition' => 'Action de vÃ©rifier la conformitÃ© Ã  des rÃ¨gles ou normes',
                'narrower' => ['CONTROLE_BUDGETAIRE', 'CONTROLE_GESTION', 'CONTROLE_LEGALITE', 'CONTROLE_SECURITE', 'CONTROLE_FISCAL', 'CONTROLE_SANITAIRE']
            ],

            'COOPERATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/cooperation',
                'notation' => 'C010',
                'prefLabel' => 'COOPÃ‰RATION',
                'definition' => 'Action de collaborer entre collectivitÃ©s ou organismes'
            ],

            'COORDINATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/coordination',
                'notation' => 'C011',
                'prefLabel' => 'COORDINATION',
                'definition' => 'Action d\'organiser la cohÃ©rence entre diffÃ©rentes actions ou services'
            ],

            // Groupe D-E
            'DELEGATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/delegation',
                'notation' => 'D001',
                'prefLabel' => 'DÃ‰LÃ‰GATION',
                'definition' => 'DÃ©lÃ©gation de service public confiÃ©e Ã  un tiers'
            ],

            'DENOMINATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/denomination',
                'notation' => 'D002',
                'prefLabel' => 'DÃ‰NOMINATION',
                'definition' => 'Action de donner un nom Ã  une rue, place, bÃ¢timent public'
            ],

            'DESIGNATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/designation',
                'notation' => 'D003',
                'prefLabel' => 'DÃ‰SIGNATION',
                'definition' => 'Action de dÃ©signer les membres d\'un organisme consultatif ou dÃ©libÃ©rant'
            ],

            'DESTRUCTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/destruction',
                'notation' => 'D004',
                'prefLabel' => 'DESTRUCTION',
                'definition' => 'Action de dÃ©truire un bien ou document selon une procÃ©dure administrative'
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
                'prefLabel' => 'ENQUÃŠTE',
                'definition' => 'ProcÃ©dure d\'investigation administrative',
                'narrower' => ['ENQUETE_PUBLIQUE']
            ],

            'ENTRETIEN' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/entretien',
                'notation' => 'E002',
                'prefLabel' => 'ENTRETIEN',
                'definition' => 'Action de maintenir en bon Ã©tat un Ã©quipement ou infrastructure'
            ],

            'EQUIPEMENT_MATERIEL' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/equipement-materiel',
                'notation' => 'E003',
                'prefLabel' => 'Ã‰QUIPEMENT MATÃ‰RIEL',
                'definition' => 'Action d\'Ã©quiper en matÃ©riel, armement ou habillement',
                'altLabels' => ['armement', 'habillement'],
                'related' => ['INFORMATISATION']
            ],

            'EVACUATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/evacuation',
                'notation' => 'E004',
                'prefLabel' => 'Ã‰VACUATION',
                'definition' => 'Action administrative d\'Ã©vacuation pour raisons de sÃ©curitÃ©'
            ],

            'EVALUATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/evaluation',
                'notation' => 'E005',
                'prefLabel' => 'Ã‰VALUATION',
                'definition' => 'Action d\'Ã©valuer la performance, la qualitÃ© ou la valeur',
                'altLabels' => ['audit']
            ],

            // Groupe F-G
            'FINANCEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/financement',
                'notation' => 'F001',
                'prefLabel' => 'FINANCEMENT',
                'definition' => 'Action de financer un projet ou une activitÃ©',
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
                'definition' => 'Garantie accordÃ©e par une collectivitÃ© pour un emprunt'
            ],

            'GESTION_COMPTABLE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/gestion-comptable',
                'notation' => 'G002',
                'prefLabel' => 'GESTION COMPTABLE',
                'definition' => 'Ensemble des opÃ©rations de gestion financiÃ¨re et comptable',
                'altLabels' => ['apurement', 'engagement', 'liquidation comptable', 'mandatement', 'ordonnancement', 'paiement'],
                'related' => ['RECOUVREMENT']
            ],

            'GESTION_DU_PERSONNEL' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/gestion-personnel',
                'notation' => 'G003',
                'prefLabel' => 'GESTION DU PERSONNEL',
                'definition' => 'Ensemble des actions relatives Ã  la gestion des ressources humaines',
                'altLabels' => ['affectation', 'avancement', 'destitution', 'dÃ©tachement', 'intÃ©gration', 'licenciement', 'mise Ã  disposition', 'mise Ã  la retraite', 'mise en disponibilitÃ©', 'nomination', 'notation', 'promotion professionnelle', 'suspension', 'titularisation'],
                'related' => ['RECRUTEMENT']
            ],

            // Groupe I-L
            'IMMATRICULATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/immatriculation',
                'notation' => 'I001',
                'prefLabel' => 'IMMATRICULATION',
                'definition' => 'Action d\'inscrire sur un registre officiel (vÃ©hicules, commerce, etc.)',
                'altLabels' => ['affiliation', 'francisation']
            ],

            'INDEMNISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/indemnisation',
                'notation' => 'I002',
                'prefLabel' => 'INDEMNISATION',
                'definition' => 'Action d\'indemniser un prÃ©judice ou des frais'
            ],

            'INFORMATISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/informatisation',
                'notation' => 'I003',
                'prefLabel' => 'INFORMATISATION',
                'definition' => 'Action de mettre en place des systÃ¨mes informatiques',
                'related' => ['EQUIPEMENT_MATERIEL', 'NUMERISATION']
            ],

            'INSPECTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/inspection',
                'notation' => 'I004',
                'prefLabel' => 'INSPECTION',
                'definition' => 'Action d\'inspecter, de contrÃ´ler par un corps d\'inspection'
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
                'prefLabel' => 'NUMÃ‰RISATION',
                'definition' => 'Action de numÃ©riser des documents ou processus'
            ],

            'ORGANISATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/organisation',
                'notation' => 'O001',
                'prefLabel' => 'ORGANISATION',
                'definition' => 'Action d\'organiser, de restructurer une administration',
                'altLabels' => ['modernisation', 'rÃ©forme administrative']
            ],

            // Groupe P
            'PLACEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/placement',
                'notation' => 'P001',
                'prefLabel' => 'PLACEMENT',
                'definition' => 'Action de placer (emploi, aide sociale, personnes vulnÃ©rables)'
            ],

            'PREPARATION_BUDGETAIRE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/preparation-budgetaire',
                'notation' => 'P002',
                'prefLabel' => 'PRÃ‰PARATION BUDGÃ‰TAIRE',
                'definition' => 'Action de prÃ©parer le budget annuel'
            ],

            'PREVENTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/prevention',
                'notation' => 'P003',
                'prefLabel' => 'PRÃ‰VENTION',
                'definition' => 'Actions de prÃ©vention des risques ou de la dÃ©linquance'
            ],

            'PROGRAMMATION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/programmation',
                'notation' => 'P004',
                'prefLabel' => 'PROGRAMMATION',
                'definition' => 'Action de programmer des activitÃ©s ou investissements'
            ],

            'PROMOTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/promotion',
                'notation' => 'P005',
                'prefLabel' => 'PROMOTION',
                'definition' => 'Action de promouvoir une activitÃ©, un territoire, un produit'
            ],

            'PROTECTION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/protection',
                'notation' => 'P006',
                'prefLabel' => 'PROTECTION',
                'definition' => 'Protection du patrimoine (sites, monuments, archives, secteurs sauvegardÃ©s)'
            ],

            // Groupe R
            'RECENSEMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/recensement',
                'notation' => 'R001',
                'prefLabel' => 'RECENSEMENT',
                'definition' => 'OpÃ©ration de comptage et dÃ©nombrement administratif'
            ],

            'RECOURS_HIERARCHIQUE' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/recours-hierarchique',
                'notation' => 'R002',
                'prefLabel' => 'RECOURS HIÃ‰RARCHIQUE',
                'definition' => 'ProcÃ©dure de contestation dans un contexte administratif'
            ],

            'RECOUVREMENT' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/recouvrement',
                'notation' => 'R003',
                'prefLabel' => 'RECOUVREMENT',
                'definition' => 'Action de recouvrer des crÃ©ances, impÃ´ts ou taxes',
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
                'prefLabel' => 'RÃ‰GLEMENTATION',
                'definition' => 'Action d\'Ã©laborer ou modifier la rÃ©glementation',
                'altLabels' => ['abrogation']
            ],

            'REQUISITION' => [
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/requisition',
                'notation' => 'R006',
                'prefLabel' => 'RÃ‰QUISITION',
                'definition' => 'Action de rÃ©quisitionner des biens ou personnes'
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
                'prefLabel' => 'TUTELLE FINANCIÃˆRE',
                'definition' => 'Exercice d\'une tutelle financiÃ¨re sur un organisme'
            ]
        ];
    }

    /**
     * Insert concepts into the database
     *
     * @param int $schemeId
     * @param array $concepts
     * @return array Concept IDs
     */
    private function insertConcepts(int $schemeId, array $concepts): array
    {
        $conceptIds = [];

        // InsÃ©rer tous les concepts principaux
        foreach ($concepts as $key => $concept) {
            // InsÃ©rer si absent (clÃ©: uri)
            DB::table('thesaurus_concepts')->insertOrIgnore([
                'scheme_id' => $schemeId,
                'uri' => $concept['uri'],
                'notation' => $concept['notation'],
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // RÃ©cupÃ©rer l'ID du concept
            $conceptRow = DB::table('thesaurus_concepts')->where('uri', $concept['uri'])->first();
            if (!$conceptRow) { continue; }
            $conceptIds[$key] = $conceptRow->id;

            // Ajouter le label prÃ©fÃ©rÃ©
            DB::table('thesaurus_labels')->insertOrIgnore([
                'concept_id' => $conceptIds[$key],
                'type' => 'prefLabel',
                'literal_form' => $concept['prefLabel'],
                'language' => 'fr-fr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ajouter la dÃ©finition
            if (isset($concept['definition'])) {
                DB::table('thesaurus_concept_notes')->insertOrIgnore([
                    'concept_id' => $conceptIds[$key],
                    'type' => 'definition',
                    'note' => $concept['definition'],
                    'language' => 'fr-fr',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Ajouter les labels alternatifs (EP - EmployÃ© pour)
            if (isset($concept['altLabels'])) {
                foreach ($concept['altLabels'] as $altLabel) {
                    DB::table('thesaurus_labels')->insertOrIgnore([
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

        return $conceptIds;
    }

    /**
     * Create sub-concepts and establish hierarchical relationships
     * @param int $schemeId
     * @param array $conceptIds
     */
    private function createSubConcepts(int $schemeId, array $conceptIds): void
    {
        // CrÃ©er les concepts spÃ©cialisÃ©s (sous-concepts)
        $subConcepts = [
            'abrogation' => [
                'parent' => 'REGLEMENTATION',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/abrogation',
                'notation' => 'R005.01',
                'prefLabel' => 'abrogation',
                'definition' => 'Action d\'abroger un texte rÃ©glementaire'
            ],
            'ENQUETE_PUBLIQUE' => [
                'parent' => 'ENQUETE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/enquete-publique',
                'notation' => 'E001.01',
                'prefLabel' => 'ENQUÃŠTE PUBLIQUE',
                'definition' => 'ProcÃ©dure de consultation du public sur un projet'
            ],
            'CONTROLE_BUDGETAIRE' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-budgetaire',
                'notation' => 'C009.01',
                'prefLabel' => 'CONTRÃ”LE BUDGÃ‰TAIRE',
                'definition' => 'ContrÃ´le de l\'exÃ©cution budgÃ©taire'
            ],
            'CONTROLE_GESTION' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-gestion',
                'notation' => 'C009.02',
                'prefLabel' => 'CONTRÃ”LE DE GESTION',
                'definition' => 'ContrÃ´le de la gestion administrative et financiÃ¨re'
            ],
            'CONTROLE_LEGALITE' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-legalite',
                'notation' => 'C009.03',
                'prefLabel' => 'CONTRÃ”LE DE LÃ‰GALITÃ‰',
                'definition' => 'ContrÃ´le de la conformitÃ© des actes administratifs'
            ],
            'CONTROLE_SECURITE' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-securite',
                'notation' => 'C009.04',
                'prefLabel' => 'CONTRÃ”LE DE SÃ‰CURITÃ‰',
                'definition' => 'ContrÃ´le des mesures de sÃ©curitÃ©'
            ],
            'CONTROLE_FISCAL' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-fiscal',
                'notation' => 'C009.05',
                'prefLabel' => 'CONTRÃ”LE FISCAL',
                'definition' => 'ContrÃ´le de la situation fiscale'
            ],
            'CONTROLE_SANITAIRE' => [
                'parent' => 'CONTROLE',
                'uri' => 'https://archives.gouv.fr/thesaurus/actions-administratives/controle-sanitaire',
                'notation' => 'C009.06',
                'prefLabel' => 'CONTRÃ”LE SANITAIRE',
                'definition' => 'ContrÃ´le des conditions sanitaires'
            ]
        ];

        // InsÃ©rer les sous-concepts et crÃ©er les relations hiÃ©rarchiques
        foreach ($subConcepts as $key => $subConcept) {
            // VÃ©rifier si ce sous-concept existe dÃ©jÃ  (au cas oÃ¹)
            $existingConcept = DB::table('thesaurus_concepts')
                ->where('uri', $subConcept['uri'])
                ->first();

            if ($existingConcept) {
                $subConceptId = $existingConcept->id;
            } else {
                $subConceptId = DB::table('thesaurus_concepts')->insertGetId([
                    'scheme_id' => $schemeId,
                    'uri' => $subConcept['uri'],
                    'notation' => $subConcept['notation'],
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // S'assurer qu'il n'y a pas de collision de clÃ©s
            if (!isset($conceptIds[$key])) {
                $conceptIds[$key] = $subConceptId;
            } else {
                // Si la clÃ© existe dÃ©jÃ , utiliser une clÃ© diffÃ©rente pour Ã©viter l'Ã©crasement
                $uniqueKey = $key . '_SUB';
                $conceptIds[$uniqueKey] = $subConceptId;
            }

            // Labels et dÃ©finitions
            // VÃ©rifier si le label existe dÃ©jÃ 
            $existingLabel = DB::table('thesaurus_labels')
                ->where('concept_id', $subConceptId)
                ->where('type', 'prefLabel')
                ->where('language', 'fr-fr')
                ->first();

            if (!$existingLabel) {
                DB::table('thesaurus_labels')->insertOrIgnore([
                    'concept_id' => $subConceptId,
                    'type' => 'prefLabel',
                    'literal_form' => $subConcept['prefLabel'],
                    'language' => 'fr-fr',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // VÃ©rifier si la dÃ©finition existe dÃ©jÃ 
            $existingNote = DB::table('thesaurus_concept_notes')
                ->where('concept_id', $subConceptId)
                ->where('type', 'definition')
                ->where('language', 'fr-fr')
                ->first();

            if (!$existingNote) {
                DB::table('thesaurus_concept_notes')->insertOrIgnore([
                    'concept_id' => $subConceptId,
                    'type' => 'definition',
                    'note' => $subConcept['definition'],
                    'language' => 'fr-fr',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Relations hiÃ©rarchiques avec le concept parent
            if (!isset($conceptIds[$subConcept['parent']])) {
                throw new \Exception('Le concept parent "' . $subConcept['parent'] . '" n\'existe pas pour le sous-concept "' . $key . '"');
            }

            $parentId = $conceptIds[$subConcept['parent']];

            // VÃ©rifier si la relation broader existe dÃ©jÃ 
            $existingBroader = DB::table('thesaurus_concept_relations')
                ->where('concept_id', $subConceptId)
                ->where('related_concept_id', $parentId)
                ->where('relation_type', 'broader')
                ->first();

            if (!$existingBroader) {
                // Relation broader (le sous-concept a un terme plus large)
                DB::table('thesaurus_concept_relations')->insertOrIgnore([
                    'concept_id' => $subConceptId,
                    'related_concept_id' => $parentId,
                    'relation_type' => 'broader',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // VÃ©rifier si la relation narrower existe dÃ©jÃ 
            $existingNarrower = DB::table('thesaurus_concept_relations')
                ->where('concept_id', $parentId)
                ->where('related_concept_id', $subConceptId)
                ->where('relation_type', 'narrower')
                ->first();

            if (!$existingNarrower) {
                // Relation narrower (le concept parent a un terme plus spÃ©cifique)
                DB::table('thesaurus_concept_relations')->insertOrIgnore([
                    'concept_id' => $parentId,
                    'related_concept_id' => $subConceptId,
                    'relation_type' => 'narrower',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Create related concept relationships
     *
     * @param array $conceptIds
     */
    private function createRelatedConcepts(array $conceptIds): void
    {
        // CrÃ©er les relations associÃ©es (TA - Terme associÃ©) basÃ©es sur le thÃ©saurus
        $relatedConcepts = [
            ['AMENAGEMENT', 'CONSTRUCTION'],
            ['GESTION_COMPTABLE', 'RECOUVREMENT'],
            ['GESTION_DU_PERSONNEL', 'RECRUTEMENT'],
            ['EQUIPEMENT_MATERIEL', 'INFORMATISATION'],
            ['INFORMATISATION', 'NUMERISATION']
        ];

        foreach ($relatedConcepts as $relation) {
            if (isset($conceptIds[$relation[0]]) && isset($conceptIds[$relation[1]])) {
                // VÃ©rifier si la relation existe dÃ©jÃ  dans un sens
                $existingRelation1 = DB::table('thesaurus_concept_relations')
                    ->where('concept_id', $conceptIds[$relation[0]])
                    ->where('related_concept_id', $conceptIds[$relation[1]])
                    ->where('relation_type', 'related')
                    ->first();

                if (!$existingRelation1) {
                    // Relation bidirectionnelle - sens 1
                    DB::table('thesaurus_concept_relations')->insertOrIgnore([
                        'concept_id' => $conceptIds[$relation[0]],
                        'related_concept_id' => $conceptIds[$relation[1]],
                        'relation_type' => 'related',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // VÃ©rifier si la relation existe dÃ©jÃ  dans l'autre sens
                $existingRelation2 = DB::table('thesaurus_concept_relations')
                    ->where('concept_id', $conceptIds[$relation[1]])
                    ->where('related_concept_id', $conceptIds[$relation[0]])
                    ->where('relation_type', 'related')
                    ->first();

                if (!$existingRelation2) {
                    // Relation bidirectionnelle - sens 2
                    DB::table('thesaurus_concept_relations')->insertOrIgnore([
                        'concept_id' => $conceptIds[$relation[1]],
                        'related_concept_id' => $conceptIds[$relation[0]],
                        'relation_type' => 'related',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Create organization and namespace entries
     */
    private function createOrganizationAndNamespaces(): void
    {
        // VÃ©rifier si l'organisation existe dÃ©jÃ 
        $existingOrg = DB::table('thesaurus_organizations')
            ->where('name', 'Service InterministÃ©riel des Archives de France')
            ->first();

        if (!$existingOrg) {
            // Ajouter l'organisation responsable si elle n'existe pas
            DB::table('thesaurus_organizations')->insertGetId([
                'name' => 'Service InterministÃ©riel des Archives de France',
                'homepage' => 'https://archives.gouv.fr',
                'email' => 'contact@archives.gouv.fr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // VÃ©rifier si les namespaces existent dÃ©jÃ 
        $skosExists = DB::table('thesaurus_namespaces')
            ->where('prefix', 'skos')
            ->exists();

        $siafExists = DB::table('thesaurus_namespaces')
            ->where('prefix', 'siaf')
            ->exists();

        $namespacesToInsert = [];

        // N'ajouter que les namespaces qui n'existent pas encore
        if (!$skosExists) {
            $namespacesToInsert[] = [
                'prefix' => 'skos',
                'namespace_uri' => 'http://www.w3.org/2004/02/skos/core#',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!$siafExists) {
            $namespacesToInsert[] = [
                'prefix' => 'siaf',
                'namespace_uri' => 'https://archives.gouv.fr/thesaurus/',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // InsÃ©rer les namespaces manquants s'il y en a
        if (!empty($namespacesToInsert)) {
            DB::table('thesaurus_namespaces')->insertOrIgnore($namespacesToInsert);
        }
    }

    /**
     * Add scope notes to concepts
     *
     * @param array $conceptIds
     */
    private function addScopeNotes(array $conceptIds): void
    {
        // Ajouter des notes d'application spÃ©cifiques du thÃ©saurus
        $scopeNotes = [
            'CLASSEMENT' => 'S\'emploie pour les installations classÃ©es, les bÃ¢timents recevant du public, les hÃ´tels et les campings... Ne s\'emploie pas pour le patrimoine (voir PROTECTION).',
            'CONCESSION' => 'Voir note sur DELEGATION.',
            'CONSTRUCTION' => 'Le terme s\'emploie quand l\'Ã‰tat, une collectivitÃ© territoriale ou un organisme public sont maÃ®tres d\'ouvrage (construction d\'un bÃ¢timent administratif ou de toute infrastructure publique).',
            'DELEGATION' => 'S\'emploie pour dÃ©signer exclusivement les dÃ©lÃ©gations de service public. Le terme CONCESSION sera choisi pour tout autre cas de convention (ex: concession de travaux, concession d\'amÃ©nagement).',
            'DENOMINATION' => 'S\'emploie pour la dÃ©nomination des noms de rues, de places, de bÃ¢timents...',
            'DESIGNATION' => 'Le terme s\'emploie pour les dossiers traitant de la composition et de la reprÃ©sentation des membres d\'un organisme consultatif, d\'une association, d\'un organisme dÃ©libÃ©rant.',
            'EVACUATION' => 'Il s\'agit bien lÃ  d\'une action rÃ©sultant d\'une dÃ©cision administrative.',
            'IMMATRICULATION' => 'S\'emploie pour toute sorte d\'immatriculation : immatriculation de voiture, inscription au registre du commerce...',
            'ORGANISATION' => 'Terme Ã  associer avec le mot objet STRUCTURE ADMINISTRATIVE pour tout ce qui touche Ã  l\'Ã©valuation, la modernisation et aux rÃ©formes des structures administratives.',
            'PLACEMENT' => 'S\'emploie notamment Ã  propos de l\'EMPLOI, de l\'AIDE SOCIALE A L\'ENFANCE et des TRAVAILLEURS(S) HANDICAPE(S), des PERSONNES AGEES et des MALADES MENTAUX.',
            'PROTECTION' => 'S\'emploie pour les sites naturels, les sites archÃ©ologiques, les monuments historiques, les antiquitÃ©s et objets d\'art, les archives privÃ©es, les secteurs sauvegardÃ©s, les ZPPAUP.',
            'RECENSEMENT' => 'S\'emploie pour toute opÃ©ration permanente, rÃ©guliÃ¨re, occasionnelle, de comptage, quel que soit l\'objet du dÃ©nombrement, exception faite des recensements de population (descripteur autorisÃ© par le thesaurus).',
            'RECOURS_HIERARCHIQUE' => 'S\'emploie quand la contestation fait l\'objet d\'un rÃ¨glement ou d\'une tentative de rÃ¨glement dans un contexte purement administratif.',
            'RESTAURATION' => 'S\'emploie pour la restauration du patrimoine mobilier et immobilier.'
        ];

        foreach ($scopeNotes as $conceptKey => $note) {
            if (isset($conceptIds[$conceptKey])) {
                // VÃ©rifier si la note existe dÃ©jÃ 
                $existingScopeNote = DB::table('thesaurus_concept_notes')
                    ->where('concept_id', $conceptIds[$conceptKey])
                    ->where('type', 'scopeNote')
                    ->where('language', 'fr-fr')
                    ->first();

                if (!$existingScopeNote) {
                    DB::table('thesaurus_concept_notes')->insertOrIgnore([
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
    }

}


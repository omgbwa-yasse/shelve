<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolderType;
use App\Models\MetadataDefinition;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentFolderTypesWithMetadataSeeder extends Seeder
{
    private $admin;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user as creator
        $this->admin = User::first();

        if (!$this->admin) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        DB::beginTransaction();

        try {
            // Créer les listes de référence
            $this->createReferenceLists();

            // Créer les définitions de métadonnées
            $metadataDefinitions = $this->createMetadataDefinitions();

            // Créer les types de dossiers et documents avec métadonnées
            $this->createAdministrationTypes($metadataDefinitions);
            $this->createFinancesTypes($metadataDefinitions);
            $this->createRessourcesHumainesTypes($metadataDefinitions);
            $this->createRessourcesMobiliersImmobiliersTypes($metadataDefinitions);
            $this->createAuditTypes($metadataDefinitions);

            DB::commit();

            $this->command->info('Types de documents/dossiers et profils de métadonnées créés avec succès!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Erreur lors de la création: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer les listes de référence
     */
    private function createReferenceLists(): void
    {
        // Liste des niveaux de confidentialité
        $confidentialite = ReferenceList::firstOrCreate(
            ['code' => 'niveau_confidentialite'],
            [
                'name' => 'Niveau de confidentialité',
                'description' => 'Niveaux de confidentialité des documents',
                'created_by' => $this->admin->id,
                'created_by' => $this->admin->id,
            ]
        );

        $niveaux = [
            ['code' => 'public', 'value' => 'Public', 'sort_order' => 1],
            ['code' => 'interne', 'value' => 'Interne', 'sort_order' => 2],
            ['code' => 'confidentiel', 'value' => 'Confidentiel', 'sort_order' => 3],
            ['code' => 'secret', 'value' => 'Secret', 'sort_order' => 4],
        ];

        foreach ($niveaux as $niveau) {
            ReferenceValue::firstOrCreate(
                ['list_id' => $confidentialite->id, 'code' => $niveau['code']],
                ['value' => $niveau['value'], 'sort_order' => $niveau['sort_order'], 'active' => true, 'created_by' => $this->admin->id]
            );
        }

        // Liste des départements
        $departements = ReferenceList::firstOrCreate(
            ['code' => 'departements'],
            [
                'name' => 'Départements',
                'description' => 'Liste des départements de l\'organisation',
                'created_by' => $this->admin->id,
                'created_by' => $this->admin->id,
            ]
        );

        $depts = [
            ['code' => 'admin', 'value' => 'Administration', 'sort_order' => 1],
            ['code' => 'finance', 'value' => 'Finance', 'sort_order' => 2],
            ['code' => 'rh', 'value' => 'Ressources Humaines', 'sort_order' => 3],
            ['code' => 'logistique', 'value' => 'Logistique', 'sort_order' => 4],
            ['code' => 'audit', 'value' => 'Audit', 'sort_order' => 5],
            ['code' => 'juridique', 'value' => 'Juridique', 'sort_order' => 6],
            ['code' => 'informatique', 'value' => 'Informatique', 'sort_order' => 7],
        ];

        foreach ($depts as $dept) {
            ReferenceValue::firstOrCreate(
                ['list_id' => $departements->id, 'code' => $dept['code']],
                ['value' => $dept['value'], 'sort_order' => $dept['sort_order'], 'active' => true, 'created_by' => $this->admin->id]
            );
        }

        // Liste des devises
        $devises = ReferenceList::firstOrCreate(
            ['code' => 'devises'],
            [
                'name' => 'Devises',
                'description' => 'Liste des devises',
                'created_by' => $this->admin->id,
                'created_by' => $this->admin->id,
            ]
        );

        $currencies = [
            ['code' => 'EUR', 'value' => 'Euro (€)', 'sort_order' => 1],
            ['code' => 'USD', 'value' => 'Dollar US ($)', 'sort_order' => 2],
            ['code' => 'GBP', 'value' => 'Livre Sterling (£)', 'sort_order' => 3],
            ['code' => 'XAF', 'value' => 'Franc CFA', 'sort_order' => 4],
        ];

        foreach ($currencies as $currency) {
            ReferenceValue::firstOrCreate(
                ['list_id' => $devises->id, 'code' => $currency['code']],
                ['value' => $currency['value'], 'sort_order' => $currency['sort_order'], 'active' => true, 'created_by' => $this->admin->id]
            );
        }

        // Liste des types de contrat
        $typesContrat = ReferenceList::firstOrCreate(
            ['code' => 'types_contrat'],
            [
                'name' => 'Types de contrat',
                'description' => 'Types de contrats de travail',
                'created_by' => $this->admin->id,
                'created_by' => $this->admin->id,
            ]
        );

        $contrats = [
            ['code' => 'cdi', 'value' => 'CDI', 'sort_order' => 1],
            ['code' => 'cdd', 'value' => 'CDD', 'sort_order' => 2],
            ['code' => 'stage', 'value' => 'Stage', 'sort_order' => 3],
            ['code' => 'interim', 'value' => 'Intérim', 'sort_order' => 4],
            ['code' => 'freelance', 'value' => 'Freelance', 'sort_order' => 5],
        ];

        foreach ($contrats as $contrat) {
            ReferenceValue::firstOrCreate(
                ['list_id' => $typesContrat->id, 'code' => $contrat['code']],
                ['value' => $contrat['value'], 'sort_order' => $contrat['sort_order'], 'active' => true, 'created_by' => $this->admin->id]
            );
        }
    }

    /**
     * Créer les définitions de métadonnées
     */
    private function createMetadataDefinitions(): array
    {
        $definitions = [];

        // Métadonnées communes
        $definitions['date_creation'] = MetadataDefinition::firstOrCreate(
            ['code' => 'date_creation'],
            [
                'name' => 'Date de création',
                'data_type' => 'date',
                'description' => 'Date de création du document',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['auteur'] = MetadataDefinition::firstOrCreate(
            ['code' => 'auteur'],
            [
                'name' => 'Auteur',
                'data_type' => 'text',
                'description' => 'Auteur du document',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $confidentialiteList = ReferenceList::where('code', 'niveau_confidentialite')->first();
        $definitions['niveau_confidentialite'] = MetadataDefinition::firstOrCreate(
            ['code' => 'niveau_confidentialite'],
            [
                'name' => 'Niveau de confidentialité',
                'data_type' => 'reference_list',
                'description' => 'Niveau de confidentialité du document',
                'created_by' => $this->admin->id,
                'reference_list_id' => $confidentialiteList->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $departementslist = ReferenceList::where('code', 'departements')->first();
        $definitions['departement'] = MetadataDefinition::firstOrCreate(
            ['code' => 'departement'],
            [
                'name' => 'Département',
                'data_type' => 'reference_list',
                'description' => 'Département concerné',
                'created_by' => $this->admin->id,
                'reference_list_id' => $departementslist->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['numero_reference'] = MetadataDefinition::firstOrCreate(
            ['code' => 'numero_reference'],
            [
                'name' => 'Numéro de référence',
                'data_type' => 'text',
                'description' => 'Numéro de référence unique',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        // Métadonnées financières
        $deviseslist = ReferenceList::where('code', 'devises')->first();
        $definitions['montant'] = MetadataDefinition::firstOrCreate(
            ['code' => 'montant'],
            [
                'name' => 'Montant',
                'data_type' => 'number',
                'description' => 'Montant en devise',
                'created_by' => $this->admin->id,
                'searchable' => false,
                'active' => true,
            ]
        );

        $definitions['devise'] = MetadataDefinition::firstOrCreate(
            ['code' => 'devise'],
            [
                'name' => 'Devise',
                'data_type' => 'reference_list',
                'description' => 'Devise du montant',
                'created_by' => $this->admin->id,
                'reference_list_id' => $deviseslist->id,
                'searchable' => false,
                'active' => true,
            ]
        );

        $definitions['date_facture'] = MetadataDefinition::firstOrCreate(
            ['code' => 'date_facture'],
            [
                'name' => 'Date de facture',
                'data_type' => 'date',
                'description' => 'Date de la facture',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['numero_facture'] = MetadataDefinition::firstOrCreate(
            ['code' => 'numero_facture'],
            [
                'name' => 'Numéro de facture',
                'data_type' => 'text',
                'description' => 'Numéro unique de la facture',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['fournisseur'] = MetadataDefinition::firstOrCreate(
            ['code' => 'fournisseur'],
            [
                'name' => 'Fournisseur',
                'data_type' => 'text',
                'description' => 'Nom du fournisseur',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        // Métadonnées RH
        $typesContratList = ReferenceList::where('code', 'types_contrat')->first();
        $definitions['type_contrat'] = MetadataDefinition::firstOrCreate(
            ['code' => 'type_contrat'],
            [
                'name' => 'Type de contrat',
                'data_type' => 'reference_list',
                'description' => 'Type de contrat de travail',
                'created_by' => $this->admin->id,
                'reference_list_id' => $typesContratList->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['matricule'] = MetadataDefinition::firstOrCreate(
            ['code' => 'matricule'],
            [
                'name' => 'Matricule',
                'data_type' => 'text',
                'description' => 'Matricule de l\'employé',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['date_debut'] = MetadataDefinition::firstOrCreate(
            ['code' => 'date_debut'],
            [
                'name' => 'Date de début',
                'data_type' => 'date',
                'description' => 'Date de début',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['date_fin'] = MetadataDefinition::firstOrCreate(
            ['code' => 'date_fin'],
            [
                'name' => 'Date de fin',
                'data_type' => 'date',
                'description' => 'Date de fin',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        // Métadonnées immobilières
        $definitions['adresse'] = MetadataDefinition::firstOrCreate(
            ['code' => 'adresse'],
            [
                'name' => 'Adresse',
                'data_type' => 'textarea',
                'description' => 'Adresse complète',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['surface'] = MetadataDefinition::firstOrCreate(
            ['code' => 'surface'],
            [
                'name' => 'Surface (m²)',
                'data_type' => 'number',
                'description' => 'Surface en mètres carrés',
                'created_by' => $this->admin->id,
                'searchable' => false,
                'active' => true,
            ]
        );

        $definitions['numero_inventaire'] = MetadataDefinition::firstOrCreate(
            ['code' => 'numero_inventaire'],
            [
                'name' => 'Numéro d\'inventaire',
                'data_type' => 'text',
                'description' => 'Numéro d\'inventaire',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        // Métadonnées audit
        $definitions['date_audit'] = MetadataDefinition::firstOrCreate(
            ['code' => 'date_audit'],
            [
                'name' => 'Date d\'audit',
                'data_type' => 'date',
                'description' => 'Date de l\'audit',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['auditeur'] = MetadataDefinition::firstOrCreate(
            ['code' => 'auditeur'],
            [
                'name' => 'Auditeur',
                'data_type' => 'text',
                'description' => 'Nom de l\'auditeur',
                'created_by' => $this->admin->id,
                'searchable' => true,
                'active' => true,
            ]
        );

        $definitions['conformite'] = MetadataDefinition::firstOrCreate(
            ['code' => 'conformite'],
            [
                'name' => 'Conformité',
                'data_type' => 'select',
                'description' => 'Niveau de conformité',
                'created_by' => $this->admin->id,
                'options' => json_encode(['conforme', 'non_conforme', 'partiellement_conforme']),
                'searchable' => true,
                'active' => true,
            ]
        );

        return $definitions;
    }

    /**
     * Créer les types Administration
     */
    private function createAdministrationTypes(array $metadata): void
    {
        // Types de dossiers Administration
        $folderTypes = [
            ['code' => 'admin_courrier', 'name' => 'Courrier administratif', 'description' => 'Dossiers de courriers administratifs'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_reunion', 'name' => 'Réunions', 'description' => 'Comptes-rendus et procès-verbaux de réunions'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_deliberation', 'name' => 'Délibérations', 'description' => 'Délibérations et décisions administratives'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_archive', 'name' => 'Archives administratives', 'description' => 'Archives de documents administratifs'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_juridique', 'name' => 'Documents juridiques', 'description' => 'Documents juridiques et légaux'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_procedure', 'name' => 'Procédures', 'description' => 'Procédures et règlements internes'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_communication', 'name' => 'Communications', 'description' => 'Communications officielles'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_rapport', 'name' => 'Rapports administratifs', 'description' => 'Rapports d\'activité administratifs'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_statistique', 'name' => 'Statistiques', 'description' => 'Statistiques et données administratives'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_projet', 'name' => 'Projets administratifs', 'description' => 'Dossiers de projets administratifs'],
                'created_by' => $this->admin->id,
        ];

        foreach ($folderTypes as $type) {
            $folderType = RecordDigitalFolderType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            // Associer les métadonnées
            $this->attachFolderMetadata($folderType, [
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['departement'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3],
                $metadata['niveau_confidentialite'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 4],
            ]);
        }

        // Types de documents Administration
        $documentTypes = [
            ['code' => 'admin_courrier_entrant', 'name' => 'Courrier entrant', 'description' => 'Courrier administratif entrant'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_courrier_sortant', 'name' => 'Courrier sortant', 'description' => 'Courrier administratif sortant'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_note_service', 'name' => 'Note de service', 'description' => 'Notes de service internes'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_pv_reunion', 'name' => 'Procès-verbal de réunion', 'description' => 'Comptes-rendus de réunions'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_deliberation_doc', 'name' => 'Délibération', 'description' => 'Document de délibération'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_arrete', 'name' => 'Arrêté', 'description' => 'Arrêté administratif'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_circulaire', 'name' => 'Circulaire', 'description' => 'Circulaire administrative'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_reglement', 'name' => 'Règlement', 'description' => 'Règlement intérieur'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_rapport_activite', 'name' => 'Rapport d\'activité', 'description' => 'Rapport d\'activité administrative'],
                'created_by' => $this->admin->id,
            ['code' => 'admin_procedure_doc', 'name' => 'Document de procédure', 'description' => 'Procédure administrative'],
                'created_by' => $this->admin->id,
        ];

        foreach ($documentTypes as $type) {
            $docType = RecordDigitalDocumentType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            // Associer les métadonnées
            $this->attachDocumentMetadata($docType, [
                $metadata['numero_reference'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3],
                $metadata['departement'] => ['mandatory' => false, 'visible' => true, 'sort_order' => 4],
                $metadata['niveau_confidentialite'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 5],
            ]);
        }
    }

    /**
     * Créer les types Finances
     */
    private function createFinancesTypes(array $metadata): void
    {
        // Types de dossiers Finances
        $folderTypes = [
            ['code' => 'fin_comptabilite', 'name' => 'Comptabilité', 'description' => 'Dossiers comptables'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_budget', 'name' => 'Budget', 'description' => 'Dossiers budgétaires'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_factures', 'name' => 'Factures', 'description' => 'Factures et notes de frais'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_tresorerie', 'name' => 'Trésorerie', 'description' => 'Documents de trésorerie'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_fiscalite', 'name' => 'Fiscalité', 'description' => 'Documents fiscaux'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_investissement', 'name' => 'Investissements', 'description' => 'Dossiers d\'investissement'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_subvention', 'name' => 'Subventions', 'description' => 'Demandes et attributions de subventions'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_marche', 'name' => 'Marchés publics', 'description' => 'Marchés et appels d\'offres'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_rapport', 'name' => 'Rapports financiers', 'description' => 'Rapports et bilans financiers'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_audit', 'name' => 'Audit financier', 'description' => 'Audits et contrôles financiers'],
                'created_by' => $this->admin->id,
        ];

        foreach ($folderTypes as $type) {
            $folderType = RecordDigitalFolderType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            $this->attachFolderMetadata($folderType, [
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['niveau_confidentialite'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3],
            ]);
        }

        // Types de documents Finances
        $documentTypes = [
            ['code' => 'fin_facture_achat', 'name' => 'Facture d\'achat', 'description' => 'Facture fournisseur'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_facture_vente', 'name' => 'Facture de vente', 'description' => 'Facture client'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_bon_commande', 'name' => 'Bon de commande', 'description' => 'Bon de commande'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_note_frais', 'name' => 'Note de frais', 'description' => 'Note de frais'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_releve_bancaire', 'name' => 'Relevé bancaire', 'description' => 'Relevé de compte bancaire'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_declaration_tva', 'name' => 'Déclaration TVA', 'description' => 'Déclaration de TVA'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_bilan', 'name' => 'Bilan comptable', 'description' => 'Bilan comptable annuel'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_compte_resultat', 'name' => 'Compte de résultat', 'description' => 'Compte de résultat'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_budget_previsionnel', 'name' => 'Budget prévisionnel', 'description' => 'Budget prévisionnel'],
                'created_by' => $this->admin->id,
            ['code' => 'fin_rapport_financier', 'name' => 'Rapport financier', 'description' => 'Rapport financier périodique'],
                'created_by' => $this->admin->id,
        ];

        foreach ($documentTypes as $type) {
            $docType = RecordDigitalDocumentType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            $attachments = [
                $metadata['numero_reference'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3],
                $metadata['niveau_confidentialite'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 4],
            ];

            // Ajouter métadonnées spécifiques aux factures
            if (str_contains($type['code'], 'facture')) {
                $attachments[$metadata['numero_facture']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 5];
                $attachments[$metadata['date_facture']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 6];
                $attachments[$metadata['fournisseur']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 7];
                $attachments[$metadata['montant']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 8];
                $attachments[$metadata['devise']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 9];
            }

            $this->attachDocumentMetadata($docType, $attachments);
        }
    }

    /**
     * Créer les types Ressources Humaines
     */
    private function createRessourcesHumainesTypes(array $metadata): void
    {
        // Types de dossiers RH
        $folderTypes = [
            ['code' => 'rh_personnel', 'name' => 'Dossiers du personnel', 'description' => 'Dossiers individuels du personnel'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_recrutement', 'name' => 'Recrutement', 'description' => 'Processus de recrutement'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_formation', 'name' => 'Formation', 'description' => 'Plans et dossiers de formation'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_evaluation', 'name' => 'Évaluations', 'description' => 'Évaluations du personnel'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_conge', 'name' => 'Congés', 'description' => 'Demandes et plannings de congés'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_paie', 'name' => 'Paie', 'description' => 'Documents de paie'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_contrat', 'name' => 'Contrats', 'description' => 'Contrats de travail'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_disciplinaire', 'name' => 'Dossiers disciplinaires', 'description' => 'Procédures disciplinaires'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_medical', 'name' => 'Médecine du travail', 'description' => 'Documents médicaux'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_social', 'name' => 'Relations sociales', 'description' => 'Dialogue social et représentation'],
                'created_by' => $this->admin->id,
        ];

        foreach ($folderTypes as $type) {
            $folderType = RecordDigitalFolderType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            $this->attachFolderMetadata($folderType, [
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['niveau_confidentialite'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3, 'default_value' => 'confidentiel'],
            ]);
        }

        // Types de documents RH
        $documentTypes = [
            ['code' => 'rh_cv', 'name' => 'Curriculum Vitae', 'description' => 'CV de candidat'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_lettre_motivation', 'name' => 'Lettre de motivation', 'description' => 'Lettre de motivation'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_contrat_travail', 'name' => 'Contrat de travail', 'description' => 'Contrat de travail'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_avenant', 'name' => 'Avenant au contrat', 'description' => 'Avenant contractuel'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_fiche_paie', 'name' => 'Fiche de paie', 'description' => 'Bulletin de salaire'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_demande_conge', 'name' => 'Demande de congé', 'description' => 'Demande de congé'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_attestation', 'name' => 'Attestation', 'description' => 'Attestation de travail'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_entretien_annuel', 'name' => 'Entretien annuel', 'description' => 'Compte-rendu d\'entretien annuel'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_formation_doc', 'name' => 'Document de formation', 'description' => 'Certificat ou attestation de formation'],
                'created_by' => $this->admin->id,
            ['code' => 'rh_demission', 'name' => 'Lettre de démission', 'description' => 'Lettre de démission'],
                'created_by' => $this->admin->id,
        ];

        foreach ($documentTypes as $type) {
            $docType = RecordDigitalDocumentType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            $attachments = [
                $metadata['numero_reference'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3],
                $metadata['niveau_confidentialite'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 4, 'default_value' => 'confidentiel'],
            ];

            // Ajouter métadonnées spécifiques aux contrats
            if (str_contains($type['code'], 'contrat')) {
                $attachments[$metadata['matricule']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 5];
                $attachments[$metadata['type_contrat']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 6];
                $attachments[$metadata['date_debut']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 7];
                $attachments[$metadata['date_fin']] = ['mandatory' => false, 'visible' => true, 'sort_order' => 8];
            }

            $this->attachDocumentMetadata($docType, $attachments);
        }
    }

    /**
     * Créer les types Ressources Mobilières et Immobilières
     */
    private function createRessourcesMobiliersImmobiliersTypes(array $metadata): void
    {
        // Types de dossiers
        $folderTypes = [
            ['code' => 'immo_batiment', 'name' => 'Bâtiments', 'description' => 'Dossiers des bâtiments'],
                'created_by' => $this->admin->id,
            ['code' => 'immo_terrain', 'name' => 'Terrains', 'description' => 'Dossiers des terrains'],
                'created_by' => $this->admin->id,
            ['code' => 'immo_location', 'name' => 'Locations', 'description' => 'Contrats de location'],
                'created_by' => $this->admin->id,
            ['code' => 'immo_travaux', 'name' => 'Travaux', 'description' => 'Dossiers de travaux'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_vehicule', 'name' => 'Véhicules', 'description' => 'Parc automobile'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_materiel', 'name' => 'Matériel', 'description' => 'Matériel et équipements'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_informatique', 'name' => 'Matériel informatique', 'description' => 'Équipements informatiques'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_mobilier', 'name' => 'Mobilier', 'description' => 'Mobilier de bureau'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_inventaire', 'name' => 'Inventaires', 'description' => 'Inventaires du patrimoine'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_maintenance', 'name' => 'Maintenance', 'description' => 'Maintenance et entretien'],
                'created_by' => $this->admin->id,
        ];

        foreach ($folderTypes as $type) {
            $folderType = RecordDigitalFolderType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            $this->attachFolderMetadata($folderType, [
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['departement'] => ['mandatory' => false, 'visible' => true, 'sort_order' => 3],
            ]);
        }

        // Types de documents
        $documentTypes = [
            ['code' => 'immo_acte_propriete', 'name' => 'Acte de propriété', 'description' => 'Titre de propriété'],
                'created_by' => $this->admin->id,
            ['code' => 'immo_bail', 'name' => 'Bail', 'description' => 'Contrat de bail'],
                'created_by' => $this->admin->id,
            ['code' => 'immo_plan', 'name' => 'Plan', 'description' => 'Plan de bâtiment ou terrain'],
                'created_by' => $this->admin->id,
            ['code' => 'immo_permis', 'name' => 'Permis de construire', 'description' => 'Permis et autorisations'],
                'created_by' => $this->admin->id,
            ['code' => 'immo_devis_travaux', 'name' => 'Devis de travaux', 'description' => 'Devis pour travaux'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_facture_achat', 'name' => 'Facture d\'achat', 'description' => 'Facture d\'achat de matériel'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_carte_grise', 'name' => 'Carte grise', 'description' => 'Certificat d\'immatriculation'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_assurance', 'name' => 'Assurance', 'description' => 'Contrat d\'assurance'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_fiche_inventaire', 'name' => 'Fiche d\'inventaire', 'description' => 'Fiche d\'inventaire de bien'],
                'created_by' => $this->admin->id,
            ['code' => 'mob_bon_intervention', 'name' => 'Bon d\'intervention', 'description' => 'Bon d\'intervention maintenance'],
                'created_by' => $this->admin->id,
        ];

        foreach ($documentTypes as $type) {
            $docType = RecordDigitalDocumentType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            $attachments = [
                $metadata['numero_reference'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3],
            ];

            // Métadonnées spécifiques immobilier
            if (str_starts_with($type['code'], 'immo_')) {
                $attachments[$metadata['adresse']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 4];
                if (in_array($type['code'], ['immo_acte_propriete', 'immo_bail', 'immo_plan'])) {
                    $attachments[$metadata['surface']] = ['mandatory' => false, 'visible' => true, 'sort_order' => 5];
                }
            }

            // Métadonnées spécifiques mobilier
            if (str_starts_with($type['code'], 'mob_')) {
                $attachments[$metadata['numero_inventaire']] = ['mandatory' => true, 'visible' => true, 'sort_order' => 4];
            }

            $this->attachDocumentMetadata($docType, $attachments);
        }
    }

    /**
     * Créer les types Audit
     */
    private function createAuditTypes(array $metadata): void
    {
        // Types de dossiers Audit
        $folderTypes = [
            ['code' => 'audit_interne', 'name' => 'Audit interne', 'description' => 'Audits internes'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_externe', 'name' => 'Audit externe', 'description' => 'Audits externes'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_conformite', 'name' => 'Audit de conformité', 'description' => 'Audits de conformité réglementaire'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_financier', 'name' => 'Audit financier', 'description' => 'Audits financiers'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_qualite', 'name' => 'Audit qualité', 'description' => 'Audits qualité'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_securite', 'name' => 'Audit sécurité', 'description' => 'Audits de sécurité'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_processus', 'name' => 'Audit de processus', 'description' => 'Audits de processus'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_projet', 'name' => 'Audit de projet', 'description' => 'Audits de projets'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_si', 'name' => 'Audit SI', 'description' => 'Audits des systèmes d\'information'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_suivi', 'name' => 'Suivi d\'audit', 'description' => 'Plans d\'action et suivis'],
                'created_by' => $this->admin->id,
        ];

        foreach ($folderTypes as $type) {
            $folderType = RecordDigitalFolderType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            $this->attachFolderMetadata($folderType, [
                $metadata['date_creation'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['auteur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['departement'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3],
                $metadata['niveau_confidentialite'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 4],
            ]);
        }

        // Types de documents Audit
        $documentTypes = [
            ['code' => 'audit_plan', 'name' => 'Plan d\'audit', 'description' => 'Plan d\'audit annuel ou ponctuel'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_programme', 'name' => 'Programme d\'audit', 'description' => 'Programme détaillé d\'audit'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_rapport', 'name' => 'Rapport d\'audit', 'description' => 'Rapport d\'audit final'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_constat', 'name' => 'Fiche de constat', 'description' => 'Constat d\'audit'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_non_conformite', 'name' => 'Non-conformité', 'description' => 'Fiche de non-conformité'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_recommandation', 'name' => 'Recommandation', 'description' => 'Recommandation d\'audit'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_plan_action', 'name' => 'Plan d\'action', 'description' => 'Plan d\'action correctif'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_suivi_action', 'name' => 'Suivi d\'action', 'description' => 'Suivi de plan d\'action'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_certificat', 'name' => 'Certificat', 'description' => 'Certificat d\'audit ou de conformité'],
                'created_by' => $this->admin->id,
            ['code' => 'audit_synthese', 'name' => 'Synthèse d\'audit', 'description' => 'Synthèse de mission d\'audit'],
                'created_by' => $this->admin->id,
        ];

        foreach ($documentTypes as $type) {
            $docType = RecordDigitalDocumentType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                'created_by' => $this->admin->id,
                    'code_prefix' => strtoupper(substr($type['code'], 0, 4)),
                    'next_code_number' => 1,
                ]
            );

            $this->attachDocumentMetadata($docType, [
                $metadata['numero_reference'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 1],
                $metadata['date_audit'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 2],
                $metadata['auditeur'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 3],
                $metadata['departement'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 4],
                $metadata['conformite'] => ['mandatory' => false, 'visible' => true, 'sort_order' => 5],
                $metadata['niveau_confidentialite'] => ['mandatory' => true, 'visible' => true, 'sort_order' => 6],
            ]);
        }
    }

    /**
     * Attacher des métadonnées à un type de dossier
     */
    private function attachFolderMetadata(RecordDigitalFolderType $folderType, array $metadata): void
    {
        foreach ($metadata as $definition => $config) {
            if (!$folderType->metadataDefinitions()->where('metadata_definition_id', $definition->id)->exists()) {
                $folderType->metadataDefinitions()->attach($definition->id, [
                    'mandatory' => $config['mandatory'] ?? false,
                    'visible' => $config['visible'] ?? true,
                    'readonly' => $config['readonly'] ?? false,
                    'default_value' => $config['default_value'] ?? null,
                    'validation_rules' => isset($config['validation_rules']) ? json_encode($config['validation_rules']) : null,
                    'sort_order' => $config['sort_order'] ?? 0,
                ]);
            }
        }
    }

    /**
     * Attacher des métadonnées à un type de document
     */
    private function attachDocumentMetadata(RecordDigitalDocumentType $docType, array $metadata): void
    {
        foreach ($metadata as $definition => $config) {
            if (!$docType->metadataDefinitions()->where('metadata_definition_id', $definition->id)->exists()) {
                $docType->metadataDefinitions()->attach($definition->id, [
                    'mandatory' => $config['mandatory'] ?? false,
                    'visible' => $config['visible'] ?? true,
                    'readonly' => $config['readonly'] ?? false,
                    'default_value' => $config['default_value'] ?? null,
                    'validation_rules' => isset($config['validation_rules']) ? json_encode($config['validation_rules']) : null,
                    'sort_order' => $config['sort_order'] ?? 0,
                ]);
            }
        }
    }
}

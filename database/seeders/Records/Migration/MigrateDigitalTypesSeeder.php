<?php

namespace Database\Seeders\Records\Migration;

use App\Models\RecordDigitalFolderType;
use App\Models\RecordDigitalDocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder pour les types de dossiers et documents numériques (Phase 3 - SpecKit)
 * Adapté à la structure réelle des tables existantes
 */
class MigrateDigitalTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("🚀 Création des types de dossiers et documents numériques (Phase 3)...");

        // Nettoyer les types existants (Phase 3 redémarrage propre)
        $this->command->info("\n🧹 Nettoyage des types existants...");
        RecordDigitalFolderType::query()->forceDelete();
        RecordDigitalDocumentType::query()->forceDelete();
        $this->command->info("   ✓ Types précédents supprimés\n");

        // ====================================================================
        // TYPES DE DOSSIERS NUMÉRIQUES
        // ====================================================================
        $this->command->info("\n📁 Création des types de dossiers...");

        // Structure réelle: code, name, description, icon, color, metadata_template_id,
        // code_prefix, code_pattern, default_access_level, requires_approval, mandatory_metadata,
        // allowed_document_types, is_active, is_system, display_order
        $folderTypes = [
            [
                'code' => 'CONTRACTS',
                'name' => 'Contrats',
                'description' => 'Dossiers pour la gestion des contrats (fournisseurs, clients, partenaires)',
                'code_prefix' => 'CTR',
                'code_pattern' => 'CTR-{YYYY}-{NNNN}',
                'default_access_level' => 'restricted',
                'requires_approval' => true,
                'mandatory_metadata' => json_encode(['contract_party', 'contract_date', 'expiry_date']),
                'allowed_document_types' => json_encode([]), // Sera mis à jour après
                'icon' => '📄',
                'color' => '#3B82F6',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 0,
            ],
            [
                'code' => 'HR',
                'name' => 'Ressources Humaines',
                'description' => 'Dossiers pour la gestion du personnel (dossiers employés, fiches de paie, contrats de travail)',
                'code_prefix' => 'HR',
                'code_pattern' => 'HR-{YYYY}-{NNNN}',
                'default_access_level' => 'confidential',
                'requires_approval' => true,
                'mandatory_metadata' => json_encode(['employee_id', 'employee_name', 'department']),
                'allowed_document_types' => json_encode([]),
                'icon' => '👥',
                'color' => '#10B981',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 1,
            ],
            [
                'code' => 'INVOICES',
                'name' => 'Factures',
                'description' => 'Dossiers pour la gestion des factures (clients, fournisseurs)',
                'code_prefix' => 'INV',
                'code_pattern' => 'INV-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'requires_approval' => false,
                'mandatory_metadata' => json_encode(['invoice_number', 'invoice_date', 'amount']),
                'allowed_document_types' => json_encode([]),
                'icon' => '💰',
                'color' => '#EF4444',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 2,
            ],
            [
                'code' => 'ACCOUNTING',
                'name' => 'Comptabilité',
                'description' => 'Dossiers pour la gestion comptable (bilans, comptes, rapports)',
                'code_prefix' => 'ACC',
                'code_pattern' => 'ACC-{YYYY}-{NNNN}',
                'default_access_level' => 'restricted',
                'requires_approval' => true,
                'mandatory_metadata' => json_encode(['fiscal_year', 'period', 'account_number']),
                'allowed_document_types' => json_encode([]),
                'icon' => '📊',
                'color' => '#8B5CF6',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 3,
            ],
            [
                'code' => 'PROJECTS',
                'name' => 'Projets',
                'description' => 'Dossiers pour la gestion des projets (documentation, rapports, livrables)',
                'code_prefix' => 'PRJ',
                'code_pattern' => 'PRJ-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'requires_approval' => false,
                'mandatory_metadata' => json_encode(['project_id', 'project_name', 'start_date']),
                'allowed_document_types' => json_encode([]),
                'icon' => '🗂️',
                'color' => '#F59E0B',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 4,
            ],
        ];

        $createdFolderTypes = [];
        foreach ($folderTypes as $data) {
            $folderType = RecordDigitalFolderType::create($data);
            $createdFolderTypes[$data['code']] = $folderType;
            $this->command->info("   ✓ Type de dossier créé: {$folderType->code} - {$folderType->name}");
        }

        // ====================================================================
        // TYPES DE DOCUMENTS NUMÉRIQUES
        // ====================================================================
        $this->command->info("\n📝 Création des types de documents...");

        // Structure réelle: code, name, description, icon, color, metadata_template_id,
        // code_prefix, code_pattern, default_access_level, allowed_mime_types, allowed_extensions,
        // max_file_size, requires_signature, requires_approval, mandatory_metadata, retention_years,
        // enable_versioning, max_versions, is_active, is_system, display_order
        $documentTypes = [
            [
                'code' => 'INVOICE',
                'name' => 'Facture',
                'description' => 'Document de facturation officiel',
                'code_prefix' => 'INV',
                'code_pattern' => 'INV-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode(['application/pdf']),
                'allowed_extensions' => json_encode(['pdf']),
                'max_file_size' => 10 * 1024 * 1024, // 10 MB
                'requires_signature' => false,
                'requires_approval' => false,
                'mandatory_metadata' => json_encode(['invoice_number', 'amount', 'date']),
                'retention_years' => 7,
                'enable_versioning' => false,
                'max_versions' => 5,
                'icon' => '📄',
                'color' => '#3B82F6',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 0,
            ],
            [
                'code' => 'QUOTE',
                'name' => 'Devis',
                'description' => 'Devis commercial ou demande de prix',
                'code_prefix' => 'QTE',
                'code_pattern' => 'QTE-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode([
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]),
                'allowed_extensions' => json_encode(['pdf', 'docx', 'xlsx']),
                'max_file_size' => 5 * 1024 * 1024, // 5 MB
                'requires_signature' => false,
                'requires_approval' => false,
                'mandatory_metadata' => json_encode(['quote_number', 'client', 'amount']),
                'retention_years' => 3,
                'enable_versioning' => true,
                'max_versions' => 10,
                'icon' => '📊',
                'color' => '#10B981',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 1,
            ],
            [
                'code' => 'CONTRACT',
                'name' => 'Contrat',
                'description' => 'Contrat légal ou accord officiel',
                'code_prefix' => 'CTR',
                'code_pattern' => 'CTR-{YYYY}-{NNNN}',
                'default_access_level' => 'restricted',
                'allowed_mime_types' => json_encode(['application/pdf']),
                'allowed_extensions' => json_encode(['pdf']),
                'max_file_size' => 20 * 1024 * 1024, // 20 MB
                'requires_signature' => true,
                'requires_approval' => true,
                'mandatory_metadata' => json_encode(['contract_party', 'start_date', 'expiry_date']),
                'retention_years' => 30,
                'enable_versioning' => true,
                'max_versions' => 20,
                'icon' => '📝',
                'color' => '#EF4444',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 2,
            ],
            [
                'code' => 'REPORT',
                'name' => 'Rapport',
                'description' => 'Rapport technique ou administratif',
                'code_prefix' => 'RPT',
                'code_pattern' => 'RPT-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode([
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ]),
                'allowed_extensions' => json_encode(['pdf', 'docx']),
                'max_file_size' => 15 * 1024 * 1024, // 15 MB
                'requires_signature' => false,
                'requires_approval' => false,
                'mandatory_metadata' => json_encode(['report_type', 'author', 'date']),
                'retention_years' => 10,
                'enable_versioning' => true,
                'max_versions' => 15,
                'icon' => '📋',
                'color' => '#8B5CF6',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 3,
            ],
            [
                'code' => 'MEMO',
                'name' => 'Note de service',
                'description' => 'Note interne ou mémorandum',
                'code_prefix' => 'MEMO',
                'code_pattern' => 'MEMO-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode([
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ]),
                'allowed_extensions' => json_encode(['pdf', 'docx']),
                'max_file_size' => 2 * 1024 * 1024, // 2 MB
                'requires_signature' => false,
                'requires_approval' => false,
                'mandatory_metadata' => json_encode(['subject', 'recipients', 'date']),
                'retention_years' => 2,
                'enable_versioning' => false,
                'max_versions' => 3,
                'icon' => '📌',
                'color' => '#F59E0B',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 4,
            ],
            [
                'code' => 'PAYSLIP',
                'name' => 'Fiche de paie',
                'description' => 'Bulletin de salaire mensuel',
                'code_prefix' => 'PAY',
                'code_pattern' => 'PAY-{YYYY}-{MM}-{NNNN}',
                'default_access_level' => 'confidential',
                'allowed_mime_types' => json_encode(['application/pdf']),
                'allowed_extensions' => json_encode(['pdf']),
                'max_file_size' => 1 * 1024 * 1024, // 1 MB
                'requires_signature' => false,
                'requires_approval' => true,
                'mandatory_metadata' => json_encode(['employee_id', 'period', 'net_salary']),
                'retention_years' => 50,
                'enable_versioning' => false,
                'max_versions' => 1,
                'icon' => '💵',
                'color' => '#06B6D4',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 5,
            ],
            [
                'code' => 'PURCHASE_ORDER',
                'name' => 'Bon de commande',
                'description' => 'Ordre d\'achat ou bon de commande fournisseur',
                'code_prefix' => 'PO',
                'code_pattern' => 'PO-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode([
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]),
                'allowed_extensions' => json_encode(['pdf', 'xlsx']),
                'max_file_size' => 5 * 1024 * 1024, // 5 MB
                'requires_signature' => false,
                'requires_approval' => true,
                'mandatory_metadata' => json_encode(['po_number', 'supplier', 'amount']),
                'retention_years' => 7,
                'enable_versioning' => true,
                'max_versions' => 5,
                'icon' => '🛒',
                'color' => '#EC4899',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 6,
            ],
            [
                'code' => 'CORRESPONDENCE',
                'name' => 'Correspondance',
                'description' => 'Courrier, email ou correspondance officielle',
                'code_prefix' => 'COR',
                'code_pattern' => 'COR-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode([
                    'application/pdf',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'message/rfc822',
                ]),
                'allowed_extensions' => json_encode(['pdf', 'docx', 'eml']),
                'max_file_size' => 10 * 1024 * 1024, // 10 MB
                'requires_signature' => false,
                'requires_approval' => false,
                'mandatory_metadata' => json_encode(['sender', 'recipient', 'date', 'subject']),
                'retention_years' => 5,
                'enable_versioning' => false,
                'max_versions' => 5,
                'icon' => '✉️',
                'color' => '#14B8A6',
                'is_active' => true,
                'is_system' => false,
                'display_order' => 7,
            ],
        ];

        $createdDocTypes = [];
        foreach ($documentTypes as $data) {
            $docType = RecordDigitalDocumentType::create($data);
            $createdDocTypes[$data['code']] = $docType;
            $this->command->info("   ✓ Type de document créé: {$docType->code} - {$docType->name}");
        }

        // ====================================================================
        // LIAISON DES TYPES DE DOCUMENTS AUX TYPES DE DOSSIERS
        // ====================================================================
        $this->command->info("\n🔗 Liaison des types de documents aux types de dossiers...");

        $associations = [
            'CONTRACTS' => ['CONTRACT', 'QUOTE'],
            'HR' => ['PAYSLIP', 'CONTRACT', 'MEMO'],
            'INVOICES' => ['INVOICE', 'PURCHASE_ORDER'],
            'ACCOUNTING' => ['INVOICE', 'REPORT'],
            'PROJECTS' => ['REPORT', 'MEMO', 'CORRESPONDENCE'],
        ];

        foreach ($associations as $folderCode => $docCodes) {
            if (isset($createdFolderTypes[$folderCode])) {
                $folderType = $createdFolderTypes[$folderCode];
                $docTypeIds = [];

                foreach ($docCodes as $docCode) {
                    if (isset($createdDocTypes[$docCode])) {
                        $docTypeIds[] = $createdDocTypes[$docCode]->id;
                    }
                }

                $folderType->update([
                    'allowed_document_types' => json_encode($docTypeIds)
                ]);

                $this->command->info("   ✓ {$folderType->name}: " . count($docTypeIds) . " types de documents autorisés");
            }
        }

        // ====================================================================
        // RÉSUMÉ
        // ====================================================================
        $this->command->info("\n✅ Seed terminé!");
        $this->command->info("   📁 " . count($createdFolderTypes) . " types de dossiers créés");
        $this->command->info("   📝 " . count($createdDocTypes) . " types de documents créés");
        $this->command->info("   🔗 " . count($associations) . " liaisons configurées");
        $this->command->info("\n🎉 Phase 3 (Types System) terminée avec succès!\n");
    }
}



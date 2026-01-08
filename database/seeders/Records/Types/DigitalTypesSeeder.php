<?php

namespace Database\Seeders\Records\Types;

use Illuminate\Database\Seeder;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordDigitalDocumentType;

class DigitalTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedFolderTypes();
        $this->seedDocumentTypes();
    }

    /**
     * Seed folder types
     */
    private function seedFolderTypes(): void
    {
        $folderTypes = [
            [
                'code' => 'CONTRACTS',
                'name' => 'Contrats',
                'description' => 'Dossiers de gestion des contrats',
                'icon' => 'fa-file-contract',
                'color' => '#3B82F6',
                'code_prefix' => 'CTR',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'confidential',
                'requires_approval' => true,
                'allowed_document_types' => ['CONTRACT_DOC', 'AMENDMENT', 'QUOTE'],
                'mandatory_metadata' => ['client_name', 'contract_date', 'amount'],
                'is_system' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'HR',
                'name' => 'Ressources Humaines',
                'description' => 'Dossiers du personnel',
                'icon' => 'fa-users',
                'color' => '#10B981',
                'code_prefix' => 'HR',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'secret',
                'requires_approval' => false,
                'allowed_document_types' => ['HR_DOC', 'PAYSLIP', 'LEAVE_REQUEST'],
                'mandatory_metadata' => ['employee_name', 'employee_id'],
                'is_system' => true,
                'display_order' => 2,
            ],
            [
                'code' => 'PROJECTS',
                'name' => 'Projets',
                'description' => 'Dossiers de gestion de projets',
                'icon' => 'fa-project-diagram',
                'color' => '#F59E0B',
                'code_prefix' => 'PRJ',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'internal',
                'requires_approval' => false,
                'allowed_document_types' => null, // Tous les types autorisés
                'mandatory_metadata' => ['project_name', 'project_manager'],
                'is_system' => true,
                'display_order' => 3,
            ],
            [
                'code' => 'FINANCE',
                'name' => 'Finance',
                'description' => 'Dossiers financiers et comptables',
                'icon' => 'fa-money-bill-wave',
                'color' => '#EF4444',
                'code_prefix' => 'FIN',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'confidential',
                'requires_approval' => true,
                'allowed_document_types' => ['INVOICE', 'RECEIPT', 'FINANCIAL_REPORT'],
                'mandatory_metadata' => ['fiscal_year', 'amount'],
                'is_system' => true,
                'display_order' => 4,
            ],
            [
                'code' => 'LEGAL',
                'name' => 'Juridique',
                'description' => 'Dossiers juridiques et contentieux',
                'icon' => 'fa-gavel',
                'color' => '#8B5CF6',
                'code_prefix' => 'LEG',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'secret',
                'requires_approval' => true,
                'allowed_document_types' => ['LEGAL_DOC', 'JUDGMENT', 'LAWSUIT'],
                'mandatory_metadata' => ['case_number', 'parties'],
                'is_system' => true,
                'display_order' => 5,
            ],
            [
                'code' => 'MARKETING',
                'name' => 'Marketing',
                'description' => 'Dossiers de marketing et communication',
                'icon' => 'fa-bullhorn',
                'color' => '#EC4899',
                'code_prefix' => 'MKT',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'internal',
                'requires_approval' => false,
                'allowed_document_types' => ['MARKETING_DOC', 'BROCHURE', 'CAMPAIGN'],
                'mandatory_metadata' => ['campaign_name'],
                'is_system' => true,
                'display_order' => 6,
            ],
        ];

        foreach ($folderTypes as $type) {
            RecordDigitalFolderType::create($type);
        }

        $this->command->info('✅ Types de dossiers créés avec succès : ' . count($folderTypes));
    }

    /**
     * Seed document types
     */
    private function seedDocumentTypes(): void
    {
        $documentTypes = [
            // Types de contrats
            [
                'code' => 'CONTRACT_DOC',
                'name' => 'Document contractuel',
                'description' => 'Contrat signé',
                'icon' => 'fa-file-signature',
                'color' => '#3B82F6',
                'code_prefix' => 'DOC',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'confidential',
                'allowed_mime_types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'allowed_extensions' => ['.pdf', '.doc', '.docx'],
                'max_file_size' => 10485760, // 10MB
                'requires_signature' => true,
                'requires_approval' => true,
                'retention_years' => 10,
                'enable_versioning' => true,
                'max_versions' => 10,
                'is_system' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'AMENDMENT',
                'name' => 'Avenant',
                'description' => 'Avenant à un contrat',
                'icon' => 'fa-file-alt',
                'color' => '#3B82F6',
                'code_prefix' => 'AVT',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'confidential',
                'allowed_mime_types' => ['application/pdf'],
                'allowed_extensions' => ['.pdf'],
                'max_file_size' => 5242880, // 5MB
                'requires_signature' => true,
                'requires_approval' => true,
                'retention_years' => 10,
                'enable_versioning' => true,
                'is_system' => true,
                'display_order' => 2,
            ],
            [
                'code' => 'QUOTE',
                'name' => 'Devis',
                'description' => 'Devis commercial',
                'icon' => 'fa-file-invoice',
                'color' => '#10B981',
                'code_prefix' => 'QTE',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                'allowed_extensions' => ['.pdf', '.xlsx'],
                'max_file_size' => 5242880, // 5MB
                'requires_signature' => false,
                'requires_approval' => false,
                'retention_years' => 3,
                'enable_versioning' => true,
                'is_system' => true,
                'display_order' => 3,
            ],
            // Types financiers
            [
                'code' => 'INVOICE',
                'name' => 'Facture',
                'description' => 'Facture client ou fournisseur',
                'icon' => 'fa-file-invoice-dollar',
                'color' => '#EF4444',
                'code_prefix' => 'INV',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'confidential',
                'allowed_mime_types' => ['application/pdf'],
                'allowed_extensions' => ['.pdf'],
                'max_file_size' => 5242880, // 5MB
                'requires_signature' => false,
                'requires_approval' => true,
                'retention_years' => 10,
                'enable_versioning' => false,
                'is_system' => true,
                'display_order' => 10,
            ],
            [
                'code' => 'RECEIPT',
                'name' => 'Reçu',
                'description' => 'Reçu de paiement',
                'icon' => 'fa-receipt',
                'color' => '#10B981',
                'code_prefix' => 'RCP',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'],
                'allowed_extensions' => ['.pdf', '.jpg', '.jpeg', '.png'],
                'max_file_size' => 2097152, // 2MB
                'requires_signature' => false,
                'requires_approval' => false,
                'retention_years' => 5,
                'enable_versioning' => false,
                'is_system' => true,
                'display_order' => 11,
            ],
            [
                'code' => 'FINANCIAL_REPORT',
                'name' => 'Rapport financier',
                'description' => 'Rapport financier périodique',
                'icon' => 'fa-chart-line',
                'color' => '#F59E0B',
                'code_prefix' => 'RPT',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'confidential',
                'allowed_mime_types' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                'allowed_extensions' => ['.pdf', '.xlsx'],
                'max_file_size' => 20971520, // 20MB
                'requires_signature' => true,
                'requires_approval' => true,
                'retention_years' => 10,
                'enable_versioning' => true,
                'is_system' => true,
                'display_order' => 12,
            ],
            // Types RH
            [
                'code' => 'HR_DOC',
                'name' => 'Document RH',
                'description' => 'Document administratif RH',
                'icon' => 'fa-id-card',
                'color' => '#10B981',
                'code_prefix' => 'RH',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'secret',
                'allowed_mime_types' => ['application/pdf'],
                'allowed_extensions' => ['.pdf'],
                'max_file_size' => 5242880, // 5MB
                'requires_signature' => true,
                'requires_approval' => true,
                'retention_years' => 50,
                'enable_versioning' => true,
                'is_system' => true,
                'display_order' => 20,
            ],
            [
                'code' => 'PAYSLIP',
                'name' => 'Bulletin de paie',
                'description' => 'Bulletin de salaire',
                'icon' => 'fa-money-check',
                'color' => '#10B981',
                'code_prefix' => 'PAY',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'secret',
                'allowed_mime_types' => ['application/pdf'],
                'allowed_extensions' => ['.pdf'],
                'max_file_size' => 1048576, // 1MB
                'requires_signature' => false,
                'requires_approval' => false,
                'retention_years' => 50,
                'enable_versioning' => false,
                'is_system' => true,
                'display_order' => 21,
            ],
            // Types génériques
            [
                'code' => 'GENERAL_DOC',
                'name' => 'Document général',
                'description' => 'Document sans type spécifique',
                'icon' => 'fa-file',
                'color' => '#6B7280',
                'code_prefix' => 'DOC',
                'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => null, // Tous les types autorisés
                'allowed_extensions' => null, // Toutes les extensions autorisées
                'max_file_size' => null, // Pas de limite
                'requires_signature' => false,
                'requires_approval' => false,
                'retention_years' => 5,
                'enable_versioning' => true,
                'is_system' => true,
                'display_order' => 100,
            ],
        ];

        foreach ($documentTypes as $type) {
            RecordDigitalDocumentType::create($type);
        }

        $this->command->info('✅ Types de documents créés avec succès : ' . count($documentTypes));
    }
}



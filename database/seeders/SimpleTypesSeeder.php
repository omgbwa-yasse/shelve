<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordDigitalDocumentType;
use App\Models\User;

class SimpleTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();

        // ====================================================================
        // TYPES DE DOSSIERS
        // ====================================================================
        $this->command->info("\n📁 Création des types de dossiers...");

        $folderTypes = [
            [
                'code' => 'CONTRACTS',
                'name' => 'Contrats',
                'description' => 'Dossiers pour la gestion des contrats (fournisseurs, clients, partenaires)',
                'code_pattern' => 'CTR-{YYYY}-{NNNN}',
                'default_access_level' => 'confidential',
                'requires_approval' => true,
                'icon' => '📄',
                'color' => '#3B82F6',
                'is_active' => true,
                'display_order' => 1,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'HR',
                'name' => 'Ressources Humaines',
                'description' => 'Dossiers pour la gestion du personnel et RH',
                'code_pattern' => 'HR-{YYYY}-{NNNN}',
                'default_access_level' => 'secret',
                'requires_approval' => true,
                'icon' => '👥',
                'color' => '#10B981',
                'is_active' => true,
                'display_order' => 2,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'INVOICES',
                'name' => 'Factures',
                'description' => 'Dossiers pour la gestion des factures',
                'code_pattern' => 'INV-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'requires_approval' => false,
                'icon' => '💰',
                'color' => '#EF4444',
                'is_active' => true,
                'display_order' => 3,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'ACCOUNTING',
                'name' => 'Comptabilité',
                'description' => 'Dossiers pour la gestion comptable',
                'code_pattern' => 'ACC-{YYYY}-{NNNN}',
                'default_access_level' => 'confidential',
                'requires_approval' => true,
                'icon' => '📊',
                'color' => '#8B5CF6',
                'is_active' => true,
                'display_order' => 4,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'PROJECTS',
                'name' => 'Projets',
                'description' => 'Dossiers pour la gestion des projets',
                'code_pattern' => 'PRJ-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'requires_approval' => false,
                'icon' => '🗂️',
                'color' => '#F59E0B',
                'is_active' => true,
                'display_order' => 5,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'LEGAL',
                'name' => 'Juridique',
                'description' => 'Dossiers juridiques et légaux',
                'code_pattern' => 'LEG-{YYYY}-{NNNN}',
                'default_access_level' => 'confidential',
                'requires_approval' => true,
                'icon' => '⚖️',
                'color' => '#6366F1',
                'is_active' => true,
                'display_order' => 6,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'MARKETING',
                'name' => 'Marketing',
                'description' => 'Dossiers marketing et communication',
                'code_pattern' => 'MKT-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'requires_approval' => false,
                'icon' => '📢',
                'color' => '#EC4899',
                'is_active' => true,
                'display_order' => 7,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'ADMIN',
                'name' => 'Administration',
                'description' => 'Dossiers administratifs généraux',
                'code_pattern' => 'ADM-{YYYY}-{NNNN}',
                'default_access_level' => 'public',
                'requires_approval' => false,
                'icon' => '🏢',
                'color' => '#64748B',
                'is_active' => true,
                'display_order' => 8,
                'created_by' => $admin?->id,
            ],
        ];

        foreach ($folderTypes as $data) {
            $type = RecordDigitalFolderType::create($data);
            $this->command->info("   ✓ {$type->code} - {$type->name}");
        }

        // ====================================================================
        // TYPES DE DOCUMENTS
        // ====================================================================
        $this->command->info("\n📝 Création des types de documents...");

        $documentTypes = [
            [
                'code' => 'INVOICE',
                'name' => 'Facture',
                'description' => 'Document de facturation',
                'category' => 'Financier',
                'naming_pattern' => 'INV-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode(['application/pdf', 'image/jpeg', 'image/png']),
                'allowed_extensions' => json_encode(['pdf', 'jpg', 'jpeg', 'png']),
                'max_file_size' => 10 * 1024 * 1024, // 10 MB
                'requires_approval' => false,
                'requires_versioning' => true,
                'icon' => '📄',
                'color' => '#3B82F6',
                'is_active' => true,
                'display_order' => 1,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'CONTRACT',
                'name' => 'Contrat',
                'description' => 'Document contractuel',
                'category' => 'Juridique',
                'naming_pattern' => 'CTR-{YYYY}-{NNNN}',
                'default_access_level' => 'confidential',
                'allowed_mime_types' => json_encode(['application/pdf']),
                'allowed_extensions' => json_encode(['pdf']),
                'max_file_size' => 20 * 1024 * 1024,
                'requires_approval' => true,
                'requires_versioning' => true,
                'icon' => '📜',
                'color' => '#10B981',
                'is_active' => true,
                'display_order' => 2,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'REPORT',
                'name' => 'Rapport',
                'description' => 'Document de rapport',
                'category' => 'Administratif',
                'naming_pattern' => 'RPT-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                'allowed_extensions' => json_encode(['pdf', 'doc', 'docx']),
                'max_file_size' => 15 * 1024 * 1024,
                'requires_approval' => false,
                'requires_versioning' => true,
                'icon' => '📊',
                'color' => '#EF4444',
                'is_active' => true,
                'display_order' => 3,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'LETTER',
                'name' => 'Courrier',
                'description' => 'Courrier officiel',
                'category' => 'Administratif',
                'naming_pattern' => 'LTR-{YYYY}-{NNNN}',
                'default_access_level' => 'public',
                'allowed_mime_types' => json_encode(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                'allowed_extensions' => json_encode(['pdf', 'doc', 'docx']),
                'max_file_size' => 5 * 1024 * 1024,
                'requires_approval' => false,
                'requires_versioning' => false,
                'icon' => '✉️',
                'color' => '#8B5CF6',
                'is_active' => true,
                'display_order' => 4,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'NOTE',
                'name' => 'Note de service',
                'description' => 'Note interne',
                'category' => 'Administratif',
                'naming_pattern' => 'NOT-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                'allowed_extensions' => json_encode(['pdf', 'doc', 'docx']),
                'max_file_size' => 5 * 1024 * 1024,
                'requires_approval' => false,
                'requires_versioning' => false,
                'icon' => '📝',
                'color' => '#F59E0B',
                'is_active' => true,
                'display_order' => 5,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'SPREADSHEET',
                'name' => 'Tableau',
                'description' => 'Document tableur',
                'category' => 'Bureautique',
                'naming_pattern' => 'XLS-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                'allowed_extensions' => json_encode(['xls', 'xlsx']),
                'max_file_size' => 10 * 1024 * 1024,
                'requires_approval' => false,
                'requires_versioning' => true,
                'icon' => '📈',
                'color' => '#14B8A6',
                'is_active' => true,
                'display_order' => 6,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'PRESENTATION',
                'name' => 'Présentation',
                'description' => 'Document de présentation',
                'category' => 'Bureautique',
                'naming_pattern' => 'PPT-{YYYY}-{NNNN}',
                'default_access_level' => 'internal',
                'allowed_mime_types' => json_encode(['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation']),
                'allowed_extensions' => json_encode(['ppt', 'pptx']),
                'max_file_size' => 20 * 1024 * 1024,
                'requires_approval' => false,
                'requires_versioning' => true,
                'icon' => '📊',
                'color' => '#F97316',
                'is_active' => true,
                'display_order' => 7,
                'created_by' => $admin?->id,
            ],
            [
                'code' => 'IMAGE',
                'name' => 'Image',
                'description' => 'Fichier image',
                'category' => 'Média',
                'naming_pattern' => 'IMG-{YYYY}-{NNNN}',
                'default_access_level' => 'public',
                'allowed_mime_types' => json_encode(['image/jpeg', 'image/png', 'image/gif', 'image/webp']),
                'allowed_extensions' => json_encode(['jpg', 'jpeg', 'png', 'gif', 'webp']),
                'max_file_size' => 5 * 1024 * 1024,
                'requires_approval' => false,
                'requires_versioning' => false,
                'icon' => '🖼️',
                'color' => '#A855F7',
                'is_active' => true,
                'display_order' => 8,
                'created_by' => $admin?->id,
            ],
        ];

        foreach ($documentTypes as $data) {
            $type = RecordDigitalDocumentType::create($data);
            $this->command->info("   ✓ {$type->code} - {$type->name}");
        }

        $this->command->info("\n✅ Seeder terminé avec succès!");
        $this->command->info("   📁 " . count($folderTypes) . " types de dossiers créés");
        $this->command->info("   📝 " . count($documentTypes) . " types de documents créés");
    }
}

<?php

namespace Database\Seeders\Records\Migration;

use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalFolderType;
use App\Models\Organisation;
use App\Models\User;
use App\Services\RecordDigitalFolderService;
use Illuminate\Database\Seeder;

/**
 * Seeder pour les dossiers numériques (Phase 4 - SpecKit)
 * Crée une hiérarchie de dossiers avec différents types
 */
class MigrateFoldersSeeder extends Seeder
{
    private RecordDigitalFolderService $service;

    public function __construct(RecordDigitalFolderService $service)
    {
        $this->service = $service;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("🚀 Création des dossiers numériques (Phase 4)...");

        // Récupérer les données de base
        $user = User::first();
        $organisation = Organisation::first();

        if (!$user || !$organisation) {
            $this->command->error("❌ Impossible de trouver un utilisateur ou une organisation");
            return;
        }

        // Nettoyer les dossiers existants
        $this->command->info("\n🧹 Nettoyage des dossiers existants...");
        RecordDigitalFolder::query()->forceDelete();
        $this->command->info("   ✓ Dossiers précédents supprimés\n");

        // Récupérer les types
        $types = RecordDigitalFolderType::all()->keyBy('code');

        // ====================================================================
        // DOSSIERS CONTRATS
        // ====================================================================
        $this->command->info("📁 Création des dossiers CONTRATS...");

        $contractsRoot = $this->service->createFolder(
            $types['CONTRACTS'],
            [
                'name' => 'Contrats 2025',
                'description' => 'Dossier principal des contrats pour l\'année 2025',
                'metadata' => [
                    'contract_party' => 'Tous',
                    'contract_date' => '2025-01-01',
                    'expiry_date' => '2025-12-31',
                ],
                'access_level' => 'confidential',
            ],
            $user,
            $organisation
        );
        $this->command->info("   ✓ Créé: {$contractsRoot->code} - {$contractsRoot->name}");

        // Sous-dossiers contrats fournisseurs
        $contractsSuppliers = $this->service->createFolder(
            $types['CONTRACTS'],
            [
                'name' => 'Contrats Fournisseurs',
                'description' => 'Contrats avec les fournisseurs',
                'metadata' => [
                    'contract_party' => 'Fournisseurs',
                    'contract_date' => '2025-01-01',
                    'expiry_date' => '2025-12-31',
                ],
            ],
            $user,
            $organisation,
            $contractsRoot
        );
        $this->command->info("   ✓ Créé: {$contractsSuppliers->code} - {$contractsSuppliers->name}");

        // Sous-dossiers contrats clients
        $contractsClients = $this->service->createFolder(
            $types['CONTRACTS'],
            [
                'name' => 'Contrats Clients',
                'description' => 'Contrats avec les clients',
                'metadata' => [
                    'contract_party' => 'Clients',
                    'contract_date' => '2025-01-01',
                    'expiry_date' => '2025-12-31',
                ],
            ],
            $user,
            $organisation,
            $contractsRoot
        );
        $this->command->info("   ✓ Créé: {$contractsClients->code} - {$contractsClients->name}");

        // ====================================================================
        // DOSSIERS RH
        // ====================================================================
        $this->command->info("\n👥 Création des dossiers RESSOURCES HUMAINES...");

        $hrRoot = $this->service->createFolder(
            $types['HR'],
            [
                'name' => 'Ressources Humaines 2025',
                'description' => 'Dossier principal RH pour 2025',
                'metadata' => [
                    'employee_id' => 'ALL',
                    'employee_name' => 'Tous les employés',
                    'department' => 'RH',
                ],
                'access_level' => 'confidential',
            ],
            $user,
            $organisation
        );
        $this->command->info("   ✓ Créé: {$hrRoot->code} - {$hrRoot->name}");

        // Sous-dossiers par département
        $hrIT = $this->service->createFolder(
            $types['HR'],
            [
                'name' => 'Département IT',
                'description' => 'Dossiers des employés IT',
                'metadata' => [
                    'employee_id' => 'DEPT-IT',
                    'employee_name' => 'Département IT',
                    'department' => 'IT',
                ],
            ],
            $user,
            $organisation,
            $hrRoot
        );
        $this->command->info("   ✓ Créé: {$hrIT->code} - {$hrIT->name}");

        $hrFinance = $this->service->createFolder(
            $types['HR'],
            [
                'name' => 'Département Finance',
                'description' => 'Dossiers des employés Finance',
                'metadata' => [
                    'employee_id' => 'DEPT-FIN',
                    'employee_name' => 'Département Finance',
                    'department' => 'Finance',
                ],
            ],
            $user,
            $organisation,
            $hrRoot
        );
        $this->command->info("   ✓ Créé: {$hrFinance->code} - {$hrFinance->name}");

        // ====================================================================
        // DOSSIERS FACTURES
        // ====================================================================
        $this->command->info("\n💰 Création des dossiers FACTURES...");

        $invoicesRoot = $this->service->createFolder(
            $types['INVOICES'],
            [
                'name' => 'Factures 2025',
                'description' => 'Factures de l\'année 2025',
                'metadata' => [
                    'invoice_number' => 'ALL-2025',
                    'invoice_date' => '2025-01-01',
                    'amount' => '0',
                ],
            ],
            $user,
            $organisation
        );
        $this->command->info("   ✓ Créé: {$invoicesRoot->code} - {$invoicesRoot->name}");

        // Par trimestre
        $invoicesQ1 = $this->service->createFolder(
            $types['INVOICES'],
            [
                'name' => 'Trimestre 1 - 2025',
                'description' => 'Factures du premier trimestre',
                'metadata' => [
                    'invoice_number' => 'Q1-2025',
                    'invoice_date' => '2025-01-01',
                    'amount' => '0',
                ],
            ],
            $user,
            $organisation,
            $invoicesRoot
        );
        $this->command->info("   ✓ Créé: {$invoicesQ1->code} - {$invoicesQ1->name}");

        $invoicesQ2 = $this->service->createFolder(
            $types['INVOICES'],
            [
                'name' => 'Trimestre 2 - 2025',
                'description' => 'Factures du deuxième trimestre',
                'metadata' => [
                    'invoice_number' => 'Q2-2025',
                    'invoice_date' => '2025-04-01',
                    'amount' => '0',
                ],
            ],
            $user,
            $organisation,
            $invoicesRoot
        );
        $this->command->info("   ✓ Créé: {$invoicesQ2->code} - {$invoicesQ2->name}");

        // ====================================================================
        // DOSSIERS COMPTABILITÉ
        // ====================================================================
        $this->command->info("\n📊 Création des dossiers COMPTABILITÉ...");

        $accountingRoot = $this->service->createFolder(
            $types['ACCOUNTING'],
            [
                'name' => 'Comptabilité 2025',
                'description' => 'Documents comptables 2025',
                'metadata' => [
                    'fiscal_year' => '2025',
                    'period' => 'Année complète',
                    'account_number' => 'ALL',
                ],
                'access_level' => 'confidential',
            ],
            $user,
            $organisation
        );
        $this->command->info("   ✓ Créé: {$accountingRoot->code} - {$accountingRoot->name}");

        // Sous-dossiers par type
        $accountingJournals = $this->service->createFolder(
            $types['ACCOUNTING'],
            [
                'name' => 'Journaux Comptables',
                'description' => 'Journaux et écritures comptables',
                'metadata' => [
                    'fiscal_year' => '2025',
                    'period' => 'Mensuel',
                    'account_number' => 'JOURNALS',
                ],
            ],
            $user,
            $organisation,
            $accountingRoot
        );
        $this->command->info("   ✓ Créé: {$accountingJournals->code} - {$accountingJournals->name}");

        $accountingReports = $this->service->createFolder(
            $types['ACCOUNTING'],
            [
                'name' => 'Rapports Financiers',
                'description' => 'Bilans et rapports financiers',
                'metadata' => [
                    'fiscal_year' => '2025',
                    'period' => 'Trimestriel',
                    'account_number' => 'REPORTS',
                ],
            ],
            $user,
            $organisation,
            $accountingRoot
        );
        $this->command->info("   ✓ Créé: {$accountingReports->code} - {$accountingReports->name}");

        // ====================================================================
        // DOSSIERS PROJETS
        // ====================================================================
        $this->command->info("\n🗂️  Création des dossiers PROJETS...");

        $projectsRoot = $this->service->createFolder(
            $types['PROJECTS'],
            [
                'name' => 'Projets 2025',
                'description' => 'Tous les projets en cours',
                'metadata' => [
                    'project_id' => 'ALL',
                    'project_name' => 'Tous les projets',
                    'start_date' => '2025-01-01',
                ],
            ],
            $user,
            $organisation
        );
        $this->command->info("   ✓ Créé: {$projectsRoot->code} - {$projectsRoot->name}");

        // Projet spécifique
        $projectSpecKit = $this->service->createFolder(
            $types['PROJECTS'],
            [
                'name' => 'Projet SpecKit',
                'description' => 'Documentation du projet SpecKit',
                'metadata' => [
                    'project_id' => 'SPECKIT-2025',
                    'project_name' => 'SpecKit Implementation',
                    'start_date' => '2025-01-15',
                ],
            ],
            $user,
            $organisation,
            $projectsRoot
        );
        $this->command->info("   ✓ Créé: {$projectSpecKit->code} - {$projectSpecKit->name}");

        // Sous-dossiers projet
        $projectSpecKitDocs = $this->service->createFolder(
            $types['PROJECTS'],
            [
                'name' => 'Documentation Technique',
                'description' => 'Documentation technique du projet',
                'metadata' => [
                    'project_id' => 'SPECKIT-2025-DOCS',
                    'project_name' => 'SpecKit Docs',
                    'start_date' => '2025-01-15',
                ],
            ],
            $user,
            $organisation,
            $projectSpecKit
        );
        $this->command->info("   ✓ Créé: {$projectSpecKitDocs->code} - {$projectSpecKitDocs->name}");

        // ====================================================================
        // STATISTIQUES FINALES
        // ====================================================================
        $totalFolders = RecordDigitalFolder::count();
        $rootFolders = RecordDigitalFolder::roots()->count();
        $subFolders = $totalFolders - $rootFolders;

        $this->command->info("\n✅ Seed terminé!");
        $this->command->info("   📁 {$totalFolders} dossiers créés");
        $this->command->info("   🌳 {$rootFolders} dossiers racines");
        $this->command->info("   📂 {$subFolders} sous-dossiers");
        $this->command->info("\n🎉 Phase 4 (Digital Folders) terminée avec succès!\n");
    }
}



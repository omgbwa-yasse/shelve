<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolder;
use App\Models\Attachment;
use App\Models\User;
use App\Models\Organisation;
use App\Services\RecordDigitalDocumentService;

class MigrateDocumentsSeeder extends Seeder
{
    private RecordDigitalDocumentService $service;
    private User $creator;
    private Organisation $organisation;

    public function __construct()
    {
        $this->service = new RecordDigitalDocumentService();
    }

    public function run(): void
    {
        echo "\n🚀 Création des documents numériques (Phase 5)...\n\n";

        // Récupérer utilisateur et organisation
        $this->creator = User::first();
        $this->organisation = Organisation::first();

        if (!$this->creator || !$this->organisation) {
            echo "❌ ERREUR: Utilisateur ou Organisation introuvable\n";
            return;
        }

        // Nettoyage
        echo "🧹 Nettoyage des documents existants...\n";
        RecordDigitalDocument::query()->forceDelete();
        echo "   ✓ Documents précédents supprimés\n\n";

        // Créer des attachments de test
        $attachments = $this->createTestAttachments();

        // Créer documents par type de dossier
        $this->createContractDocuments($attachments);
        $this->createInvoiceDocuments($attachments);
        $this->createHRDocuments($attachments);
        $this->createAccountingDocuments($attachments);
        $this->createProjectDocuments($attachments);

        echo "\n✅ Seed terminé!\n";
        echo "   📄 " . RecordDigitalDocument::where('is_current_version', true)->count() . " documents créés\n";
        echo "   📋 " . RecordDigitalDocument::where('is_current_version', false)->count() . " versions anciennes\n";
        echo "\n🎉 Phase 5 (Digital Documents) terminée avec succès!\n\n";
    }

    /**
     * Créer des attachments de test
     */
    private function createTestAttachments(): array
    {
        $attachments = [];

        // PDF files
        for ($i = 1; $i <= 12; $i++) {
            $attachments["pdf_{$i}"] = Attachment::create([
                'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
                'name' => "document_{$i}.pdf",
                'path' => "documents/document_{$i}.pdf",
                'crypt' => "doc_{$i}_" . md5("document_{$i}.pdf"),
                'crypt_sha512' => hash('sha512', "document_{$i}.pdf"),
                'mime_type' => 'application/pdf',
                'size' => rand(100000, 5000000), // 100KB - 5MB
                'creator_id' => $this->creator->id,
                'file_extension' => 'pdf',
            ]);
        }

        // DOCX files
        for ($i = 1; $i <= 3; $i++) {
            $attachments["docx_{$i}"] = Attachment::create([
                'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
                'name' => "document_{$i}.docx",
                'path' => "documents/document_{$i}.docx",
                'crypt' => "docx_{$i}_" . md5("document_{$i}.docx"),
                'crypt_sha512' => hash('sha512', "document_{$i}.docx"),
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'size' => rand(50000, 2000000),
                'creator_id' => $this->creator->id,
                'file_extension' => 'docx',
            ]);
        }

        // XLSX files
        for ($i = 1; $i <= 2; $i++) {
            $attachments["xlsx_{$i}"] = Attachment::create([
                'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
                'name' => "spreadsheet_{$i}.xlsx",
                'path' => "documents/spreadsheet_{$i}.xlsx",
                'crypt' => "xlsx_{$i}_" . md5("spreadsheet_{$i}.xlsx"),
                'crypt_sha512' => hash('sha512', "spreadsheet_{$i}.xlsx"),
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'size' => rand(80000, 3000000),
                'creator_id' => $this->creator->id,
                'file_extension' => 'xlsx',
            ]);
        }

        return $attachments;
    }

    /**
     * Documents de type CONTRAT
     */
    private function createContractDocuments(array $attachments): void
    {
        echo "📝 Création des CONTRATS...\n";

        $contractType = RecordDigitalDocumentType::where('code', 'CONTRACT')->first();
        $contractsFolder = RecordDigitalFolder::where('name', 'Contrats Clients')->first();

        if (!$contractType || !$contractsFolder) {
            echo "   ⚠️  Type CONTRACT ou dossier introuvable, ignoré\n\n";
            return;
        }

        // Contrat 1: avec signature et version
        $contract1 = $this->service->createDocument(
            $contractType,
            $contractsFolder,
            [
                'name' => 'Contrat Service Cloud',
                'description' => 'Contrat de services cloud computing pour 3 ans',
                'access_level' => 'confidential',
                'status' => 'active',
                'document_date' => '2025-01-15',
                'metadata' => [
                    'contract_party' => 'CloudTech Solutions',
                    'start_date' => '2025-01-15',
                    'expiry_date' => '2028-01-14',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['pdf_1']
        );
        echo "   ✓ Créé: {$contract1->code} - {$contract1->name}\n";

        // Signer le contrat
        $this->service->signDocument($contract1, $this->creator, [
            'method' => 'digital_signature',
            'certificate' => 'SHA256',
        ]);
        $this->service->approveDocument($contract1, $this->creator, 'Approuvé par la direction');
        echo "   ✓ Signé et approuvé\n";

        // NOTE: Versioning désactivé car contrainte UNIQUE sur code empêche versions multiples
        // TODO: Modifier migration pour permettre versions avec même code (unique sur code + version_number)
        // $contract1v2 = $this->service->createVersion(...);
        echo "   ⚠️  Versioning ignoré (contrainte DB)\n";

        // Contrat 2: simple
        $contract2 = $this->service->createDocument(
            $contractType,
            $contractsFolder,
            [
                'name' => 'Contrat Maintenance Informatique',
                'description' => 'Contrat de maintenance annuelle',
                'access_level' => 'confidential',
                'status' => 'active',
                'document_date' => '2025-02-01',
                'metadata' => [
                    'contract_party' => 'IT Services Corp',
                    'start_date' => '2025-02-01',
                    'expiry_date' => '2026-01-31',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['pdf_3']
        );
        echo "   ✓ Créé: {$contract2->code} - {$contract2->name}\n";

        // Signer le contrat 2
        $this->service->signDocument($contract2, $this->creator, [
            'method' => 'digital_signature',
            'certificate' => 'SHA256',
        ]);
        $this->service->approveDocument($contract2, $this->creator, 'Approuvé par le département IT');
        echo "   ✓ Signé et approuvé\n\n";
    }

    /**
     * Documents de type FACTURE
     */
    private function createInvoiceDocuments(array $attachments): void
    {
        echo "💰 Création des FACTURES...\n";

        $invoiceType = RecordDigitalDocumentType::where('code', 'INVOICE')->first();
        $invoicesFolder = RecordDigitalFolder::where('name', 'Trimestre 1 - 2025')->first();

        if (!$invoiceType || !$invoicesFolder) {
            echo "   ⚠️  Type INVOICE ou dossier introuvable, ignoré\n\n";
            return;
        }

        // Facture 1
        $invoice1 = $this->service->createDocument(
            $invoiceType,
            $invoicesFolder,
            [
                'name' => 'Facture F-2025-001',
                'description' => 'Facture client TechCorp',
                'access_level' => 'internal',
                'status' => 'active',
                'document_date' => '2025-01-10',
                'metadata' => [
                    'invoice_number' => 'F-2025-001',
                    'invoice_date' => '2025-01-10',
                    'amount' => '15000',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['pdf_4']
        );
        echo "   ✓ Créé: {$invoice1->code} - {$invoice1->name}\n";

        // Facture 2
        $invoice2 = $this->service->createDocument(
            $invoiceType,
            $invoicesFolder,
            [
                'name' => 'Facture F-2025-002',
                'description' => 'Facture client InnoSoft',
                'access_level' => 'internal',
                'status' => 'active',
                'document_date' => '2025-01-20',
                'metadata' => [
                    'invoice_number' => 'F-2025-002',
                    'invoice_date' => '2025-01-20',
                    'amount' => '8500',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['pdf_5']
        );
        echo "   ✓ Créé: {$invoice2->code} - {$invoice2->name}\n";

        // Facture 3
        $invoice3 = $this->service->createDocument(
            $invoiceType,
            $invoicesFolder,
            [
                'name' => 'Facture F-2025-003',
                'description' => 'Facture client DataPro',
                'access_level' => 'internal',
                'status' => 'active',
                'document_date' => '2025-02-05',
                'metadata' => [
                    'invoice_number' => 'F-2025-003',
                    'invoice_date' => '2025-02-05',
                    'amount' => '12000',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['pdf_6']
        );
        echo "   ✓ Créé: {$invoice3->code} - {$invoice3->name}\n\n";
    }

    /**
     * Documents RH (PAYSLIP)
     */
    private function createHRDocuments(array $attachments): void
    {
        echo "👥 Création des documents RH...\n";

        $payslipType = RecordDigitalDocumentType::where('code', 'PAYSLIP')->first();
        $hrFolder = RecordDigitalFolder::where('name', 'Département IT')->first();

        if (!$payslipType || !$hrFolder) {
            echo "   ⚠️  Type PAYSLIP ou dossier introuvable, ignoré\n\n";
            return;
        }

        // Fiche de paie 1
        $payslip1 = $this->service->createDocument(
            $payslipType,
            $hrFolder,
            [
                'name' => 'Fiche de paie - Janvier 2025 - Dupont',
                'description' => 'Bulletin de salaire Jean Dupont',
                'access_level' => 'confidential',
                'status' => 'draft',
                'document_date' => '2025-01-31',
                'metadata' => [
                    'employee_id' => 'EMP001',
                    'month' => '2025-01',
                    'gross_salary' => '4500',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['pdf_7']
        );
        $this->service->approveDocument($payslip1, $this->creator, 'Validé RH');
        echo "   ✓ Créé: {$payslip1->code} - {$payslip1->name}\n\n";
    }

    /**
     * Documents COMPTABILITÉ
     */
    private function createAccountingDocuments(array $attachments): void
    {
        echo "📊 Création des documents comptables...\n";

        $reportType = RecordDigitalDocumentType::where('code', 'REPORT')->first();
        $accountingFolder = RecordDigitalFolder::where('name', 'Rapports Financiers')->first();

        if (!$reportType || !$accountingFolder) {
            echo "   ⚠️  Type REPORT ou dossier introuvable, ignoré\n\n";
            return;
        }

        // Rapport 1: avec versioning
        $report1 = $this->service->createDocument(
            $reportType,
            $accountingFolder,
            [
                'name' => 'Rapport Financier T1 2025',
                'description' => 'Rapport financier trimestriel',
                'access_level' => 'confidential',
                'status' => 'draft',
                'document_date' => '2025-03-31',
                'metadata' => [
                    'period' => 'Q1-2025',
                    'report_type' => 'financial',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['docx_1']
        );
        echo "   ✓ Créé: {$report1->code} - {$report1->name}\n";

        // NOTE: Versioning désactivé (contrainte UNIQUE sur code)
        // TODO: Activer après modification de la migration
        echo "   ⚠️  Versioning ignoré (contrainte DB)\n\n";
    }

    /**
     * Documents PROJETS
     */
    private function createProjectDocuments(array $attachments): void
    {
        echo "🗂️  Création des documents projet...\n";

        $memoType = RecordDigitalDocumentType::where('code', 'MEMO')->first();
        $projectFolder = RecordDigitalFolder::where('name', 'Documentation Technique')->first();

        if (!$memoType || !$projectFolder) {
            echo "   ⚠️  Type MEMO ou dossier introuvable, ignoré\n\n";
            return;
        }

        // Mémo 1
        $memo1 = $this->service->createDocument(
            $memoType,
            $projectFolder,
            [
                'name' => 'Spécifications Techniques SpecKit',
                'description' => 'Document de spécifications',
                'access_level' => 'internal',
                'status' => 'active',
                'document_date' => '2025-01-05',
                'metadata' => [
                    'project' => 'SpecKit',
                    'author' => 'Équipe Dev',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['docx_3']
        );
        echo "   ✓ Créé: {$memo1->code} - {$memo1->name}\n";

        // Mémo 2
        $memo2 = $this->service->createDocument(
            $memoType,
            $projectFolder,
            [
                'name' => 'Plan de Tests Phase 5',
                'description' => 'Plan de tests pour documents numériques',
                'access_level' => 'internal',
                'status' => 'draft',
                'document_date' => '2025-02-15',
                'metadata' => [
                    'project' => 'SpecKit',
                    'author' => 'QA Team',
                ],
            ],
            $this->creator,
            $this->organisation,
            $attachments['pdf_9']
        );
        echo "   ✓ Créé: {$memo2->code} - {$memo2->name}\n\n";
    }
}

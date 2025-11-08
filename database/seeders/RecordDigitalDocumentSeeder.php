<?php

namespace Database\Seeders;

use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\Activity;
use App\Models\Organisation;
use App\Models\Author;
use App\Models\Keyword;
use App\Models\ThesaurusConcept;
use Illuminate\Database\Seeder;

class RecordDigitalDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les données de référence
        $levels = RecordLevel::all();
        $statuses = RecordStatus::all();
        $activities = Activity::all();
        $organisations = Organisation::all();
        $authors = Author::take(15)->get();
        $keywords = Keyword::take(25)->get();
        $concepts = ThesaurusConcept::take(20)->get();
        $folders = RecordDigitalFolder::all();

        if ($levels->isEmpty() || $statuses->isEmpty() || $organisations->isEmpty()) {
            $this->command->warn('⚠️  Veuillez d\'abord exécuter les seeders de base (RecordLevel, RecordStatus, Organisation)');
            return;
        }

        if ($folders->isEmpty()) {
            $this->command->warn('⚠️  Veuillez d\'abord exécuter RecordDigitalFolderSeeder');
            return;
        }

        $this->command->info('📄 Création des documents numériques...');

        // Documents pour le dossier RH - Recrutement
        $folderRecrutement = $folders->where('code', 'DN-RH-2024-001-01')->first();
        if ($folderRecrutement) {
            $doc1 = $this->createDocument([
                'code' => 'DOC-RH-REC-001',
                'name' => 'Offre d\'emploi - Développeur Senior',
                'description' => 'Offre d\'emploi pour un poste de développeur senior full-stack',
                'date_start' => '2024-02-01',
                'date_end' => '2024-03-15',
                'folder_id' => $folderRecrutement->id,
                'organisation_id' => $folderRecrutement->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/rh/offre-emploi-dev-senior.pdf',
                'file_name' => 'offre-emploi-dev-senior.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 245678,
                'version' => 1,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);

            $doc2 = $this->createDocument([
                'code' => 'DOC-RH-REC-002',
                'name' => 'Liste des candidatures reçues',
                'description' => 'Tableau récapitulatif des candidatures pour le poste de développeur',
                'date_start' => '2024-02-15',
                'date_end' => '2024-03-15',
                'folder_id' => $folderRecrutement->id,
                'organisation_id' => $folderRecrutement->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/rh/candidatures-recues.xlsx',
                'file_name' => 'candidatures-recues.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => 89543,
                'version' => 1,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);

            $doc3 = $this->createDocument([
                'code' => 'DOC-RH-REC-003',
                'name' => 'Grilles d\'évaluation entretiens',
                'description' => 'Formulaires d\'évaluation des candidats lors des entretiens',
                'date_start' => '2024-03-01',
                'date_end' => '2024-03-30',
                'folder_id' => $folderRecrutement->id,
                'organisation_id' => $folderRecrutement->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/rh/grilles-evaluation.docx',
                'file_name' => 'grilles-evaluation.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_size' => 156234,
                'version' => 2,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);
        }

        // Documents pour le dossier Formation
        $folderFormation = $folders->where('code', 'DN-RH-2024-001-02')->first();
        if ($folderFormation) {
            $this->createDocument([
                'code' => 'DOC-RH-FORM-001',
                'name' => 'Plan de formation annuel 2024',
                'description' => 'Document de planification des formations pour l\'année 2024',
                'date_start' => '2024-01-15',
                'date_end' => '2024-12-31',
                'folder_id' => $folderFormation->id,
                'organisation_id' => $folderFormation->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/rh/plan-formation-2024.pdf',
                'file_name' => 'plan-formation-2024.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 567890,
                'version' => 1,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);

            $this->createDocument([
                'code' => 'DOC-RH-FORM-002',
                'name' => 'Budget formation départemental',
                'description' => 'Répartition budgétaire pour les formations par département',
                'date_start' => '2024-01-20',
                'date_end' => '2024-12-31',
                'folder_id' => $folderFormation->id,
                'organisation_id' => $folderFormation->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/rh/budget-formation.xlsx',
                'file_name' => 'budget-formation.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => 78456,
                'version' => 1,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);
        }

        // Documents pour le dossier Comptabilité - Factures
        $folderFactures = $folders->where('code', 'DN-COMPTA-2024-001-01')->first();
        if ($folderFactures) {
            $this->createDocument([
                'code' => 'DOC-COMPTA-FAC-001',
                'name' => 'Facture Fournisseur - Matériel Informatique',
                'description' => 'Facture d\'achat de matériel informatique - Janvier 2024',
                'date_start' => '2024-01-15',
                'date_end' => '2024-01-15',
                'folder_id' => $folderFactures->id,
                'organisation_id' => $folderFactures->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/comptabilite/facture-001-materiel.pdf',
                'file_name' => 'facture-001-materiel.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 123456,
                'version' => 1,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);

            $this->createDocument([
                'code' => 'DOC-COMPTA-FAC-002',
                'name' => 'Facture Fournisseur - Prestations de Service',
                'description' => 'Facture de prestation de service - Février 2024',
                'date_start' => '2024-02-10',
                'date_end' => '2024-02-10',
                'folder_id' => $folderFactures->id,
                'organisation_id' => $folderFactures->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/comptabilite/facture-002-services.pdf',
                'file_name' => 'facture-002-services.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 98765,
                'version' => 1,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);

            $this->createDocument([
                'code' => 'DOC-COMPTA-FAC-003',
                'name' => 'Registre des factures Q1 2024',
                'description' => 'Registre récapitulatif des factures du premier trimestre 2024',
                'date_start' => '2024-01-01',
                'date_end' => '2024-03-31',
                'folder_id' => $folderFactures->id,
                'organisation_id' => $folderFactures->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/comptabilite/registre-factures-q1.xlsx',
                'file_name' => 'registre-factures-q1.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => 234567,
                'version' => 1,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);
        }

        // Documents pour le dossier Projets - Analyses
        $folderAnalyses = $folders->where('code', 'DN-PROJ-2024-001-01')->first();
        if ($folderAnalyses) {
            $this->createDocument([
                'code' => 'DOC-PROJ-ANA-001',
                'name' => 'Étude de faisabilité - Digitalisation Archives',
                'description' => 'Analyse complète pour le projet de digitalisation des archives physiques',
                'date_start' => '2024-03-15',
                'date_end' => '2024-04-30',
                'folder_id' => $folderAnalyses->id,
                'organisation_id' => $folderAnalyses->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/projets/etude-faisabilite-digitalisation.pdf',
                'file_name' => 'etude-faisabilite-digitalisation.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1234567,
                'version' => 3,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);

            $this->createDocument([
                'code' => 'DOC-PROJ-ANA-002',
                'name' => 'Analyse des coûts du projet',
                'description' => 'Estimation détaillée des coûts du projet de transformation digitale',
                'date_start' => '2024-04-01',
                'date_end' => '2024-04-15',
                'folder_id' => $folderAnalyses->id,
                'organisation_id' => $folderAnalyses->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/projets/analyse-couts.xlsx',
                'file_name' => 'analyse-couts.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'file_size' => 345678,
                'version' => 2,
                'is_approved' => true,
            ], $authors, $keywords, $concepts);

            $this->createDocument([
                'code' => 'DOC-PROJ-ANA-003',
                'name' => 'Rapport d\'audit technique',
                'description' => 'Audit de l\'infrastructure technique existante',
                'date_start' => '2024-03-20',
                'date_end' => '2024-04-10',
                'folder_id' => $folderAnalyses->id,
                'organisation_id' => $folderAnalyses->organisation_id,
                'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
                'status_id' => $statuses->first()->id,
                'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
                'file_path' => 'documents/projets/audit-technique.pdf',
                'file_name' => 'audit-technique.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 876543,
                'version' => 1,
                'is_approved' => false,
            ], $authors, $keywords, $concepts);
        }

        // Quelques documents orphelins (sans dossier parent)
        $this->createDocument([
            'code' => 'DOC-MISC-001',
            'name' => 'Politique de sécurité informatique',
            'description' => 'Document définissant les règles de sécurité informatique de l\'organisation',
            'date_start' => '2024-01-10',
            'date_end' => null,
            'folder_id' => null,
            'organisation_id' => $organisations->random()->id,
            'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
            'status_id' => $statuses->first()->id,
            'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
            'file_path' => 'documents/misc/politique-securite-it.pdf',
            'file_name' => 'politique-securite-it.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 456789,
            'version' => 1,
            'is_approved' => true,
        ], $authors, $keywords, $concepts);

        $this->createDocument([
            'code' => 'DOC-MISC-002',
            'name' => 'Charte d\'utilisation du système d\'information',
            'description' => 'Règles d\'usage du SI pour tous les employés',
            'date_start' => '2024-01-15',
            'date_end' => null,
            'folder_id' => null,
            'organisation_id' => $organisations->random()->id,
            'level_id' => $levels->where('code', 'item')->first()?->id ?? $levels->last()->id,
            'status_id' => $statuses->first()->id,
            'activity_id' => $activities->isNotEmpty() ? $activities->random()->id : null,
            'file_path' => 'documents/misc/charte-si.pdf',
            'file_name' => 'charte-si.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 234567,
            'version' => 2,
            'is_approved' => true,
        ], $authors, $keywords, $concepts);

        $this->command->info('✅ ' . RecordDigitalDocument::count() . ' documents numériques créés avec succès');
    }

    /**
     * Helper method to create a document with relations
     */
    private function createDocument(array $data, $authors, $keywords, $concepts)
    {
        $document = RecordDigitalDocument::create($data);

        // Attach random authors (1-3)
        if ($authors->isNotEmpty()) {
            $document->authors()->attach(
                $authors->random(min(rand(1, 3), $authors->count()))->pluck('id')->toArray()
            );
        }

        // Attach random keywords (2-5)
        if ($keywords->isNotEmpty()) {
            $document->keywords()->attach(
                $keywords->random(min(rand(2, 5), $keywords->count()))->pluck('id')->toArray()
            );
        }

        // Attach random concepts (1-3)
        if ($concepts->isNotEmpty()) {
            $document->thesaurusConcepts()->attach(
                $concepts->random(min(rand(1, 3), $concepts->count()))->pluck('id')->toArray()
            );
        }

        return $document;
    }
}

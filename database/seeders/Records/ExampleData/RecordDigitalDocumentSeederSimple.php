<?php

namespace Database\Seeders\Records\ExampleData;

use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolder;
use App\Models\Organisation;
use App\Models\User;
use App\Models\Keyword;
use App\Models\ThesaurusConcept;
use Illuminate\Database\Seeder;

class RecordDigitalDocumentSeederSimple extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les données de référence
        $folders = RecordDigitalFolder::all();
        $organisations = Organisation::all();
        $keywords = Keyword::take(25)->get();
        $concepts = ThesaurusConcept::take(20)->get();
        $user = User::first();

        if ($folders->isEmpty() || $organisations->isEmpty() || !$user) {
            $this->command->warn('⚠️  Veuillez d\'abord exécuter RecordDigitalFolderSeeder et SuperadminSeeder');
            return;
        }

        // Créer un type de document par défaut si nécessaire
        $documentType = RecordDigitalDocumentType::firstOrCreate(
            ['code' => 'GENERAL'],
            [
                'name' => 'Document Général',
                'description' => 'Type de document par défaut pour les tests',
                'is_active' => true,
            ]
        );

        $this->command->info('📄 Création des documents numériques...');

        // Documents pour différents dossiers
        $folderRecrutement = $folders->where('code', 'DN-RH-2024-001-01')->first();
        if ($folderRecrutement) {
            $this->createDocument([
                'code' => 'DOC-RH-REC-001',
                'name' => 'Offre d\'emploi - Développeur Senior',
                'description' => 'Offre d\'emploi pour un poste de développeur senior full-stack',
                'type_id' => $documentType->id,
                'folder_id' => $folderRecrutement->id,
                'organisation_id' => $folderRecrutement->organisation_id,
                'creator_id' => $user->id,
                'status' => 'active',
                'version_number' => 1,
            ], $keywords, $concepts);

            $this->createDocument([
                'code' => 'DOC-RH-REC-002',
                'name' => 'Liste des candidatures reçues',
                'description' => 'Tableau récapitulatif des candidatures pour le poste',
                'type_id' => $documentType->id,
                'folder_id' => $folderRecrutement->id,
                'organisation_id' => $folderRecrutement->organisation_id,
                'creator_id' => $user->id,
                'status' => 'active',
                'version_number' => 1,
            ], $keywords, $concepts);
        }

        $folderFormation = $folders->where('code', 'DN-RH-2024-001-02')->first();
        if ($folderFormation) {
            $this->createDocument([
                'code' => 'DOC-RH-FORM-001',
                'name' => 'Plan de formation annuel 2024',
                'description' => 'Document de planification des formations pour l\'année 2024',
                'type_id' => $documentType->id,
                'folder_id' => $folderFormation->id,
                'organisation_id' => $folderFormation->organisation_id,
                'creator_id' => $user->id,
                'status' => 'active',
                'version_number' => 1,
            ], $keywords, $concepts);
        }

        $folderFactures = $folders->where('code', 'DN-COMPTA-2024-001-01')->first();
        if ($folderFactures) {
            $this->createDocument([
                'code' => 'DOC-COMPTA-FAC-001',
                'name' => 'Facture Fournisseur - Matériel Informatique',
                'description' => 'Facture d\'achat de matériel informatique - Janvier 2024',
                'type_id' => $documentType->id,
                'folder_id' => $folderFactures->id,
                'organisation_id' => $folderFactures->organisation_id,
                'creator_id' => $user->id,
                'status' => 'active',
                'version_number' => 1,
            ], $keywords, $concepts);

            $this->createDocument([
                'code' => 'DOC-COMPTA-FAC-002',
                'name' => 'Registre des factures Q1 2024',
                'description' => 'Registre récapitulatif des factures du premier trimestre',
                'type_id' => $documentType->id,
                'folder_id' => $folderFactures->id,
                'organisation_id' => $folderFactures->organisation_id,
                'creator_id' => $user->id,
                'status' => 'active',
                'version_number' => 1,
            ], $keywords, $concepts);
        }

        $folderAnalyses = $folders->where('code', 'DN-PROJ-2024-001-01')->first();
        if ($folderAnalyses) {
            $this->createDocument([
                'code' => 'DOC-PROJ-ANA-001',
                'name' => 'Étude de faisabilité - Digitalisation Archives',
                'description' => 'Analyse complète pour le projet de digitalisation des archives physiques',
                'type_id' => $documentType->id,
                'folder_id' => $folderAnalyses->id,
                'organisation_id' => $folderAnalyses->organisation_id,
                'creator_id' => $user->id,
                'status' => 'active',
                'version_number' => 3,
            ], $keywords, $concepts);
        }

        // Quelques documents orphelins (sans dossier parent)
        $this->createDocument([
            'code' => 'DOC-MISC-001',
            'name' => 'Politique de sécurité informatique',
            'description' => 'Document définissant les règles de sécurité informatique',
            'type_id' => $documentType->id,
            'folder_id' => null,
            'organisation_id' => $organisations->random()->id,
            'creator_id' => $user->id,
            'status' => 'active',
            'version_number' => 1,
        ], $keywords, $concepts);

        $this->createDocument([
            'code' => 'DOC-MISC-002',
            'name' => 'Charte d\'utilisation du système d\'information',
            'description' => 'Règles d\'usage du SI pour tous les employés',
            'type_id' => $documentType->id,
            'folder_id' => null,
            'organisation_id' => $organisations->random()->id,
            'creator_id' => $user->id,
            'status' => 'active',
            'version_number' => 2,
        ], $keywords, $concepts);

        $this->command->info('✅ ' . RecordDigitalDocument::count() . ' documents numériques créés avec succès');
    }

    private function createDocument(array $data, $keywords, $concepts)
    {
        // Utiliser firstOrCreate pour éviter les doublons
        $code = $data['code'];
        unset($data['code']);

        $document = RecordDigitalDocument::firstOrCreate(
            ['code' => $code],
            $data
        );

        // Nettoyer les relations existantes
        $document->keywords()->detach();
        $document->thesaurusConcepts()->detach();

        // Attach random keywords
        if ($keywords->isNotEmpty()) {
            $document->keywords()->attach(
                $keywords->random(min(rand(2, 5), $keywords->count()))->pluck('id')->toArray()
            );
        }

        // Attach random concepts
        if ($concepts->isNotEmpty()) {
            $document->thesaurusConcepts()->attach(
                $concepts->random(min(rand(1, 3), $concepts->count()))->pluck('id')->toArray()
            );
        }

        return $document;
    }
}



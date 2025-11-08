<?php

namespace Database\Seeders;

use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalFolderType;
use App\Models\Organisation;
use App\Models\User;
use App\Models\Keyword;
use App\Models\ThesaurusConcept;
use Illuminate\Database\Seeder;

class RecordDigitalFolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les données de référence
        $organisations = Organisation::all();
        $keywords = Keyword::take(20)->get();
        $concepts = ThesaurusConcept::take(15)->get();

        if ($organisations->isEmpty()) {
            $this->command->warn('⚠️  Veuillez d\'abord exécuter OrganisationSeeder');
            return;
        }

        // Créer un type de dossier par défaut si nécessaire
        $folderType = RecordDigitalFolderType::firstOrCreate(
            ['code' => 'GENERAL'],
            [
                'name' => 'Dossier Général',
                'description' => 'Type de dossier par défaut pour les tests',
                'is_active' => true,
            ]
        );

        // Créer un utilisateur par défaut si nécessaire
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Seeders',
                'email' => 'admin.seeders@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        $this->command->info('📁 Création des dossiers numériques...');

        // Dossier parent 1 - RH
        $folderRH = $this->createFolder([
            'code' => 'DN-RH-2024-001',
            'name' => 'Gestion des Ressources Humaines 2024',
            'description' => 'Dossier principal regroupant tous les documents RH de l\'année 2024',
            'type_id' => $folderType->id,
            'organisation_id' => $organisations->random()->id,
            'creator_id' => $user->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'active',
        ], $keywords, $concepts);

        // Sous-dossier 1.1 - Recrutement
        $this->createFolder([
            'code' => 'DN-RH-2024-001-01',
            'name' => 'Processus de Recrutement',
            'description' => 'Candidatures et entretiens pour les postes ouverts en 2024',
            'type_id' => $folderType->id,
            'organisation_id' => $folderRH->organisation_id,
            'creator_id' => $user->id,
            'parent_id' => $folderRH->id,
            'start_date' => '2024-01-15',
            'end_date' => '2024-06-30',
            'status' => 'active',
        ], $keywords, $concepts);

        // Sous-dossier 1.2 - Formation
        $this->createFolder([
            'code' => 'DN-RH-2024-001-02',
            'name' => 'Plans de Formation',
            'description' => 'Plans de formation continue et développement des compétences',
            'type_id' => $folderType->id,
            'organisation_id' => $folderRH->organisation_id,
            'creator_id' => $user->id,
            'parent_id' => $folderRH->id,
            'start_date' => '2024-02-01',
            'end_date' => '2024-11-30',
            'status' => 'active',
        ], $keywords, $concepts);

        // Dossier parent 2 - Comptabilité
        $folderCompta = $this->createFolder([
            'code' => 'DN-COMPTA-2024-001',
            'name' => 'Comptabilité Générale 2024',
            'description' => 'Documents comptables et financiers de l\'exercice 2024',
            'type_id' => $folderType->id,
            'organisation_id' => $organisations->random()->id,
            'creator_id' => $user->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'active',
        ], $keywords, $concepts);

        // Sous-dossier 2.1 - Factures
        $this->createFolder([
            'code' => 'DN-COMPTA-2024-001-01',
            'name' => 'Factures Fournisseurs',
            'description' => 'Ensemble des factures fournisseurs de l\'année 2024',
            'type_id' => $folderType->id,
            'organisation_id' => $folderCompta->organisation_id,
            'creator_id' => $user->id,
            'parent_id' => $folderCompta->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'active',
        ], $keywords, $concepts);

        // Dossier parent 3 - Projets
        $folderProjets = $this->createFolder([
            'code' => 'DN-PROJ-2024-001',
            'name' => 'Projets de Transformation Digitale',
            'description' => 'Documentation des projets de transformation numérique',
            'type_id' => $folderType->id,
            'organisation_id' => $organisations->random()->id,
            'creator_id' => $user->id,
            'start_date' => '2024-03-01',
            'end_date' => '2025-02-28',
            'status' => 'active',
        ], $keywords, $concepts);

        // Sous-dossier 3.1 - Analyses
        $this->createFolder([
            'code' => 'DN-PROJ-2024-001-01',
            'name' => 'Analyses et Études de Faisabilité',
            'description' => 'Documents d\'analyse préalable aux projets',
            'type_id' => $folderType->id,
            'organisation_id' => $folderProjets->organisation_id,
            'creator_id' => $user->id,
            'parent_id' => $folderProjets->id,
            'start_date' => '2024-03-01',
            'end_date' => '2024-05-31',
            'status' => 'active',
        ], $keywords, $concepts);

        $this->command->info('✅ ' . RecordDigitalFolder::count() . ' dossiers numériques créés avec succès');
    }

    private function createFolder(array $data, $keywords, $concepts)
    {
        // Utiliser firstOrCreate pour éviter les doublons
        $code = $data['code'];
        unset($data['code']);
        
        $folder = RecordDigitalFolder::firstOrCreate(
            ['code' => $code],
            $data
        );

        // Nettoyer les relations existantes
        $folder->keywords()->detach();
        $folder->thesaurusConcepts()->detach();

        // Attach random keywords
        if ($keywords->isNotEmpty()) {
            $folder->keywords()->attach(
                $keywords->random(min(rand(2, 5), $keywords->count()))->pluck('id')->toArray()
            );
        }

        // Attach random concepts
        if ($concepts->isNotEmpty()) {
            $folder->thesaurusConcepts()->attach(
                $concepts->random(min(rand(1, 3), $concepts->count()))->pluck('id')->toArray()
            );
        }

        return $folder;
    }
}

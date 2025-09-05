<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\RecordsImport;
use App\Models\Dolly;
use Exception;

class TestImportTypes extends Command
{
    protected $signature = 'test:import-types';
    protected $description = 'Test des conversions de types dans l\'import';

    public function handle()
    {
        $this->info('=== Test des conversions de types ===');

        // Créer un Dolly de test
        $dolly = Dolly::create([
            'name' => 'Test Types ' . now()->format('Y-m-d H:i:s'),
            'description' => 'Test des conversions de types',
            'category' => 'record',
            'is_public' => false,
            'created_by' => 1,
            'owner_organisation_id' => 1,
        ]);

        // Créer une instance d'import
        $import = new RecordsImport($dolly, [], false, false);

        // Test avec différents types de données
        $testData = [
            // Données normales
            ['F001', 'Test normal', 'fonds', 'actif', 'papier', 'administration', 'Contenu normal'],
            
            // Données avec tableaux
            [['F002'], ['Test', 'tableau'], 'fonds', 'actif', 'papier', 'administration', 'Contenu avec tableau'],
            
            // Données avec nombres
            [123, 'Test nombre', 'fonds', 'actif', 'papier', 'administration', 'Contenu avec nombre'],
            
            // Données mixtes
            [['F004', 'F005'], 'Test mixte', 'fonds', 'actif', 'papier', 'administration', 'Contenu mixte'],
        ];

        $this->info('Test des conversions de types...');

        foreach ($testData as $index => $row) {
            $this->line("Ligne " . ($index + 1) . ": ");
            
            try {
                $result = $import->model($row);
                if ($result) {
                    $this->info("✓ Importée - Code: {$result->code}, Nom: {$result->name}");
                } else {
                    $this->warn("✗ Ignorée");
                }
            } catch (Exception $e) {
                $this->error("✗ Erreur: " . $e->getMessage());
            }
        }

        // Afficher le résumé
        $summary = $import->getImportSummary();
        $this->info("\n=== Résumé ===");
        $this->line("Importés: {$summary['imported']}");
        $this->line("Ignorés: {$summary['skipped']}");
        $this->line("Erreurs: {$summary['errors']}");

        if ($summary['errors'] > 0) {
            $this->warn("\n=== Détails des erreurs ===");
            foreach ($summary['errors'] as $error) {
                $this->error("Ligne {$error['row']}: {$error['error']}");
            }
        }

        $this->info("\nTest terminé.");
    }
}

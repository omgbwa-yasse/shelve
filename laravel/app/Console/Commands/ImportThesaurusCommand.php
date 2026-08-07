<?php

namespace App\Console\Commands;

use App\Services\RdfThesaurusImporter;
use Illuminate\Console\Command;

class ImportThesaurusCommand extends Command
{
    protected $signature = 'thesaurus:import {file : Path to the RDF file} {--validate : Validate only without importing}';
    protected $description = 'Import a SKOS RDF thesaurus file into the database';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $validateOnly = $this->option('validate');

        $validationResult = $this->validateFile($filePath);
        if ($validationResult !== 0) {
            return $validationResult;
        }

        $this->info("Starting RDF import from: {$filePath}");

        if ($validateOnly) {
            $this->info("Validation mode - no data will be imported");
        }

        return $this->performImport($filePath);
    }

    private function validateFile(string $filePath): int
    {
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        if (!is_readable($filePath)) {
            $this->error("File is not readable: {$filePath}");
            return 1;
        }

        return 0;
    }

    private function performImport(string $filePath): int
    {
        $importer = new RdfThesaurusImporter();

        try {
            $stats = $importer->importRdfFile($filePath);

            $this->info("Import completed successfully!");
            $this->table(
                ['Type', 'Count'],
                [
                    ['Concept Schemes', $stats['schemes']],
                    ['Concepts', $stats['concepts']],
                    ['XL Labels', $stats['xl_labels']],
                    ['Alternative Labels', $stats['alternative_labels']],
                    ['Relations', $stats['relations']],
                ]
            );

            if (!empty($stats['errors'])) {
                $this->warn("Errors encountered during import:");
                foreach ($stats['errors'] as $error) {
                    $this->error("  - {$error}");
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
    }
}

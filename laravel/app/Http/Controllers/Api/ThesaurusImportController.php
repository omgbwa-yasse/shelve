<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThesaurusImport;
use App\Imports\ThesaurusSkosImport;
use App\Imports\ThesaurusCsvImport;
use App\Exceptions\ThesaurusImportException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ThesaurusImportController extends Controller
{
    /**
     * Process SKOS import
     */
    public function processSkosImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xml,rdf,ttl,n3|max:20720',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id',
            'language' => 'nullable|string',
            'merge_mode' => 'required|in:replace,merge,append',
        ]);

        return $this->processImport($request, 'skos-rdf');
    }

    /**
     * Process CSV import
     */
    public function processCsvImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv|max:20720',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id',
            'language' => 'nullable|string',
            'merge_mode' => 'required|in:replace,merge,append',
        ]);

        return $this->processImport($request, 'csv');
    }

    /**
     * Process RDF import (redirects to SKOS since SKOS is written in RDF)
     */
    public function processRdfImport(Request $request)
    {
        return $this->processSkosImport($request);
    }

    /**
     * Get import status
     */
    public function getImportStatus($importId)
    {
        $import = ThesaurusImport::find($importId);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $import->id,
                'status' => $import->status,
                'message' => $import->message,
                'total_items' => $import->total_items,
                'processed_items' => $import->processed_items,
                'created_items' => $import->created_items,
                'updated_items' => $import->updated_items,
                'error_items' => $import->error_items,
                'relationships_created' => $import->relationships_created,
                'created_at' => $import->created_at,
                'updated_at' => $import->updated_at,
            ]
        ]);
    }

    /**
     * Process import for any format
     */
    private function processImport(Request $request, string $format)
    {
        $file = $request->file('file');
        $schemeId = $request->input('scheme_id');
        $language = $request->input('language', 'fr-fr');
        $mergeMode = $request->input('merge_mode');

        // Generate unique import ID
        $importId = Str::uuid();

        // Create import record
        $importRecord = ThesaurusImport::create([
            'id' => $importId,
            'type' => $format,
            'filename' => $file->getClientOriginalName(),
            'status' => 'processing',
            'message' => 'Import en cours...',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Store file temporarily
            $directory = storage_path('app/imports/thesaurus');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $file->storeAs('imports/thesaurus', $importId . '.' . $file->getClientOriginalExtension(), 'local');
            $fullPath = storage_path('app/' . $path);

            // Set proper file permissions
            chmod($fullPath, 0644);

            // Verify file was stored correctly
            if (!file_exists($fullPath) || !is_readable($fullPath)) {
                throw new \Exception("Le fichier n'a pas pu être stocké correctement ou n'est pas accessible en lecture.");
            }

            // Import file based on format
            $result = match ($format) {
                'skos-rdf' => $this->importFromSkos($path, $schemeId, $language, $mergeMode),
                'csv' => $this->importFromCsv($path, $schemeId, $language, $mergeMode),
                default => throw new \Exception('Format d\'import non supporté')
            };

            // Update import status
            $importRecord->update([
                'status' => 'completed',
                'total_items' => $result['total'] ?? 0,
                'processed_items' => $result['processed'] ?? 0,
                'created_items' => $result['created'] ?? 0,
                'updated_items' => $result['updated'] ?? 0,
                'error_items' => $result['errors'] ?? 0,
                'relationships_created' => $result['relationships'] ?? 0,
                'message' => $result['message'] ?? 'Import completed successfully',
                'updated_at' => now(),
            ]);

            // Clean up temporary file
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Import completed successfully',
                'data' => [
                    'import_id' => $importId,
                    'total' => $result['total'] ?? 0,
                    'processed' => $result['processed'] ?? 0,
                    'created' => $result['created'] ?? 0,
                    'updated' => $result['updated'] ?? 0,
                    'errors' => $result['errors'] ?? 0,
                    'relationships' => $result['relationships'] ?? 0,
                ]
            ]);

        } catch (ThesaurusImportException $e) {
            Log::error('Erreur d\'import thésaurus: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');

            $importRecord->update([
                'status' => 'error',
                'message' => 'Erreur: ' . $e->getMessage(),
                'updated_at' => now(),
            ]);

            if (isset($path)) {
                Storage::delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Exception lors de l\'import thésaurus: ' . $e->getMessage());

            $importRecord->update([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage(),
                'updated_at' => now(),
            ]);

            if (isset($path)) {
                Storage::delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import from SKOS file
     */
    private function importFromSkos($path, $schemeId, $language, $mergeMode)
    {
        $importer = new ThesaurusSkosImport();
        return $importer->import($path, $schemeId, $language, $mergeMode);
    }

    /**
     * Import from CSV file
     */
    private function importFromCsv($path, $schemeId, $language, $mergeMode)
    {
        $importer = new ThesaurusCsvImport();
        return $importer->import($path, $schemeId, $language, $mergeMode);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ThesaurusImport\StoreThesaurusImportRequest;
use App\Imports\ThesaurusCsvImport;
use App\Imports\ThesaurusJsonImport;
use App\Imports\ThesaurusSkosImport;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusImport;
use App\Models\ThesaurusScheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * D08 — import thésaurus, porté le 2026-08-05.
 *
 * Le flux de `ThesaurusController::importFile()` / `Api\ThesaurusImportController` est
 * exposé : POST `/thesaurus/import` (fichier + format + mode de fusion, enregistrement
 * de suivi dans `thesaurus_imports`, table présente dans le schéma) et GET
 * `/thesaurus/imports/{import}` pour le statut. L'import est synchrone : la table de
 * suivi est écrite en `processing` puis `completed` / `failed` (enum du schéma —
 * `failed`, pas `error`).
 *
 * Export SKOS-RDF/CSV/JSON : non porté — classe E2 (phase 3), exports de fichiers
 * binaires (voir la note du fichier de routes D08).
 *
 * Divergence documentée : quand `scheme_id` est omis, l'importateur SKOS
 * (`ThesaurusSkosImport`) crée un schéma sans renseigner `uri` (colonne NOT NULL du
 * schéma) — bug latent du Blade ; l'API renvoie alors un 500 explicite. Le cas
 * nominal (schéma cible fourni) est propre. La correction relève de l'importateur.
 */
class ThesaurusImportController extends Controller
{
    /**
     * POST /api/v1/thesaurus/import
     */
    public function import(StoreThesaurusImportRequest $request): JsonResponse
    {
        // Un import crée des concepts et, à défaut de schéma cible, un schéma.
        $this->authorize('create', ThesaurusConcept::class);
        $this->authorize('create', ThesaurusScheme::class);

        $file = $request->file('file');
        $format = $request->input('format');
        $schemeId = $request->input('scheme_id');
        $language = $request->input('language', 'fr-fr');
        $mergeMode = $request->input('merge_mode', 'append');

        $importId = Str::uuid();

        $import = ThesaurusImport::create([
            'id' => $importId,
            'type' => $format,
            'filename' => $file->getClientOriginalName(),
            'status' => 'processing',
            'message' => 'Import en cours...',
        ]);

        $path = null;

        try {
            $directory = storage_path('app/imports/thesaurus');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $file->storeAs(
                'imports/thesaurus',
                $importId . '.' . $file->getClientOriginalExtension(),
                'local'
            );

            $result = match ($format) {
                'skos-rdf' => (new ThesaurusSkosImport())->import($path, $schemeId, $language, $mergeMode),
                'csv' => (new ThesaurusCsvImport())->import($path, $schemeId, $language, $mergeMode),
                'json' => (new ThesaurusJsonImport())->import($path, $schemeId, $language, $mergeMode),
            };

            $import->update([
                'status' => 'completed',
                'total_items' => $result['total'] ?? 0,
                'processed_items' => $result['processed'] ?? 0,
                'created_items' => $result['created'] ?? 0,
                'updated_items' => $result['updated'] ?? 0,
                'error_items' => $result['errors'] ?? 0,
                'relationships_created' => $result['relationships'] ?? 0,
                'message' => $result['message'] ?? 'Import terminé.',
            ]);

            return response()->json([
                'data' => [
                    'import_id' => $importId,
                    'status' => 'completed',
                    'total' => $result['total'] ?? 0,
                    'processed' => $result['processed'] ?? 0,
                    'created' => $result['created'] ?? 0,
                    'updated' => $result['updated'] ?? 0,
                    'errors' => $result['errors'] ?? 0,
                    'relationships' => $result['relationships'] ?? 0,
                ],
            ], 201, ['Location' => "/api/v1/thesaurus/imports/{$importId}"]);
        } catch (\Throwable $e) {
            Log::error('Erreur d\'import thésaurus: ' . $e->getMessage());

            $import->update([
                'status' => 'failed',
                'message' => 'Erreur: ' . $e->getMessage(),
            ]);

            return response()->json([
                'data' => [
                    'import_id' => $importId,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        } finally {
            if ($path) {
                Storage::delete($path);
            }
        }
    }

    /**
     * GET /api/v1/thesaurus/imports/{import}
     */
    public function show(ThesaurusImport $import): JsonResponse
    {
        $this->authorize('viewAny', ThesaurusConcept::class);

        return response()->json([
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
            ],
        ]);
    }
}

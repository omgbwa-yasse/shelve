<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\NonDescriptor;
use App\Models\ExternalAlignment;
use App\Models\ThesaurusImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ThesaurusImportController extends Controller
{
    /**
     * Traitement AJAX d'import SKOS
     */
    public function processSkosImport(Request $request)
    {
        try {
            // Log pour débogage
            Log::info('Début du processus d\'import SKOS', [
                'request_data' => $request->all(),
                'has_file' => $request->hasFile('file')
            ]);

            // Validation
            $validator = Validator::make($request->all(), [
                'file' => 'required|file',
                'batch_size' => 'integer|min:10|max:1000',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation a échoué pour import SKOS', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Créer un identifiant unique pour cet import
            $importId = Str::uuid()->toString();

            Log::info('Import SKOS: Création d\'un nouvel identifiant', ['import_id' => $importId]);

            // Enregistrer les informations d'import dans la base de données
            $import = new ThesaurusImport();
            $import->id = $importId;
            $import->type = 'skos';
            $import->filename = $request->file('file')->getClientOriginalName();
            $import->status = 'processing';
            $import->total_items = 0;
            $import->processed_items = 0;
            $import->created_items = 0;
            $import->updated_items = 0;
            $import->error_items = 0;
            $import->relationships_created = 0;

            try {
                $import->save();
                Log::info('Import SKOS: Enregistrement créé dans la base de données', ['import_id' => $importId]);
            } catch (\Exception $e) {
                Log::error('Import SKOS: Erreur lors de la création de l\'enregistrement dans la base de données', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'enregistrement dans la base de données'
                ], 500);
            }

            // S'assurer que le répertoire de stockage temporaire existe
            $storage = \Storage::disk('local');
            if (!$storage->exists('temp/imports')) {
                $storage->makeDirectory('temp/imports');
                Log::info('Import SKOS: Création du répertoire temporaire', ['directory' => 'temp/imports']);
            }

            // Stocker le fichier dans un emplacement temporaire
            try {
                $filePath = $request->file('file')->store('temp/imports');

                Log::info('Import SKOS: Fichier importé avec succès', [
                    'filename' => $request->file('file')->getClientOriginalName(),
                    'path' => $filePath,
                    'import_id' => $importId
                ]);
            } catch (\Exception $e) {
                Log::error('Import SKOS: Erreur lors du stockage du fichier', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                $import->status = 'failed';
                $import->message = "Erreur lors du stockage du fichier: " . $e->getMessage();
                $import->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du stockage du fichier'
                ], 500);
            }

            // Démarrer un job en arrière-plan pour traiter l'import
            try {
                dispatch(function () use ($importId, $filePath, $request) {
                    $this->processSkosBatchImport($importId, $filePath, $request->input('batch_size', 100));
                })->afterResponse();

                Log::info('Import SKOS: Job dispatché pour traitement en arrière-plan', ['import_id' => $importId]);
            } catch (\Exception $e) {
                Log::error('Import SKOS: Erreur lors du dispatch du job', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                $import->status = 'failed';
                $import->message = "Erreur lors du lancement du traitement: " . $e->getMessage();
                $import->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du lancement du traitement'
                ], 500);
            }

            // Retourner l'ID de l'import pour que le client puisse suivre l'avancement
            return response()->json([
                'success' => true,
                'import_id' => $importId
            ]);
        } catch (\Exception $e) {
            Log::error('Import SKOS: Exception non gérée', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur inattendue est survenue lors de l\'import'
            ], 500);
        }
    }

    /**
     * Traitement AJAX d'import CSV
     */
    public function processCsvImport(Request $request)
    {
        try {
            // Log pour débogage
            Log::info('Début du processus d\'import CSV', [
                'request_data' => $request->all(),
                'has_file' => $request->hasFile('file')
            ]);

            // Validation
            $validator = Validator::make($request->all(), [
                'file' => 'required|file',
                'batch_size' => 'integer|min:10|max:1000',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation a échoué pour import CSV', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Créer un identifiant unique pour cet import
            $importId = Str::uuid()->toString();

            Log::info('Import CSV: Création d\'un nouvel identifiant', ['import_id' => $importId]);

            // Enregistrer les informations d'import dans la base de données
            $import = new ThesaurusImport();
            $import->id = $importId;
            $import->type = 'csv';
            $import->filename = $request->file('file')->getClientOriginalName();
            $import->status = 'processing';
            $import->total_items = 0;
            $import->processed_items = 0;
            $import->created_items = 0;
            $import->updated_items = 0;
            $import->error_items = 0;
            $import->relationships_created = 0;

            try {
                $import->save();
                Log::info('Import CSV: Enregistrement créé dans la base de données', ['import_id' => $importId]);
            } catch (\Exception $e) {
                Log::error('Import CSV: Erreur lors de la création de l\'enregistrement dans la base de données', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'enregistrement dans la base de données'
                ], 500);
            }

            // S'assurer que le répertoire de stockage temporaire existe
            $storage = \Storage::disk('local');
            if (!$storage->exists('temp/imports')) {
                $storage->makeDirectory('temp/imports');
                Log::info('Import CSV: Création du répertoire temporaire', ['directory' => 'temp/imports']);
            }

            // Stocker le fichier dans un emplacement temporaire
            try {
                $filePath = $request->file('file')->store('temp/imports');

                Log::info('Import CSV: Fichier importé avec succès', [
                    'filename' => $request->file('file')->getClientOriginalName(),
                    'path' => $filePath,
                    'import_id' => $importId
                ]);
            } catch (\Exception $e) {
                Log::error('Import CSV: Erreur lors du stockage du fichier', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                $import->status = 'failed';
                $import->message = "Erreur lors du stockage du fichier: " . $e->getMessage();
                $import->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du stockage du fichier'
                ], 500);
            }

            // Démarrer un job en arrière-plan pour traiter l'import
            try {
                dispatch(function () use ($importId, $filePath, $request) {
                    $this->processCsvBatchImport($importId, $filePath, $request->input('batch_size', 100));
                })->afterResponse();

                Log::info('Import CSV: Job dispatché pour traitement en arrière-plan', ['import_id' => $importId]);
            } catch (\Exception $e) {
                Log::error('Import CSV: Erreur lors du dispatch du job', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                $import->status = 'failed';
                $import->message = "Erreur lors du lancement du traitement: " . $e->getMessage();
                $import->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du lancement du traitement'
                ], 500);
            }

            // Retourner l'ID de l'import pour que le client puisse suivre l'avancement
            return response()->json([
                'success' => true,
                'import_id' => $importId
            ]);
        } catch (\Exception $e) {
            Log::error('Import CSV: Exception non gérée', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur inattendue est survenue lors de l\'import'
            ], 500);
        }
    }

    /**
     * Traitement AJAX d'import RDF
     */
    public function processRdfImport(Request $request)
    {
        try {
            // Log pour débogage
            Log::info('Début du processus d\'import RDF', [
                'request_data' => $request->all(),
                'has_file' => $request->hasFile('file')
            ]);

            // Validation
            $validator = Validator::make($request->all(), [
                'file' => 'required|file',
                'batch_size' => 'integer|min:10|max:1000',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation a échoué pour import RDF', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Créer un identifiant unique pour cet import
            $importId = Str::uuid()->toString();

            Log::info('Import RDF: Création d\'un nouvel identifiant', ['import_id' => $importId]);

            // Enregistrer les informations d'import dans la base de données
            $import = new ThesaurusImport();
            $import->id = $importId;
            $import->type = 'rdf';
            $import->filename = $request->file('file')->getClientOriginalName();
            $import->status = 'processing';
            $import->total_items = 0;
            $import->processed_items = 0;
            $import->created_items = 0;
            $import->updated_items = 0;
            $import->error_items = 0;
            $import->relationships_created = 0;

            try {
                $import->save();
                Log::info('Import RDF: Enregistrement créé dans la base de données', ['import_id' => $importId]);
            } catch (\Exception $e) {
                Log::error('Import RDF: Erreur lors de la création de l\'enregistrement dans la base de données', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'enregistrement dans la base de données'
                ], 500);
            }

            // S'assurer que le répertoire de stockage temporaire existe
            $storage = \Storage::disk('local');
            if (!$storage->exists('temp/imports')) {
                $storage->makeDirectory('temp/imports');
                Log::info('Import RDF: Création du répertoire temporaire', ['directory' => 'temp/imports']);
            }

            // Stocker le fichier dans un emplacement temporaire
            try {
                $filePath = $request->file('file')->store('temp/imports');

                Log::info('Import RDF: Fichier importé avec succès', [
                    'filename' => $request->file('file')->getClientOriginalName(),
                    'path' => $filePath,
                    'import_id' => $importId
                ]);
            } catch (\Exception $e) {
                Log::error('Import RDF: Erreur lors du stockage du fichier', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                $import->status = 'failed';
                $import->message = "Erreur lors du stockage du fichier: " . $e->getMessage();
                $import->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du stockage du fichier'
                ], 500);
            }

            // Démarrer un job en arrière-plan pour traiter l'import
            try {
                dispatch(function () use ($importId, $filePath, $request) {
                    $this->processRdfBatchImport($importId, $filePath, $request->input('batch_size', 100));
                })->afterResponse();

                Log::info('Import RDF: Job dispatché pour traitement en arrière-plan', ['import_id' => $importId]);
            } catch (\Exception $e) {
                Log::error('Import RDF: Erreur lors du dispatch du job', [
                    'error' => $e->getMessage(),
                    'import_id' => $importId
                ]);

                $import->status = 'failed';
                $import->message = "Erreur lors du lancement du traitement: " . $e->getMessage();
                $import->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du lancement du traitement'
                ], 500);
            }

            // Retourner l'ID de l'import pour que le client puisse suivre l'avancement
            return response()->json([
                'success' => true,
                'import_id' => $importId
            ]);
        } catch (\Exception $e) {
            Log::error('Import RDF: Exception non gérée', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur inattendue est survenue lors de l\'import'
            ], 500);
        }
    }

    /**
     * Récupérer le statut d'un import en cours
     */
    public function getImportStatus($importId)
    {
        $import = ThesaurusImport::findOrFail($importId);

        return response()->json([
            'success' => true,
            'status' => $import->status,
            'stats' => [
                'total' => $import->total_items,
                'processed' => $import->processed_items,
                'created' => $import->created_items,
                'updated' => $import->updated_items,
                'errors' => $import->error_items,
                'relationships' => $import->relationships_created,
            ],
            'progress' => $import->total_items > 0 ? round(($import->processed_items / $import->total_items) * 100, 2) : 0,
            'completed' => in_array($import->status, ['completed', 'failed']),
            'message' => $import->message,
        ]);
    }

    /**
     * Traitement par lots de l'import SKOS
     */
    private function processSkosBatchImport($importId, $filePath, $batchSize)
    {
        $import = ThesaurusImport::findOrFail($importId);

        try {
            // Charger le contenu du fichier
            $content = file_get_contents(storage_path('app/' . $filePath));
            $xml = new \SimpleXMLElement($content);

            // Enregistrer les namespaces pour XPath
            $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            $xml->registerXPathNamespace('skos', 'http://www.w3.org/2004/02/skos/core#');
            $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');

            // Récupérer tous les concepts
            $concepts = $xml->xpath('//skos:Concept');

            // Mettre à jour le total d'éléments à traiter
            $import->total_items = count($concepts);
            $import->save();

            // Pour stocker les relations à traiter après la création des termes
            $relationships = [];

            // Traitement par lots
            foreach (array_chunk($concepts, $batchSize) as $conceptBatch) {
                DB::beginTransaction();

                try {
                    foreach ($conceptBatch as $concept) {
                        // Code de traitement de chaque concept SKOS
                        // Similaire à ce qui est dans la méthode importSkos existante

                        // Mise à jour du compteur de traitement
                        $import->processed_items++;

                        // Sauvegarder régulièrement pour mettre à jour l'interface
                        if ($import->processed_items % 10 == 0) {
                            $import->save();
                        }
                    }

                    DB::commit();
                    $import->save();

                } catch (\Exception $e) {
                    DB::rollback();
                    $import->error_items += count($conceptBatch);
                    $import->message = "Erreur lors du traitement du lot: " . $e->getMessage();
                    $import->save();

                    Log::error("Erreur d'import SKOS par lots: " . $e->getMessage(), [
                        'import_id' => $importId,
                        'exception' => $e
                    ]);
                }
            }

            // Traiter les relations entre les termes
            $this->processRelationships($importId, $relationships);

            // Finaliser l'import
            $import->status = 'completed';
            $import->message = "Import terminé avec succès";
            $import->save();

        } catch (\Exception $e) {
            $import->status = 'failed';
            $import->message = "Erreur lors de l'import: " . $e->getMessage();
            $import->save();

            Log::error("Erreur d'import SKOS: " . $e->getMessage(), [
                'import_id' => $importId,
                'exception' => $e
            ]);
        }
    }

    /**
     * Traitement par lots de l'import CSV
     */
    private function processCsvBatchImport($importId, $filePath, $batchSize)
    {
        $import = ThesaurusImport::findOrFail($importId);

        try {
            // Ouvrir le fichier CSV
            $handle = fopen(storage_path('app/' . $filePath), 'r');

            // Lire l'en-tête
            $header = fgetcsv($handle, 1000, ',');

            // Compter le nombre total de lignes pour la barre de progression
            $lineCount = 0;
            $handle2 = fopen(storage_path('app/' . $filePath), 'r');
            while (fgetcsv($handle2, 1000, ',') !== false) {
                $lineCount++;
            }
            fclose($handle2);

            // Soustraire 1 pour l'en-tête
            $import->total_items = $lineCount - 1;
            $import->save();

            // Pour stocker les relations à traiter après la création des termes
            $relationships = [];

            // Traiter par lots
            $batch = [];
            $batchCount = 0;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $batch[] = array_combine($header, $data);
                $batchCount++;

                if ($batchCount >= $batchSize) {
                    $this->processCsvBatch($import, $batch, $relationships);
                    $batch = [];
                    $batchCount = 0;
                }
            }

            // Traiter le dernier lot s'il reste des données
            if (count($batch) > 0) {
                $this->processCsvBatch($import, $batch, $relationships);
            }

            // Fermer le fichier
            fclose($handle);

            // Traiter les relations entre les termes
            $this->processRelationships($importId, $relationships);

            // Finaliser l'import
            $import->status = 'completed';
            $import->message = "Import terminé avec succès";
            $import->save();

        } catch (\Exception $e) {
            $import->status = 'failed';
            $import->message = "Erreur lors de l'import: " . $e->getMessage();
            $import->save();

            Log::error("Erreur d'import CSV: " . $e->getMessage(), [
                'import_id' => $importId,
                'exception' => $e
            ]);
        }
    }

    /**
     * Traitement d'un lot de lignes CSV
     */
    private function processCsvBatch($import, $batch, &$relationships)
    {
        DB::beginTransaction();

        try {
            foreach ($batch as $row) {
                // Code de traitement de chaque ligne CSV
                // Similaire à ce qui est dans la méthode importCsv existante

                // Mise à jour du compteur de traitement
                $import->processed_items++;

                // Sauvegarder régulièrement pour mettre à jour l'interface
                if ($import->processed_items % 10 == 0) {
                    $import->save();
                }
            }

            DB::commit();
            $import->save();

        } catch (\Exception $e) {
            DB::rollback();
            $import->error_items += count($batch);
            $import->message = "Erreur lors du traitement du lot CSV: " . $e->getMessage();
            $import->save();

            Log::error("Erreur de traitement du lot CSV: " . $e->getMessage(), [
                'import_id' => $import->id,
                'exception' => $e
            ]);
        }
    }

    /**
     * Traitement par lots de l'import RDF
     */
    private function processRdfBatchImport($importId, $filePath, $batchSize)
    {
        $import = ThesaurusImport::findOrFail($importId);

        try {
            // Charger le contenu du fichier
            $content = file_get_contents(storage_path('app/' . $filePath));
            $xml = new \SimpleXMLElement($content);

            // Enregistrer les namespaces pour XPath
            $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            $xml->registerXPathNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
            $xml->registerXPathNamespace('owl', 'http://www.w3.org/2002/07/owl#');
            $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
            $xml->registerXPathNamespace('skos', 'http://www.w3.org/2004/02/skos/core#');

            // Récupérer toutes les descriptions (qui peuvent être des concepts)
            $descriptions = $xml->xpath('//rdf:Description');

            // Filtrer pour ne garder que les concepts
            $concepts = [];
            foreach ($descriptions as $desc) {
                $typeNodes = $desc->xpath('rdf:type[@rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"]');
                if (count($typeNodes) > 0) {
                    $concepts[] = $desc;
                }
            }

            // Mettre à jour le total d'éléments à traiter
            $import->total_items = count($concepts);
            $import->save();

            // Pour stocker les relations à traiter après la création des termes
            $relationships = [];

            // Traitement par lots
            foreach (array_chunk($concepts, $batchSize) as $conceptBatch) {
                DB::beginTransaction();

                try {
                    foreach ($conceptBatch as $concept) {
                        // Code de traitement de chaque concept RDF
                        // Similaire à ce qui est dans la méthode importRdf existante

                        // Mise à jour du compteur de traitement
                        $import->processed_items++;

                        // Sauvegarder régulièrement pour mettre à jour l'interface
                        if ($import->processed_items % 10 == 0) {
                            $import->save();
                        }
                    }

                    DB::commit();
                    $import->save();

                } catch (\Exception $e) {
                    DB::rollback();
                    $import->error_items += count($conceptBatch);
                    $import->message = "Erreur lors du traitement du lot RDF: " . $e->getMessage();
                    $import->save();

                    Log::error("Erreur de traitement du lot RDF: " . $e->getMessage(), [
                        'import_id' => $importId,
                        'exception' => $e
                    ]);
                }
            }

            // Traiter les relations entre les termes
            $this->processRelationships($importId, $relationships);

            // Finaliser l'import
            $import->status = 'completed';
            $import->message = "Import terminé avec succès";
            $import->save();

        } catch (\Exception $e) {
            $import->status = 'failed';
            $import->message = "Erreur lors de l'import: " . $e->getMessage();
            $import->save();

            Log::error("Erreur d'import RDF: " . $e->getMessage(), [
                'import_id' => $importId,
                'exception' => $e
            ]);
        }
    }

    /**
     * Traiter les relations entre les termes
     */
    private function processRelationships($importId, $relationships)
    {
        $import = ThesaurusImport::findOrFail($importId);

        DB::beginTransaction();

        try {
            // Traiter les relations hiérarchiques, associatives, etc.
            // Code similaire à celui des contrôleurs existants

            $import->relationships_created = count($relationships);
            $import->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            $import->message = $import->message . " Erreur lors du traitement des relations: " . $e->getMessage();
            $import->save();

            Log::error("Erreur de traitement des relations: " . $e->getMessage(), [
                'import_id' => $importId,
                'exception' => $e
            ]);
        }
    }
}

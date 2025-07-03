<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Services\RecordEnricherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RecordEnricherController extends Controller
{
    protected RecordEnricherService $enricherService;

    public function __construct(RecordEnricherService $enricherService)
    {
        $this->enricherService = $enricherService;
        $this->middleware('auth');
    }

    /**
     * Vérifier l'état du service d'enrichissement
     */
    public function status()
    {
        $mcpStatus = $this->enricherService->checkHealth();
        $ollamaStatus = $this->enricherService->checkOllama();

        return response()->json([
            'mcp' => $mcpStatus,
            'ollama' => $ollamaStatus,
        ]);
    }

    /**
     * Enrichir un enregistrement
     */
    public function enrich(Request $request, $id)
    {
        // Valider la requête
        $validator = Validator::make($request->all(), [
            'mode' => 'required|in:enrich,summarize,analyze,format_title,extract_keywords,categorized_keywords',
            'model' => 'nullable|string',
            'field_target' => 'nullable|in:content,biographical_history,note,name',
            'auto_assign_terms' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer l'enregistrement
        $record = Record::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'error' => "Enregistrement non trouvé"
            ], 404);
        }

        // Vérifier les autorisations (à adapter selon votre système de permissions)
        // if ($this->authorize('update', $record)) {
        //     return response()->json([
        //         'success' => false,
        //         'error' => "Non autorisé"
        //     ], 403);
        // }

        // Traiter l'enrichissement
        $result = $this->enricherService->enrichRecord(
            $record,
            $request->input('model', 'llama3'),
            $request->input('mode', 'enrich'),
            Auth::id()
        );

        if ($result['success']) {
            // Si l'utilisateur a spécifié un champ cible et que le traitement a réussi,
            // mettre à jour directement le champ spécifié
            $fieldTarget = $request->input('field_target');
            if ($fieldTarget && in_array($fieldTarget, ['content', 'biographical_history', 'note'])) {
                $record->$fieldTarget = $result['enrichedContent'];
                $record->save();

                return response()->json([
                    'success' => true,
                    'message' => "Champ '$fieldTarget' mis à jour avec le contenu enrichi",
                    'record' => $record,
                    'enrichedContent' => $result['enrichedContent'],
                    'stats' => $result['stats'] ?? null
                ]);
            }

            // Sinon, retourner simplement le contenu enrichi sans modifier l'enregistrement
            return response()->json([
                'success' => true,
                'enrichedContent' => $result['enrichedContent'],
                'mode' => $result['mode'],
                'model' => $result['model'],
                'stats' => $result['stats'] ?? null
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? "Erreur inconnue lors de l'enrichissement"
        ], 500);
    }

    /**
     * Prévisualiser l'enrichissement sans sauvegarder
     */
    public function preview(Request $request, $id)
    {
        // Valider la requête
        $validator = Validator::make($request->all(), [
            'mode' => 'required|in:enrich,summarize,analyze',
            'model' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer l'enregistrement
        $record = Record::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'error' => "Enregistrement non trouvé"
            ], 404);
        }

        // Traiter l'enrichissement
        $result = $this->enricherService->enrichRecord(
            $record,
            $request->input('model', 'llama3'),
            $request->input('mode', 'enrich'),
            Auth::id()
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'record' => [
                    'id' => $record->id,
                    'name' => $record->name,
                ],
                'originalContent' => $record->content,
                'enrichedContent' => $result['enrichedContent'],
                'mode' => $result['mode'],
                'model' => $result['model'],
                'stats' => $result['stats'] ?? null
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? "Erreur inconnue lors de l'enrichissement"
        ], 500);
    }

    /**
     * Formater le titre d'un enregistrement
     */
    public function formatTitle(Request $request, $id)
    {
        // Valider la requête
        $validator = Validator::make($request->all(), [
            'model' => 'nullable|string',
            'apply' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer l'enregistrement
        $record = Record::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'error' => "Enregistrement non trouvé"
            ], 404);
        }

        // Formatter le titre
        $result = $this->enricherService->formatTitle(
            $record->name,
            $request->input('model', 'llama3'),
            Auth::id()
        );

        if ($result['success']) {
            // Si l'utilisateur a demandé à appliquer le changement
            $apply = $request->input('apply', false);
            if ($apply && isset($result['formattedTitle'])) {
                $record->name = $result['formattedTitle'];
                $record->save();

                return response()->json([
                    'success' => true,
                    'message' => "Le titre a été mis à jour",
                    'record' => $record,
                    'originalTitle' => $result['originalTitle'],
                    'formattedTitle' => $result['formattedTitle']
                ]);
            }

            return response()->json([
                'success' => true,
                'originalTitle' => $result['originalTitle'],
                'formattedTitle' => $result['formattedTitle']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? "Erreur inconnue lors du formatage du titre"
        ], 500);
    }

    /**
     * Extraire des mots-clés et rechercher dans le thésaurus
     */
    public function extractKeywords(Request $request, $id)
    {
        // Valider la requête
        $validator = Validator::make($request->all(), [
            'model' => 'nullable|string',
            'maxTerms' => 'nullable|integer|min:1|max:20',
            'applyTerms' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer l'enregistrement
        $record = Record::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'error' => "Enregistrement non trouvé"
            ], 404);
        }

        // Extraire les mots-clés
        $result = $this->enricherService->extractKeywords(
            $record,
            $request->input('model', 'llama3'),
            $request->input('maxTerms', 5),
            Auth::id()
        );

        if ($result['success']) {
            // Si l'utilisateur a demandé à appliquer les termes trouvés
            $applyTerms = $request->input('applyTerms', false);
            if ($applyTerms && !empty($result['matchedTerms'])) {
                // Récupérer les IDs des termes correspondants
                $termIds = array_column($result['matchedTerms'], 'id');

                // Attacher les termes au record
                if (!empty($termIds)) {
                    $record->terms()->syncWithoutDetaching($termIds);
                }

                return response()->json([
                    'success' => true,
                    'message' => count($termIds) . " termes ont été associés à l'enregistrement",
                    'extractedKeywords' => $result['extractedKeywords'],
                    'matchedTerms' => $result['matchedTerms'],
                    'appliedTermIds' => $termIds
                ]);
            }

            return response()->json([
                'success' => true,
                'extractedKeywords' => $result['extractedKeywords'],
                'matchedTerms' => $result['matchedTerms']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? "Erreur inconnue lors de l'extraction des mots-clés"
        ], 500);
    }

    /**
     * Extraire des mots-clés catégorisés (géographiques, thématiques, typologiques)
     */
    public function extractCategorizedKeywords(Request $request, $id)
    {
        // Valider la requête
        $validator = Validator::make($request->all(), [
            'model' => 'nullable|string',
            'maxTermsPerCategory' => 'nullable|integer|min:1|max:10',
            'autoAssign' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer l'enregistrement
        $record = Record::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'error' => "Enregistrement non trouvé"
            ], 404);
        }

        // Extraire les mots-clés catégorisés
        $result = $this->enricherService->extractCategorizedKeywords(
            $record,
            $request->input('model', 'llama3'),
            $request->input('maxTermsPerCategory', 3),
            $request->input('autoAssign', false),
            Auth::id()
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'recordId' => $record->id,
                'extractedKeywords' => $result['extractedKeywords'],
                'matchedTerms' => $result['matchedTerms'],
                'allExtractedKeywords' => $result['allExtractedKeywords'] ?? [],
                'assignment' => $result['assignment'] ?? ['autoAssigned' => false]
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? "Erreur inconnue lors de l'extraction des mots-clés catégorisés"
        ], 500);
    }

    /**
     * Assigner des termes à un record
     */
    public function assignTerms(Request $request, $id)
    {
        // Valider la requête
        $validator = Validator::make($request->all(), [
            'termIds' => 'required|array',
            'termIds.*' => 'required|integer|exists:terms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer l'enregistrement
        $record = Record::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'error' => "Enregistrement non trouvé"
            ], 404);
        }

        // Associer les termes
        $result = $this->enricherService->assignTermsToRecord(
            $record->id,
            $request->input('termIds'),
            Auth::id()
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Termes associés avec succès',
                'assignedTerms' => $result['assignedTerms'] ?? count($request->input('termIds')),
                'recordId' => $record->id
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? "Erreur lors de l'association des termes"
        ], 500);
    }
}

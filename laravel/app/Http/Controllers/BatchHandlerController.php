<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Batch;
use App\Models\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatchHandlerController extends Controller
{
    /**
     * Liste les parapheurs (batches) disponibles pour l'organisation courante
     */
    public function list(Request $request): JsonResponse
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!Auth::check() || !Auth::user()->currentOrganisation) {
                return response()->json(['batches' => []], 200);
            }

            $batches = Batch::where('organisation_holder_id', Auth::user()->currentOrganisation->id)
                ->withCount('mails')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['batches' => $batches], 200);

        } catch (\Exception $e) {
            // En cas d'erreur, retourner une liste vide plutôt qu'une erreur
            return response()->json(['batches' => []], 200);
        }
    }

    /**
     * Crée un nouveau parapheur avec les courriers sélectionnés
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'code' => 'nullable|string|max:10|unique:batches,code',
                'name' => 'required|string|max:100',
                'mail_ids' => 'required|array',
                'mail_ids.*' => 'integer|exists:mails,id'
            ]);

            // Vérifier que l'utilisateur est connecté
            if (!Auth::check() || !Auth::user()->currentOrganisation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            DB::beginTransaction();

            // Créer le parapheur
            $batch = Batch::create([
                'code' => $validatedData['code'],
                'name' => $validatedData['name'],
                'organisation_holder_id' => Auth::user()->currentOrganisation->id
            ]);

            // Ajouter les courriers au parapheur
            $batch->mails()->attach($validatedData['mail_ids'], [
                'insert_date' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Parapheur créé avec succès',
                'data' => $batch
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du parapheur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajoute des courriers à un parapheur existant
     */
    public function addItems(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'batch_id' => 'required|integer|exists:batches,id',
                'mail_ids' => 'required|array',
                'mail_ids.*' => 'integer|exists:mails,id'
            ]);

            // Vérifier que l'utilisateur est connecté
            if (!Auth::check() || !Auth::user()->currentOrganisation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $batch = Batch::find($validatedData['batch_id']);

            // Vérifier que le parapheur appartient à l'organisation courante
            if ($batch->organisation_holder_id != Auth::user()->currentOrganisation->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à ce parapheur'
                ], 403);
            }

            DB::beginTransaction();

            // Ajouter uniquement les courriers qui ne sont pas déjà dans le parapheur
            $existingMailIds = $batch->mails()->pluck('mail_id')->toArray();
            $newMailIds = array_diff($validatedData['mail_ids'], $existingMailIds);

            if (!empty($newMailIds)) {
                $batch->mails()->attach($newMailIds, [
                    'insert_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($newMailIds) . ' courrier(s) ajouté(s) au parapheur',
                'added_count' => count($newMailIds),
                'already_exists_count' => count($validatedData['mail_ids']) - count($newMailIds)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout des courriers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retire des courriers d'un parapheur
     */
    public function removeItems(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'batch_id' => 'required|integer|exists:batches,id',
                'mail_ids' => 'required|array',
                'mail_ids.*' => 'integer|exists:mails,id'
            ]);

            // Vérifier que l'utilisateur est connecté
            if (!Auth::check() || !Auth::user()->currentOrganisation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $batch = Batch::find($validatedData['batch_id']);

            // Vérifier que le parapheur appartient à l'organisation courante
            if ($batch->organisation_holder_id != Auth::user()->currentOrganisation->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à ce parapheur'
                ], 403);
            }

            DB::beginTransaction();

            // Retirer les courriers du parapheur
            $batch->mails()->detach($validatedData['mail_ids']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Courriers retirés du parapheur avec succès'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait des courriers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un parapheur
     */
    public function deleteBatch(Request $request, $batchId): JsonResponse
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!Auth::check() || !Auth::user()->currentOrganisation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $batch = Batch::find($batchId);

            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parapheur non trouvé'
                ], 404);
            }

            // Vérifier que le parapheur appartient à l'organisation courante
            if ($batch->organisation_holder_id != Auth::user()->currentOrganisation->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à ce parapheur'
                ], 403);
            }

            DB::beginTransaction();

            // Supprimer les relations avec les courriers
            $batch->mails()->detach();

            // Supprimer le parapheur
            $batch->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Parapheur supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du parapheur: ' . $e->getMessage()
            ], 500);
        }
    }
}

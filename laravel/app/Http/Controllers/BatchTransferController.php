<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Mail;
use App\Models\MailArchive;
use App\Models\MailContainer;
use App\Models\Dolly;
use App\Models\DollyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BatchTransferController extends Controller
{
    private const MIN_ARRAY_RULE = 'min:1';
    private const MAX_MAILS_PER_REQUEST = 500;
    private const MAX_TARGETS_PER_REQUEST = 50;

    // Contrat simple: POST JSON { mail_ids: number[], box_ids: number[] }
    public function transferToBoxes(Request $request, Batch $batch)
    {
    // Validate first so payload errors are returned regardless of auth state
    $data = $request->validate([
            'mail_ids' => ['required','array', self::MIN_ARRAY_RULE],
            'mail_ids.*' => ['integer', Rule::exists('mails','id')],
            'box_ids' => ['required','array', self::MIN_ARRAY_RULE],
            'box_ids.*' => ['integer', Rule::exists('mail_containers','id')],
        ]);

    // Then authorize the action
    $this->authorize('update', $batch);

        $status = 200;
        $response = ['success' => true];

        // Seuil sécurité pour les gros lots
    if (count($data['mail_ids']) > self::MAX_MAILS_PER_REQUEST || count($data['box_ids']) > self::MAX_TARGETS_PER_REQUEST) {
            $status = 422;
            $response = [
                'success' => false,
        'message' => 'Taille de lot trop grande (max courriers: '.self::MAX_MAILS_PER_REQUEST.', cibles: '.self::MAX_TARGETS_PER_REQUEST.').'
            ];
        }

        // Vérifier que les mails appartiennent bien au batch
        if ($status === 200) {
            $batchMailIds = $batch->mails()->pluck('mails.id')->all();
            $invalid = array_diff($data['mail_ids'], $batchMailIds);
            if (!empty($invalid)) {
                $status = 422;
                $response = [
                    'success' => false,
                    'message' => 'Certains courriers ne font pas partie de ce parapheur.',
                    'invalid_mail_ids' => array_values($invalid)
                ];
            }
        }

        if ($status === 200) {
            $transferred = 0;

            DB::beginTransaction();
            try {
                foreach ($data['mail_ids'] as $mailId) {
                    foreach ($data['box_ids'] as $boxId) {
                        // Eviter les doublons: on utilise firstOrCreate sur la table pivot mail_archives
                        MailArchive::firstOrCreate([
                            'container_id' => $boxId,
                            'mail_id' => $mailId,
                            'document_type' => 'original',
                        ], [
                            'archived_by' => Auth::id(),
                        ]);
                        $transferred++;
                    }
                }
                DB::commit();
                $response = [
                    'success' => true,
                    'transferred' => $transferred,
                ];
            } catch (\Throwable $e) {
                DB::rollBack();
                $status = 500;
                $response = [
                    'success' => false,
                    'message' => 'Erreur lors du transfert vers boîtes.',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json($response, $status);
    }

    // Contrat simple: POST JSON { mail_ids: number[], dolly_ids: number[] }
    public function transferToDollies(Request $request, Batch $batch)
    {
    // Validate first so payload errors are returned regardless of auth state
    $data = $request->validate([
            'mail_ids' => ['required','array', self::MIN_ARRAY_RULE],
            'mail_ids.*' => ['integer', Rule::exists('mails','id')],
            'dolly_ids' => ['required','array', self::MIN_ARRAY_RULE],
            'dolly_ids.*' => ['integer', Rule::exists('dollies','id')],
        ]);

    // Then authorize the action
    $this->authorize('update', $batch);

        $status = 200;
        $response = ['success' => true];

        // seuil sécurité
    if (count($data['mail_ids']) > self::MAX_MAILS_PER_REQUEST || count($data['dolly_ids']) > self::MAX_TARGETS_PER_REQUEST) {
            $status = 422;
            $response = [
                'success' => false,
        'message' => 'Taille de lot trop grande (max courriers: '.self::MAX_MAILS_PER_REQUEST.', cibles: '.self::MAX_TARGETS_PER_REQUEST.').'
            ];
        }

        // Vérifier appartenance des mails au batch
        if ($status === 200) {
            $batchMailIds = $batch->mails()->pluck('mails.id')->all();
            $invalid = array_diff($data['mail_ids'], $batchMailIds);
            if (!empty($invalid)) {
                $status = 422;
                $response = [
                    'success' => false,
                    'message' => 'Certains courriers ne font pas partie de ce parapheur.',
                    'invalid_mail_ids' => array_values($invalid)
                ];
            }
        }

        if ($status === 200) {
            $transferred = 0;

            DB::beginTransaction();
            try {
                foreach ($data['dolly_ids'] as $dollyId) {
                    $dolly = Dolly::findOrFail($dollyId);

                    foreach ($data['mail_ids'] as $mailId) {
                        // éviter doublons via syncWithoutDetaching
                        $dolly->mails()->syncWithoutDetaching([$mailId]);
                        $transferred++;
                    }
                }
                DB::commit();
                $response = [
                    'success' => true,
                    'transferred' => $transferred,
                ];
            } catch (\Throwable $e) {
                DB::rollBack();
                $status = 500;
                $response = [
                    'success' => false,
                    'message' => 'Erreur lors du transfert vers chariots.',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json($response, $status);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\MailContainer;
use App\Models\Container;
use App\Models\Shelf;
use App\Models\ContainerStatus;
use App\Models\Room;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipStatus;
use App\Models\Activity;
use App\Models\Author;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class MailContainerTransferController extends Controller
{
    public function transfer(Request $request)
    {
        // Validation des données
        $request->validate([
            'service_id' => 'required|exists:organisations,id',
            'description' => 'required|string|max:1000',
            'transfer_number' => 'required|string|max:50',
            'activity_id' => 'required|exists:activities,id',
            'shelf_id' => 'required|exists:shelves,id',
            'containers' => 'required|array|min:1',
            'containers.*' => 'exists:mail_containers,id'
        ]);

        try {
            DB::beginTransaction();

            // 1. Récupérer les contenants avec leurs mails
            $containers = MailContainer::with(['mails.authors', 'mails.sender', 'mails.recipient', 'mails.externalSender', 'mails.externalRecipient'])
                ->whereIn('id', $request->containers)
                ->get();

            // Vérifier que tous les contenants ont des mails
            $this->validateContainersHaveMails($containers);

            // 2. Créer le Slip
            $slip = $this->createSlip($request);

            // 3. Créer les SlipRecords à partir des mails
            $slipRecordsCreated = $this->createSlipRecordsFromMails($containers, $slip, $request->activity_id, $request->shelf_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transfert créé avec succès. {$slipRecordsCreated} documents transférés.",
                'slip_id' => $slip->id,
                'slip_code' => $slip->code,
                'records_count' => $slipRecordsCreated
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du transfert de contenants mail', [
                'error' => $e->getMessage(),
                'containers' => $request->containers,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du transfert : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider que tous les contenants ont des mails
     */
    private function validateContainersHaveMails($containers)
    {
        $emptyContainers = $containers->filter(function ($container) {
            return $container->mails->isEmpty();
        });

        if ($emptyContainers->isNotEmpty()) {
            $emptyCodes = $emptyContainers->pluck('code')->join(', ');
            throw new InvalidArgumentException("Les contenants suivants sont vides : {$emptyCodes}");
        }
    }

    /**
     * Créer le Slip de transfert
     */
    private function createSlip(Request $request): Slip
    {
        // Générer un code unique pour le slip basé sur le numéro de transfert
        $slipCode = 'TRANS-' . $request->transfer_number;

        // Vérifier l'unicité
        $counter = 1;
        $originalCode = $slipCode;
        while (Slip::where('code', $slipCode)->exists()) {
            $slipCode = $originalCode . '-' . $counter;
            $counter++;
        }

        return Slip::create([
            'code' => $slipCode,
            'name' => 'Transfert de contenants mail - ' . $request->transfer_number,
            'description' => $request->description,
            'officer_organisation_id' => Auth::user()->current_organisation_id,
            'officer_id' => Auth::id(),
            'user_organisation_id' => $request->service_id,
            'user_id' => null, // Sera défini lors de la réception
            'slip_status_id' => 1, // Statut par défaut "En cours"
            'is_received' => false,
            'is_approved' => false,
            'is_integrated' => false,
        ]);
    }

    /**
     * Créer les SlipRecords à partir des mails des contenants
     */
    private function createSlipRecordsFromMails($containers, Slip $slip, int $activityId, int $shelfId): int
    {
        $recordsCreated = 0;
        $sequenceNumber = 1;

        foreach ($containers as $mailContainer) {
            // Créer un contenant pour ce MailContainer
            $newContainer = $this->createContainerFromMailContainer($mailContainer, $slip, $shelfId);

            foreach ($mailContainer->mails as $mail) {
                // Générer le code du SlipRecord : {slip_code}.{sequence}
                $slipRecordCode = $slip->code . '.' . str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);

                // Créer le SlipRecord basé sur le mail
                $slipRecord = SlipRecord::create([
                    'slip_id' => $slip->id,
                    'code' => $slipRecordCode,
                    'name' => $mail->name ?: $mail->description,
                    'date_format' => 'exact',
                    'date_exact' => $mail->date,
                    'content' => $this->formatMailContent($mail, $mailContainer),
                    'level_id' => 1, // Niveau par défaut "Document"
                    'support_id' => 1, // Support par défaut "Papier"
                    'activity_id' => $activityId,
                    'creator_id' => Auth::id(),
                ]);

                // Associer le SlipRecord au contenant créé via la table pivot
                $this->associateSlipRecordToContainer($slipRecord, $newContainer, $mailContainer);

                // Note: La conversion des authors sera ajoutée
                // quand la table pivot slip_record_author sera créée
                // $authors = $this->convertMailProducersToAuthors($mail);

                $recordsCreated++;
                $sequenceNumber++;
            }
        }

        return $recordsCreated;
    }

    /**
     * Formater le contenu du mail pour le SlipRecord
     */
    private function formatMailContent($mail, $container): string
    {
        $content = [];

        $content[] = "Code courrier : {$mail->code}";
        $content[] = "Contenant : {$container->code} - {$container->name}";

        if ($mail->description) {
            $content[] = "Description : {$mail->description}";
        }

        if ($mail->document_type) {
            $content[] = "Type de document : {$mail->document_type}";
        }

        return implode("\n", $content);
    }

    /**
     * Créer un contenant à partir d'un MailContainer
     */
    private function createContainerFromMailContainer($mailContainer, Slip $slip, int $shelfId): Container
    {
        // Générer le code : {slip.code}.{MailContainer.code}
        $containerCode = $slip->code . '.' . $mailContainer->code;

        // Vérifier l'unicité du code
        $counter = 1;
        $originalCode = $containerCode;
        while (Container::where('code', $containerCode)->exists()) {
            $containerCode = $originalCode . '-' . $counter;
            $counter++;
        }

        // S'assurer qu'il y a au moins une étagère
        $shelf = Shelf::find($shelfId);
        if (!$shelf) {
            throw new InvalidArgumentException("L'étagère sélectionnée n'existe pas.");
        }

        // S'assurer qu'il y a au moins un statut
        $defaultStatus = ContainerStatus::first();
        if (!$defaultStatus) {
            throw new InvalidArgumentException("Aucun statut de contenant disponible. Veuillez créer au moins un statut de contenant.");
        }

        return Container::create([
            'code' => $containerCode,
            'description' => "Contenant créé pour le transfert {$slip->code} - Origine: {$mailContainer->name}",
            'shelve_id' => $shelfId,
            'status_id' => $defaultStatus->id,
            'property_id' => $mailContainer->property_id ?? 1, // Utilise la propriété du MailContainer ou par défaut
            'creator_id' => Auth::id(),
            'creator_organisation_id' => Auth::user()->current_organisation_id,
            'is_archived' => false,
        ]);
    }

    /**
     * Associer un SlipRecord à un contenant via la table pivot
     */
    private function associateSlipRecordToContainer(SlipRecord $slipRecord, Container $container, $mailContainer): void
    {
        // Insérer dans la table pivot slip_record_container
        DB::table('slip_record_container')->insert([
            'slip_record_id' => $slipRecord->id,
            'container_id' => $container->id,
            'creator_id' => Auth::id(),
            'description' => "Association automatique du transfert - Origine: {$mailContainer->code}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Récupérer les étagères associées à une organisation via la relation organisation_room
     */
    public function getShelvesByOrganisation($organisationId)
    {
        try {
            // Récupérer les étagères liées à l'organisation via les salles
            $shelves = Shelf::whereHas('room', function ($query) use ($organisationId) {
                $query->whereHas('organisations', function ($subQuery) use ($organisationId) {
                    $subQuery->where('organisations.id', $organisationId);
                });
            })
            ->select('id', 'code', 'observation', 'face', 'ear', 'shelf')
            ->orderBy('code')
            ->get()
            ->map(function ($shelf) {
                return [
                    'id' => $shelf->id,
                    'code' => $shelf->code,
                    'label' => $shelf->code . ' - ' . ($shelf->observation ?? 'Pas de description'),
                    'details' => "Face: {$shelf->face}, Oreille: {$shelf->ear}, Tablette: {$shelf->shelf}"
                ];
            });

            return response()->json([
                'success' => true,
                'shelves' => $shelves,
                'count' => $shelves->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des étagères', [
                'organisation_id' => $organisationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des étagères : ' . $e->getMessage(),
                'shelves' => []
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\MailsExport; // Adapter l'export aux mails
use App\Models\Mail;
use App\Models\Dolly;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class MailTransactionController extends Controller
{
    public function export(Request $request)
    {
        $validatedData = $request->validate([
            'selectedIds' => 'required|array',
            'selectedIds.*' => 'integer|exists:mails,id'
        ]);

        $selectedIds = $validatedData['selectedIds'];

        $mails = Mail::whereIn('id', $selectedIds)
                     ->with(['action', 'sender', 'recipient', 'senderOrganisation', 'recipientOrganisation'])
                     ->get();

        $export = new MailsExport($mails);

        return Excel::download($export, 'courriers-' . now()->format('Y-m-d') . '.xlsx');
    }



    public function print(Request $request)
    {
        $selectedIds = $request->validate([
            'selectedIds' => 'required|array',
            'selectedIds.*' => 'integer|exists:mails,id' // Valider sur la table mails
        ])['selectedIds'];

        $mails = Mail::whereIn('id', $selectedIds)
                     ->with(['action', 'sender', 'recipient', 'senderOrganisation', 'recipientOrganisation']) // Relations adaptées
                     ->get();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->generatePdfHtml($mails); // Passer les mails à la vue

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->stream('courriers-' . date('Y-m-d') . '.pdf');
    }

    private function generatePdfHtml($mails) // Accepter les mails en paramètre
    {
        return view('mails.print.index', [ // Nouveau chemin de la vue
            'mails' => $mails, // Passer les mails à la vue
            'generatedAt' => now()->format('d/m/Y H:i'),
            'totalCount' => $mails->count(),
        ])->render();
    }

    public function archive(Request $request)
    {
        // Valider les données
        $request->validate([
            'mail_ids' => 'required|array',
            'mail_ids.*' => 'exists:mails,id',
            'container_id' => 'required|exists:mail_containers,id'
        ]);

        try {
            $mailIds = $request->input('mail_ids');
            $containerId = $request->input('container_id');
            $userId = Auth::id();

            // Vérifier les courriers avec le statut 'in_progress'
            $mailsInProgress = Mail::whereIn('id', $mailIds)
                ->whereIn('status', ['in_progress', 'reject'])
                ->pluck('code')
                ->toArray();

            if (!empty($mailsInProgress)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'archiver les courriers en cours de traitement: ' . implode(', ', $mailsInProgress)
                ], 400);
            }

            // Vérifier si des courriers sont déjà archivés dans ce conteneur
            $existingArchives = DB::table('mail_archives')
                ->whereIn('mail_id', $mailIds)
                ->where('container_id', $containerId)
                ->pluck('mail_id')
                ->toArray();

            // Filtrer les mails qui ne sont pas déjà archivés
            $mailsToArchive = array_diff($mailIds, $existingArchives);

            if (empty($mailsToArchive)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tous les courriers sélectionnés sont déjà archivés dans ce conteneur.'
                ], 400);
            }

            // Créer les entrées d'archivage dans la table mail_archives
            $archiveData = [];
            foreach ($mailsToArchive as $mailId) {
                $archiveData[] = [
                    'mail_id' => $mailId,
                    'container_id' => $containerId,
                    'archived_by' => $userId,
                    'document_type' => 'original',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            DB::table('mail_archives')->insert($archiveData);

            // Optionnellement, marquer les mails comme archivés
            Mail::whereIn('id', $mailsToArchive)->update([
                'is_archived' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Courriers archivés avec succès',
                'count' => count($mailsToArchive),
                'already_archived' => count($existingArchives)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'archivage: ' . $e->getMessage()
            ], 500);
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchMail;
use App\Models\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BatchController extends Controller
{
    public function index()
    {
        $mailBatches = Batch::where('organisation_holder_id', Auth::user()->currentOrganisation->id ?? '')
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Ajoute la pagination, 10 éléments par page

        return view('batch.index', compact('mailBatches'));
    }



    public function create()
    {
        return view('batch.create');
    }


    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'code' => 'nullable|unique:batches|max:10',
            'name' => 'required|max:100',
        ]);

        $validatedData['organisation_holder_id'] = Auth::user()->currentOrganisation->id;


        Batch::create($validatedData);

        return redirect()->route('batch.index')->with('success', 'Mail batch created successfully.');
    }



    public function show(INT $id)
    {
        $mailBatch = Batch::findOrFail($id);

        return view('batch.show', compact('mailBatch'));
    }




    public function edit(INT $id)
    {
        $mailBatch = Batch::findOrFail($id);
        return view('batch.edit', compact('mailBatch'));
    }




    public function update(Request $request, int $id)
    {
        $mailBatch = Batch::findOrFail($id);
        $validatedData = $request->validate([
            'code' => 'nullable|unique:batches,code,' . $mailBatch->id . '|max:10',
            'name' => 'required|max:100',
        ]);
        $validatedData['organisation_holder_id'] = Auth::user()->currentOrganisation->id;
        $mailBatch->update($validatedData);
        return redirect()->route('batch.index')->with('success', 'Mail batch updated successfully.');
    }



    public function destroy(Batch $mailBatch)
    {

        if ($mailBatch->mails->count() <= 0) {
            $mailBatch->delete();
            return redirect()->route('batch.index')->with('success', 'Mail batch deleted successfully.');
        }
        return redirect()->route('batch.index')->with('error', 'Cannot delete mail batch with associated mails.');
    }

    public function exportPdf(Batch $batch)
    {
        $mails = $batch->mails()->with([
            'priority', 'action', 'typology',
            'sender', 'senderOrganisation', 'externalSender', 'externalSenderOrganization',
            'recipient', 'recipientOrganisation', 'externalRecipient', 'externalRecipientOrganization',
            'containers', 'attachments'
        ])->get();

        $data = [
            'mails' => $mails,
            'totalCount' => $mails->count(),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mails.print.index', $data);
        return $pdf->download('mail_batch_' . $batch->code . '.pdf');
    }

    // Méthodes pour la gestion des mails dans les batches
    public function indexMail(Batch $batch)
    {
        $batchMails = BatchMail::where('batch_id', $batch->id)->get();
        $batchMails->load('mail');

        return view('batch.mail.index', compact('batchMails', 'batch'));
    }

    public function createMail(Batch $batch)
    {
        $mails = Mail::all();
        return view('batch.mail.create', compact('batch', 'mails'));
    }

    public function storeMail(Request $request, Batch $batch)
    {
        // Supporter l'ajout de courriers multiples ou simple
        if ($request->has('mail_ids')) {
            $validatedData = $request->validate([
                'mail_ids' => 'required|array',
                'mail_ids.*' => 'exists:mails,id'
            ]);

            $addedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($validatedData['mail_ids'] as $mailId) {
                // Vérifier si le courrier est déjà dans ce batch
                $existingBatchMail = BatchMail::where('batch_id', $batch->id)
                    ->where('mail_id', $mailId)
                    ->first();

                if ($existingBatchMail) {
                    $skippedCount++;
                    continue;
                }

                $batchMail = new BatchMail([
                    'mail_id' => $mailId,
                    'insert_date' => now(),
                    'batch_id' => $batch->id,
                ]);

                if ($batchMail->save()) {
                    $addedCount++;
                } else {
                    $errors[] = "Erreur lors de l'ajout du courrier ID {$mailId}";
                }
            }

            if ($request->expectsJson()) {
                $message = "{$addedCount} courrier(s) ajouté(s) avec succès.";
                if ($skippedCount > 0) {
                    $message .= " {$skippedCount} courrier(s) déjà présent(s) ont été ignoré(s).";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'added_count' => $addedCount,
                    'skipped_count' => $skippedCount,
                    'errors' => $errors
                ]);
            }

            $message = "{$addedCount} courrier(s) ajouté(s) avec succès.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} courrier(s) déjà présent(s) ont été ignoré(s).";
            }

            return redirect()->route('batch.show', $batch->id)
                ->with('success', $message);

        } else {
            // Logique originale pour un seul courrier
            $validatedData = $request->validate([
                'mail_id' => 'required|exists:mails,id'
            ]);

            // Vérifier si le courrier est déjà dans ce batch
            $existingBatchMail = BatchMail::where('batch_id', $batch->id)
                ->where('mail_id', $validatedData['mail_id'])
                ->first();

            if ($existingBatchMail) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce courrier est déjà dans ce parapheur.'
                    ], 422);
                }
                return redirect()->route('batch.show', $batch->id)
                    ->with('error', 'Ce courrier est déjà dans ce parapheur.');
            }

            $batchMail = new BatchMail([
                'mail_id' => $validatedData['mail_id'],
                'insert_date' => now(),
                'batch_id' => $batch->id,
            ]);

            $batchMail->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Courrier ajouté avec succès au parapheur.'
                ]);
            }

            return redirect()->route('batch.show', $batch->id)
                ->with('success', 'Courriel de lot créé avec succès.');
        }
    }

    public function editMail(Batch $batch, BatchMail $batchMail)
    {
        $mails = Mail::all();
        return view('batch.mail.edit', compact('batch', 'batchMail', 'mails'));
    }

    public function updateMail(Request $request, Batch $batch, BatchMail $batchMail)
    {
        $validatedData = $request->validate([
            'mail_id' => 'required|exists:mails,id'
        ]);

        $validatedData['insert_date'] = now();
        $batchMail->update($validatedData);

        return redirect()->route('batch.mail.index', $batch)->with('success', 'Batch mail updated successfully.');
    }

    public function destroyMail(Batch $batch, int $id)
    {
        $mail = $batch->mails()->find($id);

        if (!$mail) {
            return redirect()->route('batch.show', ['batch' => $batch->id])->with('error', 'Mail not found in this batch.');
        }

        $batch->mails()->detach($id);

        return redirect()->route('batch.show', ['batch' => $batch->id])->with('success', 'Mail and its attachments removed from batch successfully.');
    }

    public function getAvailableMails(Request $request, Batch $batch)
    {
        $query = $request->get('q', '');

        // Récupérer les IDs des courriers déjà dans ce batch
        $existingMailIds = $batch->mails()->pluck('mails.id')->toArray();

        // Récupérer l'organisation de l'utilisateur actuel
        $organisationId = Auth::user()->currentOrganisation->id ?? null;

        // Rechercher les courriers disponibles pour l'organisation de l'utilisateur
        $mailsQuery = Mail::query();

        if ($organisationId) {
            // Récupérer les courriers reçus ET émis par l'organisation actuelle
            $mailsQuery->where(function($q) use ($organisationId) {
                $q->where('sender_organisation_id', $organisationId)      // Courriers émis
                  ->orWhere('recipient_organisation_id', $organisationId); // Courriers reçus
            });
        }

        // Exclure les courriers déjà dans le batch seulement s'il y en a
        if (!empty($existingMailIds)) {
            $mailsQuery->whereNotIn('id', $existingMailIds);
        }

        // Appliquer la recherche par texte seulement si une requête est fournie
        if (!empty($query)) {
            $mailsQuery->where(function($q) use ($query) {
                $q->where('code', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        // Ajouter la pagination
        $page = $request->get('page', 1);
        $perPage = 20;

        $mails = $mailsQuery->with(['typology', 'priority', 'authors'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'mails' => $mails->map(function($mail) use ($organisationId) {
                // Déterminer le type de courrier (reçu ou émis)
                $mailDirection = '';
                if ($mail->sender_organisation_id == $organisationId) {
                    $mailDirection = 'Émis';
                } elseif ($mail->recipient_organisation_id == $organisationId) {
                    $mailDirection = 'Reçu';
                }

                return [
                    'id' => $mail->id,
                    'code' => $mail->code,
                    'name' => $mail->name,
                    'description' => $mail->description,
                    'date' => $mail->date,
                    'direction' => $mailDirection,
                    'type' => $mail->typology,
                    'priority' => $mail->priority,
                    'authors' => $mail->authors
                ];
            }),
            'pagination' => [
                'current_page' => $mails->currentPage(),
                'last_page' => $mails->lastPage(),
                'per_page' => $mails->perPage(),
                'total' => $mails->total(),
                'has_more' => $mails->hasMorePages()
            ]
        ]);
    }

}

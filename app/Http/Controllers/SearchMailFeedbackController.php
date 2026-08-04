<?php

namespace App\Http\Controllers;

use App\Models\Dolly;
use App\Models\Mail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Suivi des courriers « à retourner » : ceux dont l'action impose une réponse.
 *
 * L'implémentation précédente s'appuyait sur mail_transactions et rendait une vue
 * `mails.send.index` inexistante (erreur 500), avec un `whereRaw('DATE_ADD(...)')`
 * propre à MySQL qui ne pouvait pas fonctionner sous SQLite. On interroge désormais
 * directement les courriers et on réutilise la liste centralisée `mails.index`.
 */
class SearchMailFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $organisationId = Auth::user()->current_organisation_id;

        // 'true' = uniquement les courriers dont l'action attend un retour.
        $onlyToReturn = $request->input('type') === 'true';
        $deadline = $request->input('deadline');

        $query = Mail::query()
            ->with(['action', 'sender', 'senderOrganisation', 'attachments', 'containers'])
            ->where(function ($q) use ($organisationId) {
                $q->where('recipient_organisation_id', $organisationId)
                    ->orWhere('assigned_organisation_id', $organisationId);
            })
            ->whereHas('action', fn ($q) => $q->where('to_return', $onlyToReturn));

        if ($deadline === 'exceeded') {
            $query->overdue();
        } elseif ($deadline === 'available') {
            $query->where(function ($q) {
                $q->whereNull('deadline')->orWhere('deadline', '>=', now());
            });
        }

        $mails = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('mails.index', [
            'mails' => $mails,
            'dollies' => Dolly::all(),
            'categories' => Dolly::categories(),
            'users' => User::all(),
            'type' => 'received',
        ]);
    }
}

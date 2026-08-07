<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\SyncEmailAccountJob;
use App\Models\EmailAccount;
use App\Services\Email\EmailSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Throwable;

/**
 * Paramètres des comptes de messagerie (IMAP/SMTP) — un compte par
 * organisation (parfois par utilisateur), consommé par la boîte mail
 * (`MailboxController`) et sa synchro planifiée (`SyncEmailAccountJob`).
 */
class EmailAccountController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', EmailAccount::class);

        $accounts = EmailAccount::byOrganisation(Auth::user()->current_organisation_id)
            ->latest()
            ->paginate(20);

        $moduleEnabled = (bool) Auth::user()->currentOrganisation?->email_module_enabled;

        return view('settings.email-accounts.index', compact('accounts', 'moduleEnabled'));
    }

    /**
     * Active/désactive le module Email pour l'organisation courante — condition
     * d'affichage de la section "Email" dans le menu Mails (Blade + Next.js).
     */
    public function toggleModule()
    {
        // Action au niveau organisation, sans instance EmailAccount précise à
        // vérifier : contrôle direct de permission plutôt que Gate::authorize
        // (qui attend un modèle pour la vérification d'appartenance org).
        abort_unless(Auth::user()->hasPermission('email_account_update'), 403);

        $organisation = Auth::user()->currentOrganisation;
        abort_unless($organisation, 404);

        $organisation->update(['email_module_enabled' => ! $organisation->email_module_enabled]);

        return back()->with('success', $organisation->email_module_enabled
            ? 'Email activé.'
            : 'Email désactivé.');
    }

    /** Active/désactive rapidement un compte sans passer par le formulaire complet. */
    public function toggleActive(EmailAccount $emailAccount)
    {
        Gate::authorize('update', $emailAccount);

        $emailAccount->update(['is_active' => ! $emailAccount->is_active]);

        return back()->with('success', $emailAccount->is_active ? 'Compte activé.' : 'Compte désactivé.');
    }

    public function create()
    {
        Gate::authorize('create', EmailAccount::class);

        return view('settings.email-accounts.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', EmailAccount::class);

        $data = $this->validateAccount($request);
        $data['created_by'] = Auth::id();
        $data['user_id'] = Auth::id();

        EmailAccount::create($data);

        return redirect()->route('settings.email-accounts.index')
            ->with('success', 'Compte de messagerie créé.');
    }

    public function edit(EmailAccount $emailAccount)
    {
        Gate::authorize('update', $emailAccount);

        return view('settings.email-accounts.edit', compact('emailAccount'));
    }

    public function update(Request $request, EmailAccount $emailAccount)
    {
        Gate::authorize('update', $emailAccount);

        $data = $this->validateAccount($request, $emailAccount);
        $data['updated_by'] = Auth::id();

        $emailAccount->update($data);

        return redirect()->route('settings.email-accounts.index')
            ->with('success', 'Compte de messagerie mis à jour.');
    }

    public function destroy(EmailAccount $emailAccount)
    {
        Gate::authorize('delete', $emailAccount);

        $emailAccount->delete();

        return redirect()->route('settings.email-accounts.index')
            ->with('success', 'Compte de messagerie supprimé.');
    }

    /** Teste la connexion IMAP/SMTP et déclenche une synchro immédiate en cas de succès. */
    public function testConnection(EmailAccount $emailAccount, EmailSyncService $syncService)
    {
        Gate::authorize('update', $emailAccount);

        try {
            $syncService->sync($emailAccount);
        } catch (Throwable $e) {
            return back()->withErrors(['connection' => 'Échec de connexion : '.$e->getMessage()]);
        }

        return back()->with('success', 'Connexion réussie — synchronisation effectuée.');
    }

    public function syncNow(EmailAccount $emailAccount)
    {
        Gate::authorize('update', $emailAccount);

        SyncEmailAccountJob::dispatch($emailAccount);

        return back()->with('success', 'Synchronisation lancée en arrière-plan.');
    }

    private function validateAccount(Request $request, ?EmailAccount $account = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email_address' => 'required|email|max:255',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'imap_username' => 'required|string|max:255',
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_encryption' => 'required|in:ssl,tls,none',
            'smtp_username' => 'required|string|max:255',
            'default_from_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];

        // Le mot de passe n'est requis qu'à la création — laissé vide en édition,
        // la valeur chiffrée existante est conservée (jamais réaffichée en clair).
        $rules['imap_password'] = $account ? 'nullable|string' : 'required|string';
        $rules['smtp_password'] = $account ? 'nullable|string' : 'required|string';

        $data = $request->validate($rules);

        if (empty($data['imap_password'])) {
            unset($data['imap_password']);
        }
        if (empty($data['smtp_password'])) {
            unset($data['smtp_password']);
        }

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Bloque l'accès à la boîte de messagerie (`mails.email.*` / `api/v1/email-messages`,
 * `email-tags`) tant qu'un administrateur n'a pas activé le module pour
 * l'organisation courante (`organisations.email_module_enabled`). La gestion
 * des comptes (`settings.email-accounts.*`) reste volontairement HORS de ce
 * middleware : l'admin doit pouvoir configurer des comptes et activer le
 * module depuis les mêmes écrans, avant même que le module soit actif.
 */
class EnsureEmailModuleEnabled
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $enabled = (bool) ($user?->currentOrganisation?->email_module_enabled ?? false);

        if (! $enabled) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Le module Email n\'est pas activé pour votre organisation.',
                ], 403);
            }

            return redirect()->route('settings.email-accounts.index')
                ->with('error', 'Le module Email n\'est pas activé. Un administrateur doit l\'activer depuis les paramètres.');
        }

        return $next($request);
    }
}

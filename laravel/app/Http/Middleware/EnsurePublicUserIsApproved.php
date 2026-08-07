<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsurePublicUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->is_approved) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Votre compte est en attente d\'approbation.',
                    'status' => 'pending_approval'
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('public.users.pending-approval')
                ->with('warning', 'Votre compte est en attente d\'approbation par un administrateur.');
        }

        if ($user && !$user->email_verified_at && config('public_portal.registration.requires_verification')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Veuillez vérifier votre adresse email.',
                    'status' => 'email_verification_required'
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('verification.notice')
                ->with('info', 'Veuillez vérifier votre adresse email avant de continuer.');
        }

        return $next($request);
    }
}

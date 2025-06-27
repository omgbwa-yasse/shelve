<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('locale')) {
            App::setLocale(session()->get('locale'));
        }

        // Ensure currentOrganisation relationship is loaded for authenticated users
        if (Auth::check() && Auth::user()) {
            $user = Auth::user();
            if (!$user->relationLoaded('currentOrganisation')) {
                $user->load('currentOrganisation');
            }
        }

        return $next($request);
    }
}

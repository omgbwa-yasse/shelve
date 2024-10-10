<?php

namespace App\Http\Middleware;

use Closure;

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogUserAction
{

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (auth()->check()) {
            Log::create([
                'user_id' => auth()->id(),
                'action' => $request->route()->getName(),
                'description' => auth()->user()->name .' - action performed: ' . $request->method() . ' ' . $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
        return $response;
    }
}

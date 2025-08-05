<?php

namespace App\Http\Controllers;

use App\Services\RateLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RateLimitController extends Controller
{
    public function dashboard(RateLimitService $rateLimitService)
    {
        $this->authorize('admin'); // Seuls les admins peuvent voir le dashboard

        $userStats = $rateLimitService->getStats(Auth::id());

        return view('admin.rate-limit-dashboard', compact('userStats'));
    }

    public function userStats(Request $request, RateLimitService $rateLimitService)
    {
        $this->authorize('admin');

        $userId = $request->input('user_id');
        if (!$userId) {
            return redirect()->back()->withErrors(['user_id' => 'ID utilisateur requis']);
        }

        $userStats = $rateLimitService->getStats($userId);

        return response()->json([
            'stats' => $userStats,
            'user_id' => $userId
        ]);
    }

    public function clearLimits(Request $request, RateLimitService $rateLimitService)
    {
        $this->authorize('admin');

        $action = $request->input('action');
        $userId = $request->input('user_id');

        if (!$action) {
            return redirect()->back()->withErrors(['action' => 'Action requise']);
        }

        $rateLimitService->clear($action, $userId);

        return redirect()->back()->with('success', "Limites effacées pour l'action: {$action}");
    }
}

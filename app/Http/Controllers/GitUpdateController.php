<?php

namespace App\Http\Controllers;

use App\Services\GitUpdateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class GitUpdateController extends Controller
{
    private GitUpdateService $gitUpdateService;

    public function __construct(GitUpdateService $gitUpdateService)
    {
        $this->middleware('auth');
        $this->gitUpdateService = $gitUpdateService;
    }

    /**
     * Run the update process
     */
    public function update(Request $request): JsonResponse
    {
        // Require superadmin or similar management permission
        if (!Auth::user()->isSuperAdmin() && !Gate::allows('system_updates_manage')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $force = $request->input('force', false);
        $result = $this->gitUpdateService->update($force);

        return response()->json($result);
    }
}

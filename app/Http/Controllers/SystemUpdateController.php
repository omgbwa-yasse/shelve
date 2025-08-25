<?php

namespace App\Http\Controllers;

use App\Services\GitHubVersionService;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class SystemUpdateController extends Controller
{
    private const ACCESS_DENIED_MESSAGE = 'Accès non autorisé';

    private GitHubVersionService $versionService;
    private UpdateService $updateService;

    public function __construct(GitHubVersionService $versionService, UpdateService $updateService)
    {
        $this->middleware('auth');
        $this->versionService = $versionService;
        $this->updateService = $updateService;
    }

    /**
     * Display the main update interface
     */
    public function index()
    {
        // Check if user has permission
        if (!Gate::allows('system_updates_manage')) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        $currentVersion = $this->versionService->getCurrentVersion();
        $versionInfo = $this->versionService->getVersionInfo();
        $availableVersions = $this->versionService->getAvailableVersions();
        $versionHistory = $this->versionService->getVersionHistory();

        return view('system.updates.index', compact(
            'currentVersion',
            'versionInfo',
            'availableVersions',
            'versionHistory'
        ));
    }

    /**
     * Check for available updates (AJAX)
     */
    public function checkVersions(): JsonResponse
    {
        try {
            $updateCheck = $this->versionService->checkForUpdates();
            $this->versionService->updateLastCheck();

            return response()->json([
                'success' => true,
                'data' => $updateCheck
            ]);
        } catch (Exception $e) {
            Log::error('Error checking versions', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available versions (AJAX)
     */
    public function getVersions(): JsonResponse
    {
        try {
            $versions = $this->versionService->getAvailableVersions();

            return response()->json([
                'success' => true,
                'data' => $versions
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des versions'
            ], 500);
        }
    }

    /**
     * Get changelog for a specific version (AJAX)
     */
    public function getChangelog(string $version): JsonResponse
    {
        try {
            $changelog = $this->versionService->getVersionInfo();

            return response()->json([
                'success' => true,
                'data' => [
                    'version' => $version,
                    'changelog' => $changelog
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du changelog'
            ], 500);
        }
    }

    /**
     * Update to specific version
     */
    public function updateToVersion(Request $request, string $version)
    {
        try {
            // Validate request
            $request->validate([
                'confirm' => 'required|boolean|accepted'
        ]);

        if (!Gate::allows('system_updates_manage')) {
            return response()->json([
                'success' => false,
                'message' => self::ACCESS_DENIED_MESSAGE
            ], 403);
        }

        Log::info("Début de la mise à jour vers {$version}", ['user' => Auth::id()]);            // Step 1: Prepare update
            $preparation = $this->updateService->prepareUpdate($version);
            if (!$preparation['success']) {
                return response()->json($preparation, 400);
            }

            // Step 2: Create backup
            $backup = $this->updateService->createBackup();
            if (!$backup['success']) {
                return response()->json($backup, 500);
            }

            // Step 3: Download and extract
            $download = $this->updateService->downloadAndExtract($version);
            if (!$download['success']) {
                return response()->json($download, 500);
            }

            // Step 4: Apply update
            $update = $this->updateService->applyUpdate($download['source_path'], $version);
            if (!$update['success']) {
                // Attempt rollback
                $this->updateService->rollback($backup['backup_path']);
                return response()->json([
                    'success' => false,
                    'message' => 'Mise à jour échouée, rollback effectué'
                ], 500);
            }

            // Step 5: Cleanup
            $this->updateService->cleanup();

            return response()->json([
                'success' => true,
                'message' => "Mise à jour vers {$version} réussie",
                'new_version' => $version
            ]);

        } catch (Exception $e) {
            Log::error('Update failed', [
                'version' => $version,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get update progress (for real-time updates)
     */
    public function getUpdateProgress(): JsonResponse
    {
        // This would typically read from a cache/session to show real-time progress
        // For now, return a simple status
        return response()->json([
            'success' => true,
            'progress' => [
                'step' => 'completed',
                'percentage' => 100,
                'message' => 'Mise à jour terminée'
            ]
        ]);
    }

    /**
     * Rollback to a previous version
     */
    public function rollback(Request $request)
    {
        try {
            $request->validate([
                'backup_path' => 'required|string',
                'confirm' => 'required|boolean|accepted'
            ]);

        if (!Gate::allows('system_updates_manage')) {
            return response()->json([
                'success' => false,
                'message' => self::ACCESS_DENIED_MESSAGE
            ], 403);
        }            $result = $this->updateService->rollback($request->backup_path);

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('Rollback failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rollback: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get version history
     */
    public function history()
    {
        if (!Gate::allows('system_updates_manage')) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        $history = $this->versionService->getVersionHistory();

        return view('system.updates.history', compact('history'));
    }
}

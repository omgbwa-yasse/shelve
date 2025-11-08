<?php

namespace App\Http\Controllers\Museum;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display inventory dashboard.
     */
    public function index(Request $request)
    {
        // Statistiques générales
        $stats = [
            'total_artifacts' => RecordArtifact::count(),
            'on_display' => RecordArtifact::where('is_on_display', true)->count(),
            'on_loan' => RecordArtifact::where('is_on_loan', true)->count(),
            'in_storage' => RecordArtifact::where('is_on_display', false)
                ->where('is_on_loan', false)
                ->count(),
            'by_conservation_state' => RecordArtifact::selectRaw('conservation_state, COUNT(*) as count')
                ->groupBy('conservation_state')
                ->get(),
            'by_category' => RecordArtifact::selectRaw('category, COUNT(*) as count')
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->get(),
            'by_location' => RecordArtifact::selectRaw('current_location, COUNT(*) as count')
                ->whereNotNull('current_location')
                ->groupBy('current_location')
                ->orderBy('count', 'desc')
                ->get(),
        ];

        // Liste des artefacts avec filtres
        $query = RecordArtifact::query();

        if ($request->filled('location')) {
            $query->where('current_location', $request->location);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('conservation_state')) {
            $query->where('conservation_state', $request->conservation_state);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $artifacts = $query->orderBy('code')->paginate(50);

        return view('museum.inventory.index', compact('stats', 'artifacts'));
    }

    /**
     * Display recolement form.
     */
    public function recolement()
    {
        // Récupérer tous les emplacements
        $locations = RecordArtifact::select('current_location')
            ->whereNotNull('current_location')
            ->distinct()
            ->orderBy('current_location')
            ->pluck('current_location');

        // Statistiques pour le récolement
        $stats = [
            'total_to_check' => RecordArtifact::count(),
            'by_location' => RecordArtifact::selectRaw('current_location, COUNT(*) as count')
                ->whereNotNull('current_location')
                ->groupBy('current_location')
                ->get(),
        ];

        return view('museum.inventory.recolement', compact('locations', 'stats'));
    }

    /**
     * Store recolement record.
     */
    public function storeRecolement(Request $request)
    {
        $validated = $request->validate([
            'artifacts' => 'required|array',
            'artifacts.*.id' => 'required|exists:record_artifacts,id',
            'artifacts.*.present' => 'required|boolean',
            'artifacts.*.notes' => 'nullable|string',
            'artifacts.*.new_location' => 'nullable|string|max:255',
            'recolement_date' => 'required|date',
            'inspector' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['artifacts'] as $artifactData) {
                $artifact = RecordArtifact::find($artifactData['id']);

                // Mettre à jour l'emplacement si changé
                if (!empty($artifactData['new_location'])) {
                    $artifact->update([
                        'current_location' => $artifactData['new_location'],
                    ]);
                }

                // Enregistrer dans les métadonnées
                $metadata = $artifact->metadata ?? [];
                $metadata['last_recolement'] = [
                    'date' => $validated['recolement_date'],
                    'inspector' => $validated['inspector'],
                    'present' => $artifactData['present'],
                    'notes' => $artifactData['notes'] ?? null,
                ];

                $artifact->update(['metadata' => $metadata]);
            }

            DB::commit();

            return redirect()->route('museum.inventory.index')
                ->with('success', 'Récolement enregistré avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Erreur lors de l\'enregistrement du récolement: ' . $e->getMessage());
        }
    }
}

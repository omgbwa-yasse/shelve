<?php

namespace App\Http\Controllers;

use App\Models\RecordPhysical;
use App\Models\RecordReactivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecordReactivationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', RecordReactivation::class);

        $query = RecordReactivation::query();

        if (!Auth::user()->isSuperAdmin()) {
            $query->byOrganisation(Auth::user()->current_organisation_id);
        }

        $reactivations = $query->with(['record', 'previousStatus', 'requestedBy', 'approvedBy'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('record-reactivations.index', compact('reactivations'));
    }

    public function create(RecordPhysical $record)
    {
        $this->authorize('create', RecordReactivation::class);

        return view('record-reactivations.create', compact('record'));
    }

    public function store(Request $request, RecordPhysical $record)
    {
        $this->authorize('create', RecordReactivation::class);

        $request->validate([
            'reason' => 'required',
            'new_transfer_date' => 'nullable|date',
        ]);

        $reactivation = RecordReactivation::create([
            'record_physical_id' => $record->id,
            'previous_status_id' => $record->status_id,
            'reason' => $request->input('reason'),
            'new_transfer_date' => $request->input('new_transfer_date'),
            'requested_by' => Auth::id(),
            'requested_date' => now(),
        ]);

        return redirect()->route('record-reactivations.index')
            ->with('success', 'Demande de réactivation envoyée.');
    }

    public function approve(Request $request, RecordReactivation $reactivation)
    {
        $this->authorize('update', $reactivation);

        if ($reactivation->is_approved) {
            return back()->with('error', 'Cette demande a déjà été approuvée.');
        }

        $reactivation->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_date' => now(),
        ]);

        if ($reactivation->previous_status_id) {
            $reactivation->record->update(['status_id' => $reactivation->previous_status_id]);
        }

        return redirect()->route('record-reactivations.index')
            ->with('success', 'Réactivation approuvée : le dossier a retrouvé son statut antérieur.');
    }

    public function reject(Request $request, RecordReactivation $reactivation)
    {
        $this->authorize('update', $reactivation);

        $request->validate([
            'reason' => 'required',
        ]);

        $reactivation->update([
            'rejection_reason' => $request->input('reason'),
        ]);

        return redirect()->route('record-reactivations.index')
            ->with('success', 'Demande de réactivation rejetée.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\RecordReactivation;
use App\Models\RecordStatus;
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

    public function create(Record $record)
    {
        $this->authorize('create', RecordReactivation::class);
        abort_if($record->destruction_effective_date, 409, 'Une archive effectivement détruite ne peut pas être réactivée.');

        return view('record-reactivations.create', compact('record'));
    }

    public function store(Request $request, Record $record)
    {
        $this->authorize('create', RecordReactivation::class);
        abort_if($record->destruction_effective_date, 409, 'Une archive effectivement détruite ne peut pas être réactivée.');

        $request->validate([
            'reason' => 'required',
            'new_transfer_date' => 'nullable|date',
        ]);

        $reactivation = RecordReactivation::create([
            'record_id' => $record->id,
            'organisation_id' => $record->organisation_id,
            'previous_status_id' => $record->status_id,
            'previous_transfer_date' => $record->transfer_effective_date,
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

        $activeStatusId = RecordStatus::where('name', 'Publié')->value('id')
            ?? RecordStatus::where('name', 'Brouillon')->value('id');

        $reactivation->record->update(array_filter([
            'status_id' => $activeStatusId,
            'closing_date' => null,
            'transfer_approved_date' => null,
            'transfer_effective_date' => null,
            'deposit_approved_date' => null,
            'deposit_effective_date' => null,
            'archival_status_gvaa' => 'reactivated',
        ], fn ($value, $key) => $value !== null || in_array($key, [
            'closing_date',
            'transfer_approved_date',
            'transfer_effective_date',
            'deposit_approved_date',
            'deposit_effective_date',
        ], true), ARRAY_FILTER_USE_BOTH));

        return redirect()->route('record-reactivations.index')
            ->with('success', 'Réactivation approuvée : le dossier est de nouveau actif.');
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

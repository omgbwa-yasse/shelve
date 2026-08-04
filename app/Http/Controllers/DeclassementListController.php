<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\DeclassementComment;
use App\Models\DeclassementList;
use App\Models\DeclassementRecord;
use App\Models\DeclassementStatus;
use App\Models\RecordPhysical;
use App\Models\RecordStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeclassementListController extends Controller
{
    /**
     * Récupère (ou crée si absent) un statut de déclassement par son libellé.
     */
    private function statusByName(string $name): DeclassementStatus
    {
        return DeclassementStatus::firstOrCreate(['name' => $name]);
    }

    public function index()
    {
        return $this->sort(request());
    }

    public function sort(Request $request)
    {
        $categ = $request->input('categ', 'draft');

        $query = DeclassementList::query();

        if (!Auth::user()->isSuperAdmin()) {
            $query->byOrganisation(Auth::user()->current_organisation_id);
        }

        switch ($categ) {
            case 'requested':
                $query->where('is_approval_requested', true)->where('is_approved', false);
                break;
            case 'approved':
                $query->where('is_approved', true)->where('is_validated', false);
                break;
            case 'validated':
                $query->where('is_validated', true)->where('is_treated', false);
                break;
            case 'treated':
                $query->where('is_treated', true);
                break;
            case 'draft':
            default:
                $query->where('is_approval_requested', false);
                break;
        }

        $declassementLists = $query->with(['status', 'creator', 'records'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('declassement-lists.index', compact('declassementLists', 'categ'));
    }

    public function eligibleRecords(Request $request)
    {
        $activityId = $request->input('activity_id');

        $records = DeclassementList::eligibleRecordsQuery($activityId ? (int) $activityId : null)
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($records);
        }

        $activities = Activity::select('id', 'name')->orderBy('name')->get();

        return view('declassement-lists.eligible-records', compact('records', 'activities', 'activityId'));
    }

    public function create()
    {
        $activities = Activity::select('id', 'name')->orderBy('name')->get();

        return view('declassement-lists.create', compact('activities'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DeclassementList::class);

        $request->validate([
            'code' => 'required|max:20|unique:declassement_lists,code',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'record_ids' => 'nullable|array',
            'record_ids.*' => 'exists:record_physicals,id',
            'generate_from_query' => 'nullable|boolean',
            'activity_id' => 'nullable|exists:activities,id',
        ]);

        $declassementList = DeclassementList::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'declassement_status_id' => $this->statusByName('Brouillon')->id,
            'creator_id' => Auth::id(),
            'query_criteria' => $request->boolean('generate_from_query')
                ? ['sort_code' => 'E', 'activity_id' => $request->input('activity_id')]
                : null,
        ]);

        $recordIds = $request->input('record_ids', []);

        if ($request->boolean('generate_from_query')) {
            $recordIds = array_unique(array_merge(
                $recordIds,
                DeclassementList::eligibleRecordsQuery($request->input('activity_id'))->pluck('record_physicals.id')->all()
            ));
        }

        foreach ($recordIds as $recordId) {
            DeclassementRecord::firstOrCreate([
                'declassement_list_id' => $declassementList->id,
                'record_physical_id' => $recordId,
            ], [
                'added_by' => Auth::id(),
            ]);
        }

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Liste de déclassement créée.');
    }

    public function show(DeclassementList $declassementList)
    {
        $this->authorize('view', $declassementList);

        $declassementList->load([
            'status',
            'creator',
            'records.record.activity',
            'records.record.status',
            'records.addedBy',
            'comments.user',
            'approvalRequestedBy',
            'approvedBy',
            'validatedBy',
            'treatedBy',
        ]);

        return view('declassement-lists.show', compact('declassementList'));
    }

    public function edit(DeclassementList $declassementList)
    {
        $this->authorize('update', $declassementList);

        if ($declassementList->is_approval_requested) {
            return redirect()->route('declassement-lists.show', $declassementList)
                ->with('error', "Impossible de modifier une liste déjà soumise pour approbation.");
        }

        return view('declassement-lists.edit', compact('declassementList'));
    }

    public function update(Request $request, DeclassementList $declassementList)
    {
        $this->authorize('update', $declassementList);

        if ($declassementList->is_approval_requested) {
            return redirect()->route('declassement-lists.show', $declassementList)
                ->with('error', "Impossible de modifier une liste déjà soumise pour approbation.");
        }

        $request->validate([
            'name' => 'required|max:200',
            'description' => 'nullable',
        ]);

        $declassementList->update($request->only('name', 'description'));

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Liste de déclassement mise à jour.');
    }

    public function destroy(DeclassementList $declassementList)
    {
        $this->authorize('delete', $declassementList);

        if ($declassementList->is_approval_requested) {
            return redirect()->route('declassement-lists.index')
                ->with('error', "Impossible de supprimer une liste déjà soumise pour approbation.");
        }

        $declassementList->delete();

        return redirect()->route('declassement-lists.index')
            ->with('success', 'Liste de déclassement supprimée.');
    }

    public function addRecords(Request $request, DeclassementList $declassementList)
    {
        $this->authorize('update', $declassementList);

        if ($declassementList->is_approval_requested) {
            return redirect()->route('declassement-lists.show', $declassementList)
                ->with('error', "Impossible de modifier une liste déjà soumise pour approbation.");
        }

        $request->validate([
            'record_ids' => 'required|array',
            'record_ids.*' => 'exists:record_physicals,id',
        ]);

        foreach ($request->input('record_ids') as $recordId) {
            DeclassementRecord::firstOrCreate([
                'declassement_list_id' => $declassementList->id,
                'record_physical_id' => $recordId,
            ], [
                'added_by' => Auth::id(),
            ]);
        }

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Dossiers ajoutés à la liste.');
    }

    public function removeRecord(DeclassementList $declassementList, DeclassementRecord $declassementRecord)
    {
        $this->authorize('update', $declassementList);

        if ($declassementList->is_approval_requested) {
            return redirect()->route('declassement-lists.show', $declassementList)
                ->with('error', "Impossible de modifier une liste déjà soumise pour approbation.");
        }

        abort_unless($declassementRecord->declassement_list_id === $declassementList->id, 404);

        $declassementRecord->delete();

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Dossier retiré de la liste.');
    }

    public function comment(Request $request, DeclassementList $declassementList)
    {
        $this->authorize('view', $declassementList);

        $request->validate([
            'content' => 'required',
        ]);

        DeclassementComment::create([
            'declassement_list_id' => $declassementList->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Commentaire ajouté.');
    }

    public function requestApproval(Request $request, DeclassementList $declassementList)
    {
        $this->authorize('update', $declassementList);

        if ($declassementList->records()->count() === 0) {
            return back()->with('error', "La liste ne contient aucun dossier.");
        }

        $declassementList->update([
            'is_approval_requested' => true,
            'approval_requested_by' => Auth::id(),
            'approval_requested_date' => now(),
            'declassement_status_id' => $this->statusByName("Demande d'approbation soumise")->id,
        ]);

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', "Demande d'approbation envoyée.");
    }

    public function approve(Request $request, DeclassementList $declassementList)
    {
        $this->authorize('update', $declassementList);

        if (!$declassementList->is_approval_requested) {
            return back()->with('error', "Cette liste n'a pas encore été soumise pour approbation.");
        }

        $declassementList->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_date' => now(),
            'declassement_status_id' => $this->statusByName('Approuvé')->id,
        ]);

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Liste approuvée.');
    }

    public function validateList(Request $request, DeclassementList $declassementList)
    {
        $this->authorize('update', $declassementList);

        if (!$declassementList->is_approved) {
            return back()->with('error', "Cette liste n'a pas encore été approuvée.");
        }

        $declassementList->update([
            'is_validated' => true,
            'validated_by' => Auth::id(),
            'validated_date' => now(),
            'declassement_status_id' => $this->statusByName('Validé')->id,
        ]);

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Liste validée.');
    }

    public function process(Request $request, DeclassementList $declassementList)
    {
        $this->authorize('update', $declassementList);

        if (!$declassementList->is_validated) {
            return back()->with('error', "Cette liste n'a pas encore été validée.");
        }

        $eliminatedStatus = RecordStatus::where('name', 'Éliminé')->first();

        foreach ($declassementList->records()->with('record')->get() as $declassementRecord) {
            if ($declassementRecord->record && $eliminatedStatus) {
                $declassementRecord->record->update(['status_id' => $eliminatedStatus->id]);
            }
        }

        $declassementList->update([
            'is_treated' => true,
            'treated_by' => Auth::id(),
            'treated_date' => now(),
            'declassement_status_id' => $this->statusByName('Traité')->id,
        ]);

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Liste traitée : les dossiers ont été marqués comme éliminés.');
    }

    public function reject(Request $request, DeclassementList $declassementList)
    {
        $this->authorize('update', $declassementList);

        $request->validate([
            'reason' => 'required',
        ]);

        $declassementList->update([
            'is_approval_requested' => false,
            'is_approved' => false,
            'is_validated' => false,
            'rejection_reason' => $request->input('reason'),
            'declassement_status_id' => $this->statusByName('Rejeté')->id,
        ]);

        DeclassementComment::create([
            'declassement_list_id' => $declassementList->id,
            'user_id' => Auth::id(),
            'content' => 'Rejet : ' . $request->input('reason'),
        ]);

        return redirect()->route('declassement-lists.show', $declassementList)
            ->with('success', 'Liste rejetée et renvoyée pour correction.');
    }
}

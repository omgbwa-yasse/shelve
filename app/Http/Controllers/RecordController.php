<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordMedium;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\RecordType;
use App\Models\ReferenceList;
use App\Models\User;
use App\Services\MetadataValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Contrôleur unifié des notices (Phase 5).
 *
 * Remplace RecordController (physique) + Web\FolderController + Web\DocumentController :
 * une seule requête SQL sur `records` + `record_mediums`. Les opérations fichier
 * (checkout/signature/version) s'appliquent à un `RecordMedium`, pas à la notice.
 */
class RecordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Lecture
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        Gate::authorize('records_view');

        $query = Record::query()
            ->with(['type', 'level', 'status', 'activity', 'organisation', 'creator', 'parent', 'mediums.support', 'mediums.attachment'])
            ->currentVersion()
            ->whereNull('deleted_at');

        $this->applyOrganisationScope($query);

        if ($keyword = $request->input('keyword_filter')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    // Les anciens champs descriptifs (content, etc.) vivent désormais dans
                    // `metadata` (JSON) : recherche sur le blob complet plutôt qu'une colonne.
                    ->orWhereRaw('CAST(metadata AS CHAR) LIKE ?', ["%{$keyword}%"]);
            });
        }

        if ($typeId = $request->input('type_filter')) {
            $query->where('type_id', $typeId);
        }

        if ($medium = $request->input('medium_filter')) {
            if ($medium === 'physical') {
                $query->whereHas('mediums', fn ($q) => $q->physical());
            } elseif ($medium === 'digital') {
                $query->whereHas('mediums', fn ($q) => $q->digital());
            }
        }

        $records = $query->orderBy('updated_at', 'desc')->paginate(15);

        session(['records.back_url' => $request->fullUrl()]);
        session(['records.list_ids' => $records->pluck('id')->all()]);

        return view('records.index', [
            'records' => $records,
            'types' => RecordType::active()->ordered()->get(),
            'statuses' => RecordStatus::all(),
            'organisations' => Organisation::all(),
            'title' => 'Archives',
            'query' => $request->input('keyword_filter'),
        ]);
    }

    public function search(Request $request)
    {
        Gate::authorize('records_view');

        $query = Record::query()
            ->with(['type', 'level', 'status', 'organisation', 'mediums.support', 'mediums.attachment'])
            ->currentVersion();

        $this->applyOrganisationScope($query);

        $term = trim($request->input('q', $request->input('search', '')));

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    // content/archivist_note/note vivent désormais dans `metadata` (JSON).
                    ->orWhereRaw('CAST(metadata AS CHAR) LIKE ?', ["%{$term}%"]);
            });
        }

        if ($keyword = $request->input('keyword_filter')) {
            $query->whereHas('keywords', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
        }

        $records = $query->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();

        return view('records.index', [
            'records' => $records,
            'types' => RecordType::active()->ordered()->get(),
            'statuses' => RecordStatus::all(),
            'organisations' => Organisation::all(),
            'title' => 'Recherche',
            'query' => $term,
        ]);
    }

    /**
     * Vue filtrée sur les notices à support physique (remplace records.physical).
     */
    public function indexPhysical(Request $request)
    {
        Gate::authorize('records_view');

        $query = Record::query()
            ->with(['type', 'level', 'status', 'organisation', 'mediums.support', 'mediums.attachment'])
            ->currentVersion()
            ->whereHas('mediums', fn ($q) => $q->physical());

        $this->applyOrganisationScope($query);

        $records = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('records.index', [
            'records' => $records,
            'types' => RecordType::active()->ordered()->get(),
            'statuses' => RecordStatus::all(),
            'organisations' => Organisation::all(),
            'title' => 'Archives physiques',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Création
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        Gate::authorize('records_create');

        return view('records.create', [
            'types' => RecordType::active()->ordered()->get(),
            'levels' => RecordLevel::all(),
            'statuses' => RecordStatus::all(),
            'supports' => RecordSupport::all(),
            'activities' => \App\Models\Activity::orderBy('name')->get(),
            'parents' => Record::currentVersion()->whereHas('type', fn ($q) => $q->where('is_container', true))->orderBy('name')->get(),
            'containers' => \App\Models\Container::all(),
            'users' => User::all(),
            'organisations' => Organisation::all(),
            'confidentialities' => \App\Models\RecordConfidentiality::all(),
            'accessLimits' => ReferenceList::all(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('records_create');

        $data = $this->validateRecord($request);

        $created = null;

        DB::transaction(function () use ($request, $data, &$created) {
            $record = new Record($data);
            $record->creator_id = $request->user()->id;
            $record->is_current_version = true;
            $record->version_number = 1;

            if (empty($record->code) && $record->type) {
                $record->code = $record->type->generateCode();
            }

            $record->save();
            $created = $record;

            if (! empty($data['metadata']) && is_array($data['metadata'])) {
                $record->setMultipleMetadata($data['metadata']);
                $record->save();
            }

            // Support physique ou numérique attaché à la création.
            if ($request->input('support_id')) {
                $this->createMediumFromRequest($record, $request);
            }
        });

        return redirect()->route('records.show', $created)->with('success', 'Notice créée.');
    }

    /*
    |--------------------------------------------------------------------------
    | Affichage / édition
    |--------------------------------------------------------------------------
    */
    public function show(Record $record)
    {
        Gate::authorize('records_view');

        $record->load([
            'type', 'level', 'status', 'activity', 'organisation', 'creator', 'assignedUser', 'approver',
            'parent', 'children', 'mediums.support', 'mediums.container', 'mediums.attachment', 'mediums.checkedOutUser', 'mediums.signer',
            'authors', 'keywords', 'thesaurusConcepts',
        ]);

        $listIds = session('records.list_ids', []);
        $prevId = null;
        $nextId = null;

        if (($pos = array_search($record->id, $listIds)) !== false) {
            $prevId = $listIds[$pos - 1] ?? null;
            $nextId = $listIds[$pos + 1] ?? null;
        }

        return view('records.show', compact('record', 'prevId', 'nextId'));
    }

    public function edit(Record $record)
    {
        Gate::authorize('records_update');

        return view('records.edit', [
            'record' => $record,
            'types' => RecordType::active()->ordered()->get(),
            'levels' => RecordLevel::all(),
            'statuses' => RecordStatus::all(),
            'supports' => RecordSupport::all(),
            'activities' => \App\Models\Activity::orderBy('name')->get(),
            'parents' => Record::currentVersion()->whereHas('type', fn ($q) => $q->where('is_container', true))->where('id', '!=', $record->id)->orderBy('name')->get(),
            'containers' => \App\Models\Container::all(),
            'users' => User::all(),
            'organisations' => Organisation::all(),
            'confidentialities' => \App\Models\RecordConfidentiality::all(),
            'accessLimits' => ReferenceList::all(),
        ]);
    }

    public function update(Request $request, Record $record)
    {
        Gate::authorize('records_update');

        $data = $this->validateRecord($request, $record);

        DB::transaction(function () use ($record, $data) {
            $record->fill($data);
            $record->save();

            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $record->setMultipleMetadata($data['metadata']);
                $record->save();
            }
        });

        return redirect()->route('records.show', $record)->with('success', 'Notice mise à jour.');
    }

    public function destroy(Record $record)
    {
        Gate::authorize('records_delete');

        $record->delete();

        return redirect()->route('records.index')->with('success', 'Notice supprimée.');
    }

    /*
    |--------------------------------------------------------------------------
    | Corbeille (étape 1)
    |--------------------------------------------------------------------------
    */
    public function trash(Request $request)
    {
        Gate::authorize('records_view');

        $query = Record::onlyTrashed()
            ->with(['type', 'creator', 'organisation'])
            ->orderBy('deleted_at', 'desc');

        $this->applyOrganisationScope($query);

        $records = $query->paginate(20);

        return view('records.trash', compact('records'));
    }

    public function restore($id)
    {
        Gate::authorize('records_restore');

        $record = Record::withTrashed()->findOrFail($id);

        $record->restore();

        return redirect()->route('records.trash')->with('success', 'Notice restaurée.');
    }

    public function forceDelete($id)
    {
        Gate::authorize('records_force_delete');

        $record = Record::withTrashed()->findOrFail($id);

        $record->forceDelete();

        return redirect()->route('records.trash')->with('success', 'Notice supprimée définitivement.');
    }

    /*
    |--------------------------------------------------------------------------
    | Étape 9 — duplication & finalisation
    |--------------------------------------------------------------------------
    */
    public function duplicate(Request $request, Record $record)
    {
        Gate::authorize('records_update');

        $request->validate(['with_children' => 'boolean']);

        $withChildren = (bool) $request->boolean('with_children');

        $copy = $record->duplicate($withChildren);

        return redirect()->route('records.show', $copy)
            ->with('success', $withChildren
                ? 'Notice et arborescence dupliquées avec succès.'
                : 'Notice dupliquée avec succès.');
    }

    public function finalizeMedium(Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_update');

        $this->ensureMediumBelongs($record, $medium);

        try {
            $medium->finalize(auth()->user());
        } catch (\Exception $e) {
            return redirect()->route('records.show', $record)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('records.show', $record)
            ->with('success', 'Support finalisé (version majeure).');
    }

    /**
     * Déplacer une notice (générique, plus seulement pour les dossiers numériques).
     */
    public function move(Request $request, Record $record)
    {
        Gate::authorize('records_update');

        $request->validate(['parent_id' => 'nullable|exists:records,id']);

        $newParentId = $request->input('parent_id');

        if ($newParentId && $newParentId == $record->id) {
            return response()->json(['success' => false, 'message' => 'Une notice ne peut pas être son propre parent.'], 422);
        }

        if ($newParentId) {
            $newParent = Record::find($newParentId);
            if ($newParent && $newParent->isDescendantOf($record->id)) {
                return response()->json(['success' => false, 'message' => 'Impossible de déplacer une notice sous l\'un de ses descendants.'], 422);
            }
        }

        $record->update(['parent_id' => $newParentId]);

        return response()->json(['success' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Arbre
    |--------------------------------------------------------------------------
    */
    public function treeView()
    {
        Gate::authorize('records_view');

        return view('records.tree', [
            'types' => RecordType::active()->ordered()->get(),
            'organisations' => Organisation::all(),
        ]);
    }

    public function tree(Request $request)
    {
        Gate::authorize('records_view');

        $query = Record::query()->with('type')->currentVersion()->whereNull('deleted_at');

        $this->applyOrganisationScope($query);

        if ($request->input('container_only')) {
            $query->whereHas('type', fn ($q) => $q->where('is_container', true));
        }

        $roots = $query->whereNull('parent_id')->orderBy('name')->get();

        return response()->json($roots->map(fn ($r) => $this->buildTree($r))->all());
    }

    private function buildTree(Record $record): array
    {
        return [
            'id' => $record->id,
            'text' => ($record->code ? $record->code.' — ' : '').$record->name,
            'type' => $record->type?->code,
            'is_container' => (bool) $record->isContainer(),
            'children' => $record->children()
                ->currentVersion()
                ->with('type')
                ->orderBy('name')
                ->get()
                ->map(fn ($child) => $this->buildTree($child))
                ->all(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Supports (le cœur de l'unification : addMedium / removeMedium)
    |--------------------------------------------------------------------------
    */
    public function addMedium(Request $request, Record $record)
    {
        Gate::authorize('records_update');

        $request->validate([
            'support_id' => 'required|exists:record_supports,id',
            'container_id' => 'nullable|exists:containers,id',
            'linear_measure_cm' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file|max:51200',
        ]);

        $medium = new RecordMedium([
            'record_id' => $record->id,
            'support_id' => $request->input('support_id'),
            'container_id' => $request->input('container_id'),
            'linear_measure_cm' => $request->input('linear_measure_cm'),
        ]);

        if ($request->hasFile('attachment')) {
            $attachment = Attachment::createFromUpload(
                $request->file('attachment'),
                Attachment::TYPE_ATTACHMENT,
                $request->user()->id,
                ['is_primary' => true]
            );
            $medium->attachment_id = $attachment->id;
        }

        $medium->is_principal = $record->mediums()->count() === 0;
        $medium->save();

        return back()->with('success', 'Support ajouté à la notice.');
    }

    public function removeMedium(Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_update');

        if ($medium->record_id !== $record->id) {
            abort(404);
        }

        $medium->delete();

        return back()->with('success', 'Support retiré.');
    }

    /*
    |--------------------------------------------------------------------------
    | Opérations fichier (RecordMedium)
    |--------------------------------------------------------------------------
    */
    public function checkout(Request $request, Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_update');
        $this->ensureMediumBelongs($record, $medium);

        $medium->checkout($request->user());

        return back()->with('success', 'Support verrouillé (check-out).');
    }

    public function checkin(Request $request, Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_update');
        $this->ensureMediumBelongs($record, $medium);

        $medium->checkin($request->user());

        return back()->with('success', 'Support déverrouillé (check-in).');
    }

    public function cancelCheckout(Request $request, Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_update');
        $this->ensureMediumBelongs($record, $medium);

        $medium->cancelCheckout($request->user());

        return back()->with('success', 'Check-out annulé.');
    }

    public function sign(Request $request, Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_update');
        $this->ensureMediumBelongs($record, $medium);

        if (! Auth::validate(['email' => $request->user()->email, 'password' => $request->input('password')])) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        $medium->sign($request->user(), $request->input('signature_data'));

        return back()->with('success', 'Support signé.');
    }

    public function verifySignature(Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_view');
        $this->ensureMediumBelongs($record, $medium);

        $valid = $medium->verifySignature();

        return response()->json(['valid' => $valid]);
    }

    public function revokeSignature(Request $request, Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_update');
        $this->ensureMediumBelongs($record, $medium);

        $medium->revokeSignature($request->user());

        return back()->with('success', 'Signature révoquée.');
    }

    public function download(Record $record, RecordMedium $medium)
    {
        Gate::authorize('records_view');
        $this->ensureMediumBelongs($record, $medium);

        if (! $medium->attachment) {
            abort(404);
        }

        return $medium->attachment->download();
    }

    /*
    |--------------------------------------------------------------------------
    | Versionnement
    |--------------------------------------------------------------------------
    */
    public function createVersion(Request $request, Record $record)
    {
        Gate::authorize('records_update');

        $newVersion = $record->createNewVersion([
            'metadata' => $record->metadata,
            'description' => $record->description,
        ]);

        return redirect()->route('records.show', $newVersion)->with('success', 'Nouvelle version créée.');
    }

    public function versions(Record $record)
    {
        Gate::authorize('records_view');

        $record->load('mediums.attachment');

        return view('records.versions', ['record' => $record]);
    }

    /**
     * Export Excel des notices sélectionnées.
     */
    public function bulkExport(Request $request)
    {
        Gate::authorize('records_export');

        $ids = $request->input('record_ids', []);
        $records = \App\Models\Record::whereIn('id', $ids)->currentVersion()->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\UnifiedRecordsExport($records),
            'records-'.date('Ymd-His').'.xlsx'
        );
    }

    /**
     * Impression PDF des notices sélectionnées.
     */
    public function bulkPrint(Request $request)
    {
        Gate::authorize('records_export');

        $ids = $request->input('record_ids', []);
        $records = \App\Models\Record::whereIn('id', $ids)->currentVersion()->with(['type', 'level', 'status', 'activity'])->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('records.print-list', compact('records'));

        return $pdf->download('records-'.date('Ymd-His').'.pdf');
    }

    /**
     * Créer un bordereau (transfert) avec les notices sélectionnées.
     */
    public function bulkTransfer(Request $request)
    {
        Gate::authorize('records_update');

        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'record_ids' => 'required|array',
        ]);

        $defaultStatusId = \App\Models\SlipStatus::where('name', 'Projects')->value('id') ?? 1;

        $slip = \App\Models\Slip::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'officer_organisation_id' => $request->user()->current_organisation_id,
            'officer_id' => $request->user()->id,
            'user_organisation_id' => $request->input('user_organisation_id', $request->user()->current_organisation_id),
            'slip_status_id' => $defaultStatusId,
        ]);

        foreach ($request->input('record_ids') as $recordId) {
            $record = \App\Models\Record::find($recordId);
            if (! $record) {
                continue;
            }

            $supportId = \App\Models\RecordMedium::where('record_id', $recordId)->value('support_id') ?? 24;

            \App\Models\SlipRecord::create([
                'slip_id' => $slip->id,
                'code' => $record->code,
                'name' => $record->name,
                'date_format' => $record->date_format,
                'date_start' => $record->start_date,
                'date_end' => $record->end_date,
                'date_exact' => $record->date_exact,
                'content' => $record->getMetadataValue('content'),
                'level_id' => $record->level_id,
                'support_id' => $supportId,
                'activity_id' => $record->activity_id,
                'creator_id' => $request->user()->id,
            ]);
        }

        return redirect()->route('slips.index')
            ->with('success', 'Bordereau créé avec '.count($request->input('record_ids')).' notice(s).');
    }

    /**
     * Créer une communication avec les notices sélectionnées.
     */
    public function bulkCommunicate(Request $request)
    {
        Gate::authorize('communications_create');

        $request->validate([
            'name' => 'required|max:200',
            'content' => 'nullable',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'record_ids' => 'required|array',
        ]);

        $communication = \App\Models\Communication::create([
            'code' => app(\App\Services\CodeGeneratorService::class)->generateCommunicationCode(),
            'name' => $request->name,
            'content' => $request->content,
            'operator_id' => $request->user()->id,
            'operator_organisation_id' => $request->user()->current_organisation_id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'return_date' => $request->input('return_date', now()->addDays(30)->format('Y-m-d')),
            'status' => 'pending',
        ]);

        foreach ($request->input('record_ids') as $recordId) {
            $communication->records()->attach($recordId, [
                'operator_id' => $request->user()->id,
                'return_date' => $communication->return_date,
            ]);
        }

        return redirect()->route('communications.transactions.show', $communication)
            ->with('success', 'Communication créée.');
    }

    /**
     * Champs de métadonnées d'un type de notice (rendu dynamique du formulaire de création).
     */
    public function typeMetadataFields(Request $request)
    {
        Gate::authorize('records_create');

        $request->validate(['type_id' => 'required|exists:record_types,id']);

        $type = RecordType::findOrFail($request->input('type_id'));

        return response()->json(
            app(\App\Services\MetadataValidationService::class)->getRecordMetadataFields($type)
        );
    }

    /**
     * Autocomplétion du thésaurus (utilisée par le champ termes des formulaires).
     */
    public function autocompleteTerms(Request $request)
    {
        Gate::authorize('records_view');

        $term = $request->input('term', $request->input('q', ''));

        $concepts = \App\Models\ThesaurusConcept::query()
            ->with(['labels' => fn ($q) => $q->where('language', 'fr')->orderBy('literal_form')])
            ->where(function ($q) use ($term) {
                $q->whereHas('labels', fn ($l) => $l->where('literal_form', 'like', "%{$term}%"));
            })
            ->limit(20)
            ->get();

        return response()->json($concepts->map(function ($concept) {
            $label = $concept->labels->first()?->literal_form ?? $concept->uri ?? $concept->id;

            return ['id' => $concept->id, 'text' => $label, 'label' => $label];
        }));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function validateRecord(Request $request, ?Record $record = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'type_id' => 'nullable|exists:record_types,id',
            'level_id' => 'nullable|exists:record_levels,id',
            'status_id' => 'nullable|exists:record_statuses,id',
            'activity_id' => 'nullable|exists:activities,id',
            'parent_id' => 'nullable|exists:records,id',
            'organisation_id' => 'required|exists:organisations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'access_level' => 'nullable|string|max:20',
            'confidentiality_id' => 'nullable|exists:record_confidentialities,id',
            'access_limit_id' => 'nullable|exists:reference_lists,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'date_exact' => 'nullable|date',
            'date_format' => 'nullable|string|max:1',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date',
            'metadata' => 'nullable|array',
        ];

        $data = $request->validate($rules);

        // Un document (type non-conteneur) doit obligatoirement être classé dans un
        // dossier (type conteneur) ; un dossier peut rester à la racine.
        $typeIdForParentCheck = $data['type_id'] ?? $record?->type_id;
        if ($typeIdForParentCheck) {
            $selectedType = RecordType::find($typeIdForParentCheck);

            if ($selectedType && ! $selectedType->isContainer() && empty($data['parent_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'parent_id' => 'Un document doit être classé dans un dossier.',
                ]);
            }
        }

        if (! empty($data['parent_id'])) {
            $parentRecord = Record::find($data['parent_id']);

            if ($parentRecord && (! $parentRecord->type || ! $parentRecord->type->isContainer())) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'parent_id' => 'La notice parente doit être un dossier (type conteneur).',
                ]);
            }
        }

        if (empty($data['level_id'])) {
            $data['level_id'] = RecordLevel::query()->value('id');
        }

        if (empty($data['status_id'])) {
            $data['status_id'] = RecordStatus::query()->value('id');
        }

        if (empty($data['access_level'])) {
            $data['access_level'] = 'internal';
        }

        // Les anciens champs descriptifs figés (content, biographical_history, extent,
        // quantity, ...) sont désormais des MetadataDefinition (système ou personnalisées)
        // rattachées au RecordType via record_type_metadata_profiles — validées
        // dynamiquement plutôt que par une liste de règles codées en dur.
        $typeId = $data['type_id'] ?? $record?->type_id;

        // Toujours valider (même sans `metadata` envoyé) : un type peut avoir des
        // métadonnées obligatoires, auquel cas leur absence doit être rejetée.
        if ($typeId) {
            $type = RecordType::find($typeId);

            if ($type) {
                app(MetadataValidationService::class)->validateRecordMetadata(
                    $type,
                    $data['metadata'] ?? [],
                    $record?->id
                );
            }
        }

        return $data;
    }

    private function createMediumFromRequest(Record $record, Request $request): void
    {
        $medium = new RecordMedium([
            'record_id' => $record->id,
            'support_id' => $request->input('support_id'),
            'container_id' => $request->input('container_id'),
        ]);

        if ($request->hasFile('attachment')) {
            $attachment = Attachment::createFromUpload(
                $request->file('attachment'),
                Attachment::TYPE_ATTACHMENT,
                $request->user()->id,
                ['is_primary' => true]
            );
            $medium->attachment_id = $attachment->id;
        }

        $medium->is_principal = true;
        $medium->save();
    }

    private function ensureMediumBelongs(Record $record, RecordMedium $medium): void
    {
        if ($medium->record_id !== $record->id) {
            abort(404);
        }
    }

    private function applyOrganisationScope($query): void
    {
        $user = auth()->user();

        if ($user && ! $user->hasRole('superadmin')) {
            $query->where('organisation_id', $user->current_organisation_id ?? $user->organisation_id);
        }
    }
}

<?php

namespace App\Http\Controllers;
use App\Models\Record;
use App\Models\RecordSupport;
use App\Models\RecordStatus;
use App\Models\Container;
use App\Models\Activity;
use App\Models\Term;
use App\Models\Accession;
use App\Models\Author;
use App\Models\RecordLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class RecordController extends Controller
{
    public function createChild(Record $record)
    {
        return view('records.create_child', compact('record'));
    }


    public function storeChild(Request $request, Record $record)
    {
        $validatedData = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string',
            'date_format' => 'required|string|max:1',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'level_id' => 'required|integer|exists:record_levels,id',
            'width' => 'nullable|numeric|between:0,99999999.99',
            'width_description' => 'nullable|string|max:100',
            'biographical_history' => 'nullable|string',
            'archival_history' => 'nullable|string',
            'acquisition_source' => 'nullable|string',
            'content' => 'nullable|string',
            'appraisal' => 'nullable|string',
            'accrual' => 'nullable|string',
            'arrangement' => 'nullable|string',
            'access_conditions' => 'nullable|string|max:50',
            'reproduction_conditions' => 'nullable|string|max:50',
            'language_material' => 'nullable|string|max:50',
            'characteristic' => 'nullable|string|max:100',
            'finding_aids' => 'nullable|string|max:100',
            'location_original' => 'nullable|string|max:100',
            'location_copy' => 'nullable|string|max:100',
            'related_unit' => 'nullable|string|max:100',
            'publication_note' => 'nullable|string',
            'note' => 'nullable|string',
            'archivist_note' => 'nullable|string',
            'rule_convention' => 'nullable|string|max:100',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
            'status_id' => 'required|integer|exists:record_statuses,id',
            'support_id' => 'required|integer|exists:record_supports,id',
            'activity_id' => 'required|integer|exists:activities,id',
            'parent_id' => 'nullable|integer|exists:records,id',
            'container_id' => 'nullable|integer|exists:containers,id',
            'accession_id' => 'nullable|integer|exists:accessions,id',
            'user_id' => 'required|integer|exists:users,id',
            'author_ids' => 'required|array',
            'term_ids' => 'required|array',
        ]);


        $childRecord = new Record($validatedData);
        $childRecord->parent_id = $record->id;
        $childRecord->save();

        return redirect()->route('records.show', $record->id)->with('success', 'Enregistrement enfant créé avec succès.');
    }

    public function index()
    {
        $records = Record::with(['level','attachments','status', 'support', 'activity', 'parent', 'container', 'user', 'authors', 'terms'])
            ->paginate(10);
        $statuses = RecordStatus::all();
        $terms = Term::all();
        return view('records.index', compact('records', 'statuses', 'terms'));
    }


    public function create()
    {
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $parents = Record::all();
        $containers = Container::all();
//        $accessions = Accession::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $records = Record::all();
        $authors = Author::with('authorType')->get();
        $terms = Term::all();
        return view('records.create', compact('records','terms','authors','levels','statuses', 'supports', 'activities', 'parents', 'containers', 'users'));
    }


    public function store(Request $request)
    {
        // Définissez une valeur par défaut pour date_format
        $request->merge(['date_format' => $request->input('date_format', 'Y')]);
        $request->merge(['user_id' => Auth::id()]);
//         dd($request);
        $validatedData = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string',
            'date_format' => 'required|string|max:1',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'level_id' => 'required|integer|exists:record_levels,id',
            'width' => 'nullable|numeric|between:0,99999999.99',
            'width_description' => 'nullable|string|max:100',
            'biographical_history' => 'nullable|string',
            'archival_history' => 'nullable|string',
            'acquisition_source' => 'nullable|string',
            'content' => 'nullable|string',
            'appraisal' => 'nullable|string',
            'accrual' => 'nullable|string',
            'arrangement' => 'nullable|string',
            'access_conditions' => 'nullable|string|max:50',
            'reproduction_conditions' => 'nullable|string|max:50',
            'language_material' => 'nullable|string|max:50',
            'characteristic' => 'nullable|string|max:100',
            'finding_aids' => 'nullable|string|max:100',
            'location_original' => 'nullable|string|max:100',
            'location_copy' => 'nullable|string|max:100',
            'related_unit' => 'nullable|string|max:100',
            'publication_note' => 'nullable|string',
            'note' => 'nullable|string',
            'archivist_note' => 'nullable|string',
            'rule_convention' => 'nullable|string|max:100',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
            'status_id' => 'required|integer|exists:record_statuses,id',
            'support_id' => 'required|integer|exists:record_supports,id',
            'activity_id' => 'required|integer|exists:activities,id',
            'parent_id' => 'nullable|integer|exists:records,id',
            'container_id' => 'nullable|integer|exists:containers,id',
            'accession_id' => 'nullable|integer|exists:accessions,id',
            'user_id' => 'required|integer|exists:users,id',
            'author_ids' => 'required|array',
            'term_ids' => 'required|array',
        ]);

        $record = Record::create($validatedData);


        $term_ids = $request->input('term_ids');
        $author_ids = $request->input('author_ids');
        $term_ids = explode(',', $term_ids[0]);

        $author_ids = explode(',', $author_ids[0]);

        $term_ids = array_map('intval', $term_ids);
        $author_ids = array_map('intval', $author_ids);


        foreach ($author_ids as $author_id) {
            $record->authors()->attach($author_id);
        }

        foreach ($term_ids as $term_id) {
            $record->terms()->attach($term_id);
        }

        return redirect()->route('records.index')->with('success', 'Record created successfully.');
    }

    public function show(Record $record)
    {
        return view('records.show', compact('record'));
    }


    public function edit(Record $record)
    {
        $authors = Author::with('authorType')->get();
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $parents = Record::all();
        $containers = Container::all();
        $accessions = Accession::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $terms = Term::all();


        $author_ids = $record->authors->pluck('id')->toArray();
        $term_ids = $record->terms->pluck('id')->toArray();

        return view('records.edit', compact('levels', 'record', 'statuses', 'supports', 'activities', 'parents', 'containers', 'accessions', 'users', 'authors', 'author_ids', 'terms', 'term_ids'));
    }

    public function update(Request $request, Record $record)
    {

        $request->merge(['date_format' => $request->input('date_format', 'Y')]);
        $request->merge(['user_id' => Auth::id()]);
        $validatedData = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string',
            'date_format' => 'required|string|max:1',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'level_id' => 'required|integer|exists:record_levels,id',
            'width' => 'nullable|numeric|between:0,99999999.99',
            'width_description' => 'nullable|string|max:100',
            'biographical_history' => 'nullable|string',
            'archival_history' => 'nullable|string',
            'acquisition_source' => 'nullable|string',
            'content' => 'nullable|string',
            'appraisal' => 'nullable|string',
            'accrual' => 'nullable|string',
            'arrangement' => 'nullable|string',
            'access_conditions' => 'nullable|string|max:50',
            'reproduction_conditions' => 'nullable|string|max:50',
            'language_material' => 'nullable|string|max:50',
            'characteristic' => 'nullable|string|max:100',
            'finding_aids' => 'nullable|string|max:100',
            'location_original' => 'nullable|string|max:100',
            'location_copy' => 'nullable|string|max:100',
            'related_unit' => 'nullable|string|max:100',
            'publication_note' => 'nullable|string',
            'note' => 'nullable|string',
            'archivist_note' => 'nullable|string',
            'rule_convention' => 'nullable|string|max:100',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
            'status_id' => 'required|integer|exists:record_statuses,id',
            'support_id' => 'required|integer|exists:record_supports,id',
            'activity_id' => 'required|integer|exists:activities,id',
            'parent_id' => 'nullable|integer|exists:records,id',
            'container_id' => 'nullable|integer|exists:containers,id',
            'accession_id' => 'nullable|integer|exists:accessions,id',
            'user_id' => 'required|integer|exists:users,id',
            'author_ids' => 'required|array',
            'term_ids' => 'required|array',
        ]);

        // Mettez à jour l'enregistrement
        $record->update($validatedData);
        // Supprimez les clés author_ids et term_ids du tableau $validatedData
        $term_ids = $request->input('term_ids');
        $author_ids = $request->input('author_ids');
        $term_ids = explode(',', $term_ids[0]);

        $author_ids = explode(',', $author_ids[0]);
// Supprimez les valeurs vides du tableau
//        $term_ids = array_filter($term_ids);
//        $author_ids = array_filter($author_ids);

// Convertissez les valeurs en entiers

        $term_ids = array_map('intval', $term_ids);
        $author_ids = array_map('intval', $author_ids);

        // Mettez à jour les relations entre les auteurs et l'enregistrement
        $record->authors()->sync($term_ids);

        // Mettez à jour les relations entre les termes et l'enregistrement
        $record->terms()->sync($author_ids);

        return redirect()->route('records.index')->with('success', 'Record updated successfully.');
    }

    public function destroy(Record $record)
    {
        $record->delete();

        return redirect()->route('records.index')->with('success', 'Record deleted successfully.');
    }
}

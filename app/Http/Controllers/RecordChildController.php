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

class RecordChildController extends Controller
{
    public function index(Record $parent)
    {
        $children = $parent->children;
        return view('records.child.index', compact('parent', 'record'));
    }


    public function create(Record $parent)
    {
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $records = Record::all();
        $authors = Author::with('authorType')->get();
        $terms = Term::all();
        return view('records.child.create', compact('parent','terms','authors','levels','statuses', 'supports', 'activities', 'containers', 'users'));
    }



    public function store(Request $request, Record $parent)
    {
        $validatedData =  $request->validate([
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

        // Association au parent
        $record->parent()->attach($parent->id);


        // Enregistrement des mots du thésaurus
        $term_ids = $request->input('term_ids');
        $term_ids = explode(',', $term_ids[0]);
        $term_ids = array_map('intval', $term_ids);
        foreach ($term_ids as $term_id) {
            $record->terms()->attach($term_id);
        }

        // Enregistrement des auteurs
        $author_ids = $request->input('author_ids');
        $author_ids = explode(',', $author_ids[0]);
        $author_ids = array_map('intval', $author_ids);
        foreach ($author_ids as $author_id) {
            $record->authors()->attach($author_id);
        }


        return redirect()->route('record-child.index', $record)->with('success', 'Child record created successfully.');
    }



    public function edit(Record $parent, Record $record)
    {
        return view('records.child.edit', compact('record', 'child'));
    }


    public function update(Request $request, Record $parent, Record $record)
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

        // Mise à jour de l'enregistrement
        $record->update($validatedData);

        // Association au parent
        $record->parent()->associate($parent);
        $record->save();

        // Enregistrement des mots du thésaurus
        $term_ids = $request->input('term_ids');
        $record->terms()->sync($term_ids);

        // Enregistrement des auteurs
        $author_ids = $request->input('author_ids');
        $record->authors()->sync($author_ids);

        return redirect()->route('record-child.index', $parent)->with('success', 'Child record updated successfully.');
    }



    public function destroy(Record $parent, Record $record)
    {
        if ($record->parent_id !== $parent->id) {
            return redirect()->route('record-child.index', $parent)->with('error', 'Child record does not belong to the specified parent.');
        }

        $record->delete();

        return redirect()->route('record-child.index', $parent)->with('success', 'Child record deleted successfully.');
    }
}

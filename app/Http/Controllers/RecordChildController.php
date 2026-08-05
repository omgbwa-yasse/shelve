<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Author;
use App\Models\Container;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\RecordType;
use App\Models\User;
use App\Services\MetadataValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Notices filles d'une notice unifiée (Record::parent_id). Le paramètre de route
 * généré par `Route::resource('records.child', ...)` s'appelle `{record}` (pas
 * `{parent}`) : les méthodes doivent type-hinter `$record` sous peine de voir
 * Laravel résoudre un `Record` vide via le conteneur au lieu de faire le binding
 * (bug historique, présent avant le portage vers le modèle unifié).
 */
class RecordChildController extends Controller
{
    public function index(Record $record)
    {
        $parent = $record;

        return view('records.child.index', compact('parent'));
    }

    public function create(Record $record)
    {
        $statuses = RecordStatus::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $authors = Author::with('authorType')->get();
        $terms = [];

        return view('records.child.create', compact('record', 'authors', 'terms', 'levels', 'statuses', 'supports', 'activities', 'containers', 'users'));
    }

    public function store(Request $request, Record $record)
    {
        $parent = $record;

        $validatedData = $request->validate([
            'code' => 'nullable|string|max:30',
            'name' => 'required|string',
            'date_format' => 'nullable|string|max:1',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'level_id' => 'nullable|integer|exists:record_levels,id',
            'status_id' => 'nullable|integer|exists:record_statuses,id',
            'activity_id' => 'nullable|integer|exists:activities,id',
            // Les anciens champs descriptifs (biographical_history, content, ...) sont
            // désormais des MetadataDefinition rattachées au type de la notice — validées
            // dynamiquement via MetadataValidationService, pas par une liste figée ici.
            'metadata' => 'nullable|array',
            'author_ids' => 'nullable|array',
            'term_ids' => 'nullable|array',
        ]);

        $typeId = $parent->type_id;

        // Toujours valider : le type hérité du parent peut avoir des métadonnées
        // obligatoires, même si le client n'envoie aucun `metadata`.
        if ($typeId) {
            $type = RecordType::find($typeId);

            if ($type) {
                app(MetadataValidationService::class)->validateRecordMetadata($type, $validatedData['metadata'] ?? []);
            }
        }

        $child = new Record([
            'code' => $validatedData['code'] ?? null,
            'name' => $validatedData['name'],
            'date_format' => $validatedData['date_format'] ?? null,
            'start_date' => $validatedData['date_start'] ?? null,
            'end_date' => $validatedData['date_end'] ?? null,
            'date_exact' => $validatedData['date_exact'] ?? null,
            'level_id' => $validatedData['level_id'] ?? $parent->level_id,
            'status_id' => $validatedData['status_id'] ?? RecordStatus::query()->value('id'),
            'activity_id' => $validatedData['activity_id'] ?? $parent->activity_id,
            'parent_id' => $parent->id,
            'type_id' => $typeId,
            'organisation_id' => $parent->organisation_id,
            'access_level' => $parent->access_level ?? 'internal',
            'version_number' => 1,
            'is_current_version' => true,
        ]);

        $child->creator_id = $request->user()?->id;

        if (empty($child->code)) {
            $child->code = 'REC-' . Str::upper(Str::random(8));
        }

        $child->save();

        if (!empty($validatedData['metadata']) && is_array($validatedData['metadata'])) {
            $child->setMultipleMetadata($validatedData['metadata']);
            $child->save();
        }

        foreach (($validatedData['term_ids'] ?? []) as $conceptId) {
            $child->thesaurusConcepts()->attach((int) $conceptId, ['weight' => 1.0]);
        }

        foreach (($validatedData['author_ids'] ?? []) as $authorId) {
            $child->authors()->attach((int) $authorId);
        }

        return redirect()->route('record-child.index', $parent->id)->with('success', 'Child record created successfully.');
    }
}

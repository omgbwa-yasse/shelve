@php($record = $record ?? null)
@php($metadataFields = $record?->type ? app(\App\Services\MetadataValidationService::class)->getRecordMetadataFields($record->type) : [])

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nom / Titre <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $record?->name) }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Code</label>
        <input type="text" name="code" value="{{ old('code', $record?->code) }}" class="form-control" placeholder="Auto si vide">
    </div>
    <div class="col-md-3">
        <label class="form-label">Type</label>
        <select name="type_id" class="form-select" id="recordTypeSelect">
            <option value="">—</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" data-container="{{ $type->is_container ? '1' : '0' }}" @selected(old('type_id', $record?->type_id) == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Niveau (ISAD(G))</label>
        <select name="level_id" class="form-select">
            @foreach($levels as $level)
                <option value="{{ $level->id }}" @selected(old('level_id', $record?->level_id) == $level->id)>{{ $level->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Statut</label>
        <select name="status_id" class="form-select">
            @foreach($statuses as $status)
                <option value="{{ $status->id }}" @selected(old('status_id', $record?->status_id) == $status->id)>{{ $status->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Activité</label>
        <select name="activity_id" class="form-select">
            <option value="">—</option>
            @foreach($activities as $activity)
                <option value="{{ $activity->id }}" @selected(old('activity_id', $record?->activity_id) == $activity->id)>{{ $activity->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Organisation <span class="text-danger">*</span></label>
        <select name="organisation_id" class="form-select" required>
            @foreach($organisations as $organisation)
                <option value="{{ $organisation->id }}" @selected(old('organisation_id', $record?->organisation_id) == $organisation->id)>{{ $organisation->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Notice parente (dossier)</label>
        <select name="parent_id" class="form-select">
            <option value="">— Racine —</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $record?->parent_id) == $parent->id)>{{ $parent->code }} — {{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Assigné à</label>
        <select name="assigned_to" class="form-select">
            <option value="">—</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('assigned_to', $record?->assigned_to) == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Confidentialité</label>
        <select name="confidentiality_id" class="form-select">
            <option value="">—</option>
            @foreach($confidentialities as $conf)
                <option value="{{ $conf->id }}" @selected(old('confidentiality_id', $record?->confidentiality_id) == $conf->id)>{{ $conf->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Date exacte</label>
        <input type="date" name="date_exact" value="{{ old('date_exact', $record?->date_exact?->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Date début</label>
        <input type="date" name="start_date" value="{{ old('start_date', $record?->start_date?->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Date fin</label>
        <input type="date" name="end_date" value="{{ old('end_date', $record?->end_date?->format('Y-m-d')) }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Ouverture</label>
        <input type="date" name="opening_date" value="{{ old('opening_date', $record?->opening_date?->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Fermeture</label>
        <input type="date" name="closing_date" value="{{ old('closing_date', $record?->closing_date?->format('Y-m-d')) }}" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $record?->description) }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Étendue (fonds)</label>
        <input type="text" name="extent" value="{{ old('extent', $record?->extent) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Table des matières</label>
        <input type="text" name="table_of_contents" value="{{ old('table_of_contents', $record?->table_of_contents) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Quantité</label>
        <input type="text" name="quantity" value="{{ old('quantity', $record?->quantity) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Dimension</label>
        <input type="text" name="dimension" value="{{ old('dimension', $record?->dimension) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Éditeur</label>
        <input type="text" name="publisher" value="{{ old('publisher', $record?->publisher) }}" class="form-control">
    </div>
</div>

{{-- Champs dynamiques du profil du type --}}
@include('records.partials.metadata-fields', ['record' => $record ?? null, 'metadataFields' => $metadataFields])

{{-- Support à la création --}}
@if(!$record)
    <hr>
    <h6>Support initial (optionnel — ajoutable ensuite depuis la fiche)</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Type de support</label>
            <select name="support_id" class="form-select">
                <option value="">— Aucun —</option>
                @foreach($supports as $support)
                    <option value="{{ $support->id }}" @selected(old('support_id') == $support->id)>{{ $support->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Boîte / contenant</label>
            <select name="container_id" class="form-select">
                <option value="">—</option>
                @foreach($containers as $container)
                    <option value="{{ $container->id }}">{{ $container->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Fichier (si support numérique)</label>
            <input type="file" name="attachment" class="form-control">
        </div>
    </div>
@endif

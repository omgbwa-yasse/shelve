@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Modifier le domaine — {{ $referenceList->name }}</h1>
        <a href="{{ route('settings.reference-lists.show', $referenceList) }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('settings.reference-lists.update', $referenceList) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $referenceList->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $referenceList->code) }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $referenceList->description) }}</textarea>
                    </div>
                    @if($referenceList->isLinkedSchemaEligible())
                        <div class="col-md-6">
                            <label class="form-label">Schéma lié</label>
                            <select name="linked_schema_id" class="form-select">
                                <option value="">— Aucun schéma lié —</option>
                                @foreach($linkedSchemas as $schema)
                                    <option value="{{ $schema->id }}" @selected(old('linked_schema_id', $referenceList->linked_schema_id) == $schema->id)>
                                        {{ $schema->name }} ({{ $schema->code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Associe le schéma (RecordType) à ce domaine de valeurs.</small>
                        </div>
                    @endif
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="active" value="1" id="active"
                                   @checked($referenceList->active)>
                            <label class="form-check-label" for="active">Actif</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

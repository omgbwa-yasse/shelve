@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Modifier la typologie — {{ $recordType->name }}</h1>
        <a href="{{ route('settings.record-types.index') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('settings.record-types.update', $recordType) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $recordType->code) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $recordType->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Domaine de valeurs</label>
                        <select name="reference_list_id" class="form-select">
                            <option value="">—</option>
                            @foreach($referenceLists as $list)
                                <option value="{{ $list->id }}" @selected(old('reference_list_id', $recordType->reference_list_id) == $list->id)>{{ $list->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Typologie parente</label>
                        <select name="parent_id" class="form-select">
                            <option value="">—</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected(old('parent_id', $recordType->parent_id) == $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Icône</label>
                        <input type="text" name="icon" value="{{ old('icon', $recordType->icon) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Couleur</label>
                        <input type="text" name="color" value="{{ old('color', $recordType->color) }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Préfixe de code</label>
                        <input type="text" name="code_prefix" value="{{ old('code_prefix', $recordType->code_prefix) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pattern de code</label>
                        <input type="text" name="code_pattern" value="{{ old('code_pattern', $recordType->code_pattern) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Niveau d'accès par défaut</label>
                        <select name="default_access_level" class="form-select">
                            @foreach(['internal', 'public', 'confidential', 'secret'] as $level)
                                <option value="{{ $level }}" @selected(old('default_access_level', $recordType->default_access_level) == $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $recordType->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_container" value="1" id="is_container"
                                           @checked(old('is_container', $recordType->is_container))>
                                    <label class="form-check-label" for="is_container">Conteneur (dossier)</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="requires_versioning" value="1" id="rv"
                                           @checked(old('requires_versioning', $recordType->requires_versioning))>
                                    <label class="form-check-label" for="rv">Versionnage</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="requires_approval" value="1" id="ra"
                                           @checked(old('requires_approval', $recordType->requires_approval))>
                                    <label class="form-check-label" for="ra">Approbation requise</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="requires_signature" value="1" id="rs"
                                           @checked(old('requires_signature', $recordType->requires_signature))>
                                    <label class="form-check-label" for="rs">Signature requise</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active"
                                           @checked(old('is_active', $recordType->is_active))>
                                    <label class="form-check-label" for="active">Actif</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ordre d'affichage</label>
                                <input type="number" name="display_order" value="{{ old('display_order', $recordType->display_order) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Taille max fichier (octets)</label>
                                <input type="number" name="max_file_size" value="{{ old('max_file_size', $recordType->max_file_size) }}" class="form-control">
                            </div>
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

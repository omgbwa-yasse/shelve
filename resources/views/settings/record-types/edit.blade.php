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

    <div class="card mt-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Métadonnées de cette typologie</h2>
        </div>
        <div class="card-body">
            @if($attachedProfiles->isEmpty())
                <p class="text-muted mb-3">Aucune métadonnée attachée pour l'instant.</p>
            @else
                <table class="table table-sm align-middle mb-4">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Code</th>
                            <th>Origine</th>
                            <th class="text-center">Obligatoire</th>
                            <th class="text-center">Visible</th>
                            <th>Ordre</th>
                            <th>Propriétés</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attachedProfiles as $profile)
                            <tr>
                                <td>{{ $profile->metadataDefinition->name }}</td>
                                <td><code>{{ $profile->metadataDefinition->code }}</code></td>
                                <td>
                                    @if($profile->metadataDefinition->is_system)
                                        <span class="badge bg-secondary">Système</span>
                                    @else
                                        <span class="badge bg-info text-dark">Personnalisée</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('settings.record-types.metadata.update', [$recordType, $profile]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="visible" value="{{ $profile->visible ? 1 : 0 }}">
                                        <input type="hidden" name="sort_order" value="{{ $profile->sort_order }}">
                                        <input type="checkbox" class="form-check-input" name="mandatory" value="1"
                                               onchange="this.form.submit()" @checked($profile->mandatory)>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('settings.record-types.metadata.update', [$recordType, $profile]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="mandatory" value="{{ $profile->mandatory ? 1 : 0 }}">
                                        <input type="hidden" name="sort_order" value="{{ $profile->sort_order }}">
                                        <input type="checkbox" class="form-check-input" name="visible" value="1"
                                               onchange="this.form.submit()" @checked($profile->visible)>
                                    </form>
                                </td>
                                <td>{{ $profile->sort_order }}</td>
                                <td>
                                    @php($d = $profile->metadataDefinition)
                                    <span class="badge bg-light text-dark border"
                                          data-bs-toggle="collapse" data-bs-target="#config{{ $profile->id }}"
                                          style="cursor:pointer" title="Configurer">
                                        <i class="bi bi-sliders"></i>
                                        @if($d->sortable || $d->highlightable || $d->autocomplete || $d->unique || $d->input_mask || $d->max_length || $d->copy_source_type || $d->computed_template || !empty($profile->restricted_to_roles))
                                            <span class="text-success">configuré</span>
                                        @else
                                            configurer
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('settings.record-types.metadata.destroy', [$recordType, $profile]) }}"
                                          onsubmit="return confirm('Détacher cette métadonnée de la typologie ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="collapse" id="config{{ $profile->id }}">
                                <td colspan="8" class="bg-light">
                                    @php($d = $profile->metadataDefinition)
                                    <form method="POST" action="{{ route('settings.record-types.metadata.update', [$recordType, $profile]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="mandatory" value="{{ $profile->mandatory ? 1 : 0 }}">
                                        <input type="hidden" name="visible" value="{{ $profile->visible ? 1 : 0 }}">
                                        <input type="hidden" name="sort_order" value="{{ $profile->sort_order }}">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="sortable" value="1" id="sortable{{ $profile->id }}" @checked($d->sortable)>
                                                    <label class="form-check-label" for="sortable{{ $profile->id }}">Triable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="highlightable" value="1" id="hl{{ $profile->id }}" @checked($d->highlightable)>
                                                    <label class="form-check-label" for="hl{{ $profile->id }}">Surlignable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="autocomplete" value="1" id="ac{{ $profile->id }}" @checked($d->autocomplete)>
                                                    <label class="form-check-label" for="ac{{ $profile->id }}">Autocomplétion</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="unique" value="1" id="uq{{ $profile->id }}" @checked($d->unique)>
                                                    <label class="form-check-label" for="uq{{ $profile->id }}">Unicité</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Masque de saisie</label>
                                                <input type="text" name="input_mask" value="{{ $d->input_mask }}" class="form-control" placeholder="ex. 99/99/9999">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Longueur max (text)</label>
                                                <input type="number" name="max_length" value="{{ $d->max_length }}" min="1" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Groupe / onglet</label>
                                                <input type="text" name="group" value="{{ $profile->group }}" class="form-control" placeholder="ex. Identification">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Copie depuis le parent</label>
                                                <select name="copy_source_type" class="form-select">
                                                    <option value="">— Désactivé —</option>
                                                    <option value="parent" @selected($d->copy_source_type === 'parent')>Parent</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Champ source (code)</label>
                                                <input type="text" name="copy_source_field" value="{{ $d->copy_source_field }}" class="form-control" placeholder="ex. code_du_parent">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Champ calculé (gabarit)</label>
                                                <input type="text" name="computed_template" value="{{ $d->computed_template }}" class="form-control" placeholder="ex. $Titre $Code">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Restreint aux rôles (vide = tous)</label>
                                                <select name="restricted_to_roles[]" class="form-select" multiple size="4">
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->name }}" @selected(in_array($role->name, $profile->restricted_to_roles ?? []))>{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Valeur par défaut</label>
                                                <input type="text" name="default_value" value="{{ $profile->default_value }}" class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Enregistrer la configuration</button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if($availableDefinitions->isEmpty())
                <p class="text-muted mb-0">Toutes les définitions de métadonnées actives sont déjà attachées.</p>
            @else
                <form method="POST" action="{{ route('settings.record-types.metadata.store', $recordType) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label">Attacher une métadonnée</label>
                        <select name="metadata_definition_id" class="form-select" required>
                            <option value="">—</option>
                            @foreach($availableDefinitions as $definition)
                                <option value="{{ $definition->id }}">
                                    {{ $definition->name }} ({{ $definition->code }})@if($definition->is_system) — système @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="mandatory" value="1" id="new-mandatory">
                            <label class="form-check-label" for="new-mandatory">Obligatoire</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="visible" value="1" id="new-visible" checked>
                            <label class="form-check-label" for="new-visible">Visible</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </form>
            @endif

            <p class="text-muted small mt-3 mb-0">
                Pour créer une nouvelle définition de métadonnée personnalisée, utilisez
                <a href="{{ route('settings.metadata-definitions.create') }}">Métadonnées &gt; Nouvelle définition</a>
                puis attachez-la ici.
            </p>
        </div>
    </div>
</div>
@endsection

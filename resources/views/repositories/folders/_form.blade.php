{{--
  Partial partagé: formulaire de création/modification de dossier
  Variables attendues:
    $folder       (null pour create, objet pour edit)
    $types, $parentFolders, $organisations, $users
    $parentId     (optionnel, pour pré-sélectionner le parent)
    $isEdit       (bool)
--}}
@php $isEdit = isset($isEdit) && $isEdit; @endphp

<div class="row g-3">

    {{-- Nom --}}
    <div class="col-md-8">
        <label for="name" class="form-label fw-semibold small">Nom <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name"
               value="{{ old('name', $folder->name ?? '') }}"
               placeholder="Nom du dossier" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Type --}}
    <div class="col-md-4">
        <label for="type_id" class="form-label fw-semibold small">Type <span class="text-danger">*</span></label>
        <select class="form-select @error('type_id') is-invalid @enderror"
                id="type_id" name="type_id" required onchange="loadMetadata(this.value)">
            <option value="">-- Sélectionner --</option>
            @foreach($types as $type)
            <option value="{{ $type->id }}" {{ old('type_id', $folder->type_id ?? '') == $type->id ? 'selected' : '' }}>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        @error('type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Description --}}
    <div class="col-12">
        <label for="description" class="form-label fw-semibold small">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror"
                  id="description" name="description" rows="2"
                  placeholder="Description du dossier (optionnel)">{{ old('description', $folder->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Dossier parent --}}
    <div class="col-md-6">
        <label for="parent_id" class="form-label fw-semibold small">Dossier parent</label>
        <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
            <option value="">— Racine (sans parent)</option>
            @foreach($parentFolders as $pf)
            @if(!$isEdit || $pf->id !== ($folder->id ?? null))
            <option value="{{ $pf->id }}"
                {{ old('parent_id', $folder->parent_id ?? $parentId ?? '') == $pf->id ? 'selected' : '' }}>
                {{ $pf->name }}@if($pf->type) · {{ $pf->type->name }}@endif
            </option>
            @endif
            @endforeach
        </select>
        @if($isEdit)
        <div class="form-text text-muted" style="font-size:.72rem;">Attention: vérifier la hiérarchie avant de changer le parent.</div>
        @endif
        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Organisation --}}
    <div class="col-md-6">
        <label for="organisation_id" class="form-label fw-semibold small">Organisation <span class="text-danger">*</span></label>
        <select class="form-select @error('organisation_id') is-invalid @enderror"
                id="organisation_id" name="organisation_id" required>
            <option value="">-- Sélectionner --</option>
            @foreach($organisations as $organisation)
            <option value="{{ $organisation->id }}"
                {{ old('organisation_id', $folder->organisation_id ?? auth()->user()->organisation_id ?? '') == $organisation->id ? 'selected' : '' }}>
                {{ $organisation->name }}
            </option>
            @endforeach
        </select>
        @error('organisation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Accès --}}
    <div class="col-md-4">
        <label for="access_level" class="form-label fw-semibold small">Niveau d'accès <span class="text-danger">*</span></label>
        <select class="form-select @error('access_level') is-invalid @enderror"
                id="access_level" name="access_level" required>
            <option value="public"       {{ old('access_level', $folder->access_level ?? 'public') == 'public'       ? 'selected' : '' }}>Public</option>
            <option value="internal"     {{ old('access_level', $folder->access_level ?? '') == 'internal'           ? 'selected' : '' }}>Interne</option>
            <option value="confidential" {{ old('access_level', $folder->access_level ?? '') == 'confidential'       ? 'selected' : '' }}>Confidentiel</option>
            <option value="secret"       {{ old('access_level', $folder->access_level ?? '') == 'secret'             ? 'selected' : '' }}>Secret</option>
        </select>
        @error('access_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Statut --}}
    <div class="col-md-4">
        <label for="status" class="form-label fw-semibold small">Statut <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror"
                id="status" name="status" required>
            <option value="active"   {{ old('status', $folder->status ?? 'active') == 'active'   ? 'selected' : '' }}>Actif</option>
            <option value="closed"   {{ old('status', $folder->status ?? '') == 'closed'          ? 'selected' : '' }}>Fermé</option>
            <option value="archived" {{ old('status', $folder->status ?? '') == 'archived'        ? 'selected' : '' }}>Archivé</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Assigné à --}}
    <div class="col-md-4">
        <label for="assigned_to" class="form-label fw-semibold small">Responsable</label>
        <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
            <option value="">— Non assigné</option>
            @foreach($users as $user)
            <option value="{{ $user->id }}" {{ old('assigned_to', $folder->assigned_to ?? '') == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
            @endforeach
        </select>
        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Dates --}}
    <div class="col-md-6">
        <label for="start_date" class="form-label fw-semibold small">Date de début</label>
        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
               id="start_date" name="start_date"
               value="{{ old('start_date', ($folder->start_date ?? null)?->format('Y-m-d') ?? '') }}">
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="end_date" class="form-label fw-semibold small">Date de fin</label>
        <input type="date" class="form-control @error('end_date') is-invalid @enderror"
               id="end_date" name="end_date"
               value="{{ old('end_date', ($folder->end_date ?? null)?->format('Y-m-d') ?? '') }}">
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Approbation --}}
    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="requires_approval"
                   name="requires_approval" value="1"
                   {{ old('requires_approval', $folder->requires_approval ?? false) ? 'checked' : '' }}>
            <label class="form-check-label small" for="requires_approval">Nécessite une approbation</label>
        </div>
    </div>

    {{-- Métadonnées dynamiques --}}
    <div id="metadata-container" class="col-12" style="display:none;">
        <div class="border-top pt-3 mt-1">
            <div class="fw-semibold small mb-2" style="color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">Métadonnées personnalisées</div>
            <div id="metadata-fields" class="row g-3"></div>
        </div>
    </div>

</div>

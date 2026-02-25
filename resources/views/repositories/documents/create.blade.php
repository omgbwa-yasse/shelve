@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Créer un nouveau document numérique</h1>
        <a href="{{ route('documents.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="type_id" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type_id') is-invalid @enderror"
                                    id="type_id" name="type_id" required onchange="loadDocumentMetadata(this.value)">
                                <option value="">-- Sélectionner un type --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="folder_id" class="form-label">Dossier <span class="text-danger">*</span></label>
                            <select class="form-select @error('folder_id') is-invalid @enderror"
                                    id="folder_id" name="folder_id" required>
                                <option value="">-- Sélectionner un dossier --</option>
                                @foreach($folders as $folder)
                                    <option value="{{ $folder->id }}"
                                        {{ old('folder_id', $folderId) == $folder->id ? 'selected' : '' }}>
                                        {{ $folder->name }} ({{ $folder->type->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('folder_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="organisation_id" class="form-label">Organisation <span class="text-danger">*</span></label>
                            <select class="form-select @error('organisation_id') is-invalid @enderror"
                                    id="organisation_id" name="organisation_id" required>
                                <option value="">-- Sélectionner une organisation --</option>
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}"
                                        {{ old('organisation_id', auth()->user()->organisation_id) == $organisation->id ? 'selected' : '' }}>
                                        {{ $organisation->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('organisation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="file" class="form-label">Fichier <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror"
                           id="file" name="file" required>
                    <small class="form-text text-muted">
                        Formats acceptés: PDF, Word, Excel, Images. Taille max: 10 MB
                    </small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="access_level" class="form-label">Niveau d'accès <span class="text-danger">*</span></label>
                            <select class="form-select @error('access_level') is-invalid @enderror"
                                    id="access_level" name="access_level" required>
                                <option value="public" {{ old('access_level', 'public') == 'public' ? 'selected' : '' }}>Public</option>
                                <option value="internal" {{ old('access_level') == 'internal' ? 'selected' : '' }}>Interne</option>
                                <option value="confidential" {{ old('access_level') == 'confidential' ? 'selected' : '' }}>Confidentiel</option>
                                <option value="secret" {{ old('access_level') == 'secret' ? 'selected' : '' }}>Secret</option>
                            </select>
                            @error('access_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
                                <option value="obsolete" {{ old('status') == 'obsolete' ? 'selected' : '' }}>Obsolète</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="signature_status" class="form-label">Signature <span class="text-danger">*</span></label>
                            <select class="form-select @error('signature_status') is-invalid @enderror"
                                    id="signature_status" name="signature_status" required>
                                <option value="unsigned" {{ old('signature_status', 'unsigned') == 'unsigned' ? 'selected' : '' }}>Non signé</option>
                                <option value="pending" {{ old('signature_status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="signed" {{ old('signature_status') == 'signed' ? 'selected' : '' }}>Signé</option>
                                <option value="rejected" {{ old('signature_status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                            </select>
                            @error('signature_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assigné à</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror"
                                    id="assigned_to" name="assigned_to">
                                <option value="">-- Non assigné --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="document_date" class="form-label">Date du document</label>
                            <input type="date" class="form-control @error('document_date') is-invalid @enderror"
                                   id="document_date" name="document_date" value="{{ old('document_date', date('Y-m-d')) }}">
                            @error('document_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="requires_approval"
                                   name="requires_approval" value="1" {{ old('requires_approval') ? 'checked' : '' }}>
                            <label class="form-check-label" for="requires_approval">
                                Nécessite une approbation
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="retention_until" class="form-label">Conservation jusqu'au</label>
                            <input type="date" class="form-control @error('retention_until') is-invalid @enderror"
                                   id="retention_until" name="retention_until" value="{{ old('retention_until') }}">
                            @error('retention_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Conteneur pour les métadonnées dynamiques --}}
                <div id="metadata-container" class="mb-4" style="display:none;">
                    <h5 class="border-top pt-3 mt-3">Métadonnées personnalisées</h5>
                    <div id="metadata-fields" class="row"></div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('documents.index') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Créer le document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadDocumentMetadata(typeId) {
    const container = document.getElementById('metadata-container');
    const fieldsDiv = document.getElementById('metadata-fields');

    if (!typeId) {
        container.style.display = 'none';
        fieldsDiv.innerHTML = '';
        return;
    }

    fetch(`/api/v1/metadata/document-types/${typeId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            container.style.display = 'block';
            renderMetadataFields(data.data);
        } else {
            container.style.display = 'none';
            fieldsDiv.innerHTML = '';
        }
    })
    .catch(error => {
        console.error('Erreur chargement métadonnées:', error);
        container.style.display = 'none';
        fieldsDiv.innerHTML = '';
    });
}

function renderMetadataFields(metadata) {
    const fieldsDiv = document.getElementById('metadata-fields');
    fieldsDiv.innerHTML = '';

    metadata.forEach(field => {
        if (!field.visible) return;

        const col = document.createElement('div');
        col.className = 'col-md-6 mb-3';

        const label = document.createElement('label');
        label.className = 'form-label';
        label.htmlFor = `metadata_${field.name}`;
        label.innerHTML = field.name + (field.mandatory ? ' <span class="text-danger">*</span>' : '');

        let input;
        const fieldName = `metadata[${field.name}]`;
        const required = field.mandatory ? 'required' : '';
        const readonly = field.readonly ? 'readonly' : '';

        switch (field.data_type) {
            case 'text':
                input = `<input type="text" class="form-control" id="metadata_${field.name}" name="${fieldName}" value="${field.default_value || ''}" ${required} ${readonly}>`;
                break;
            case 'textarea':
                input = `<textarea class="form-control" id="metadata_${field.name}" name="${fieldName}" rows="3" ${required} ${readonly}>${field.default_value || ''}</textarea>`;
                break;
            case 'number':
                input = `<input type="number" class="form-control" id="metadata_${field.name}" name="${fieldName}" value="${field.default_value || ''}" ${required} ${readonly}>`;
                break;
            case 'date':
                input = `<input type="date" class="form-control" id="metadata_${field.name}" name="${fieldName}" value="${field.default_value || ''}" ${required} ${readonly}>`;
                break;
            case 'datetime':
                input = `<input type="datetime-local" class="form-control" id="metadata_${field.name}" name="${fieldName}" value="${field.default_value || ''}" ${required} ${readonly}>`;
                break;
            case 'boolean':
                const checked = field.default_value ? 'checked' : '';
                input = `<div class="form-check"><input type="checkbox" class="form-check-input" id="metadata_${field.name}" name="${fieldName}" value="1" ${checked} ${readonly}><input type="hidden" name="${fieldName}" value="0"></div>`;
                break;
            case 'email':
                input = `<input type="email" class="form-control" id="metadata_${field.name}" name="${fieldName}" value="${field.default_value || ''}" ${required} ${readonly}>`;
                break;
            case 'url':
                input = `<input type="url" class="form-control" id="metadata_${field.name}" name="${fieldName}" value="${field.default_value || ''}" ${required} ${readonly}>`;
                break;
            case 'select':
            case 'reference_list':
                if (field.reference_list && field.reference_list.values) {
                    let options = '<option value="">-- Sélectionner --</option>';
                    field.reference_list.values.forEach(val => {
                        const selected = val.value === field.default_value ? 'selected' : '';
                        options += `<option value="${val.value}" ${selected}>${val.display_value}</option>`;
                    });
                    input = `<select class="form-select" id="metadata_${field.name}" name="${fieldName}" ${required} ${readonly}>${options}</select>`;
                } else {
                    input = `<input type="text" class="form-control" id="metadata_${field.name}" name="${fieldName}" value="${field.default_value || ''}" ${required} ${readonly}>`;
                }
                break;
            default:
                input = `<input type="text" class="form-control" id="metadata_${field.name}" name="${fieldName}" value="${field.default_value || ''}" ${required} ${readonly}>`;
        }

        col.innerHTML = label.outerHTML + input;
        if (field.description) {
            col.innerHTML += `<small class="form-text text-muted">${field.description}</small>`;
        }
        fieldsDiv.appendChild(col);
    });
}

// Charger au démarrage si type déjà sélectionné
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type_id');
    if (typeSelect && typeSelect.value) {
        loadDocumentMetadata(typeSelect.value);
    }
});
</script>
@endpush

@endsection

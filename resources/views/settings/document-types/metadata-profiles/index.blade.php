@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Profils de métadonnées - {{ $documentType->name }}</h1>
            <p class="text-muted">Gérer les champs de métadonnées pour ce type de document</p>
        </div>
        <a href="{{ route('settings.document-types.show', $documentType) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Erreur!</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Métadonnées associées ({{ $documentType->metadataProfiles->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($documentType->metadataProfiles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Champ</th>
                                        <th>Type</th>
                                        <th class="text-center">Obligatoire</th>
                                        <th class="text-center">Visible</th>
                                        <th class="text-center">Lecture seule</th>
                                        <th class="text-center">Ordre</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-profiles">
                                    @foreach($documentType->metadataProfiles as $profile)
                                        <tr class="sortable-row" data-profile-id="{{ $profile->id }}">
                                            <td>
                                                <strong>{{ $profile->metadataDefinition->name }}</strong>
                                                @if($profile->metadataDefinition->code)
                                                    <br><small class="text-muted">{{ $profile->metadataDefinition->code }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($profile->metadataDefinition->data_type) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($profile->mandatory)
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                @else
                                                    <i class="bi bi-circle text-secondary"></i>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($profile->visible)
                                                    <i class="bi bi-eye-fill text-success"></i>
                                                @else
                                                    <i class="bi bi-eye-slash text-secondary"></i>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($profile->readonly)
                                                    <i class="bi bi-lock-fill text-warning"></i>
                                                @else
                                                    <i class="bi bi-unlock-fill text-secondary"></i>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <input type="number" class="form-control form-control-sm"
                                                       value="{{ $profile->sort_order ?? 0 }}"
                                                       min="0" style="width: 60px;">
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editProfileModal"
                                                        data-profile="{{ json_encode($profile) }}"
                                                        data-definition="{{ json_encode($profile->metadataDefinition) }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('settings.document-types.metadata-profiles.destroy', [$documentType, $profile]) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Êtes-vous sûr?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <form method="POST" action="{{ route('settings.document-types.metadata-profiles.bulk-update', $documentType) }}" id="sortForm">
                                @csrf
                                <input type="hidden" id="profilesData" name="profiles">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="saveSortOrder()">
                                    <i class="bi bi-check"></i> Enregistrer l'ordre
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">Aucune métadonnée associée. Ajoutez-en une ci-dessous.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ajouter une métadonnée</h5>
                </div>
                <div class="card-body">
                    @if($availableDefinitions->count() > 0)
                        <form action="{{ route('settings.document-types.metadata-profiles.store', $documentType) }}"
                              method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="metadata_definition_id" class="form-label">Champ <span class="text-danger">*</span></label>
                                <select class="form-select @error('metadata_definition_id') is-invalid @enderror"
                                        id="metadata_definition_id" name="metadata_definition_id" required
                                        onchange="updateDefinitionInfo()">
                                    <option value="">-- Sélectionner un champ --</option>
                                    @foreach($availableDefinitions as $definition)
                                        <option value="{{ $definition->id }}"
                                                data-type="{{ $definition->data_type }}"
                                                data-code="{{ $definition->code }}">
                                            {{ $definition->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('metadata_definition_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div id="definitionInfo" class="text-muted small"></div>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="mandatory"
                                       name="mandatory" value="1">
                                <label class="form-check-label" for="mandatory">
                                    Obligatoire
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="visible"
                                       name="visible" value="1" checked>
                                <label class="form-check-label" for="visible">
                                    Visible
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="readonly"
                                       name="readonly" value="1">
                                <label class="form-check-label" for="readonly">
                                    Lecture seule
                                </label>
                            </div>

                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Ordre d'affichage</label>
                                <input type="number" class="form-control" id="sort_order"
                                       name="sort_order" value="0" min="0">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus"></i> Ajouter
                            </button>
                        </form>
                    @else
                        <p class="text-muted text-center py-3">Toutes les métadonnées disponibles sont déjà associées.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="definitionName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="editMandatory"
                               name="mandatory" value="1">
                        <label class="form-check-label" for="editMandatory">
                            Obligatoire
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="editVisible"
                               name="visible" value="1">
                        <label class="form-check-label" for="editVisible">
                            Visible
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="editReadonly"
                               name="readonly" value="1">
                        <label class="form-check-label" for="editReadonly">
                            Lecture seule
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="editDefaultValue" class="form-label">Valeur par défaut</label>
                        <textarea class="form-control" id="editDefaultValue"
                                  name="default_value" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="editSortOrder" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="editSortOrder"
                               name="sort_order" value="0" min="0">
                    </div>

                    <div class="mb-3">
                        <label for="editValidationRules" class="form-label">Règles de validation (JSON)</label>
                        <textarea class="form-control" id="editValidationRules"
                                  name="validation_rules" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateDefinitionInfo() {
    const select = document.getElementById('metadata_definition_id');
    const selected = select.options[select.selectedIndex];
    const infoDiv = document.getElementById('definitionInfo');

    if (selected.value) {
        const type = selected.dataset.type;
        const code = selected.dataset.code;
        infoDiv.innerHTML = `<strong>Type:</strong> ${type}<br><strong>Code:</strong> ${code}`;
    } else {
        infoDiv.innerHTML = '';
    }
}

function saveSortOrder() {
    const profiles = [];
    document.querySelectorAll('.sortable-row').forEach((row, index) => {
        const input = row.querySelector('input[type="number"]');
        profiles.push({
            id: row.dataset.profileId,
            sort_order: parseInt(input.value) || index
        });
    });

    document.getElementById('profilesData').value = JSON.stringify(profiles);
    document.getElementById('sortForm').submit();
}

// Handle edit modal
const editModal = document.getElementById('editProfileModal');
if (editModal) {
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const profileStr = button.dataset.profile;
        const definitionStr = button.dataset.definition;

        if (profileStr && definitionStr) {
            const profile = JSON.parse(profileStr);
            const definition = JSON.parse(definitionStr);

            // Update modal title
            document.getElementById('definitionName').textContent = definition.name;

            // Update form action
            const editForm = document.getElementById('editForm');
            editForm.action = `{{ route('settings.document-types.metadata-profiles.index', $documentType) }}/${profile.id}`;

            // Update form values
            document.getElementById('editMandatory').checked = profile.mandatory || false;
            document.getElementById('editVisible').checked = profile.visible || false;
            document.getElementById('editReadonly').checked = profile.readonly || false;
            document.getElementById('editDefaultValue').value = profile.default_value || '';
            document.getElementById('editSortOrder').value = profile.sort_order || 0;
            document.getElementById('editValidationRules').value = profile.validation_rules || '';
        }
    });
}
</script>
@endpush
@endsection

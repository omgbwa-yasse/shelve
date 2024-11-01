@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h1 class="h4 mb-0">Archives à communiquer</h1>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('transactions.records.store', $communication) }}" method="POST">
                            @csrf

                            <!-- Archives voulues -->
                            <div class="mb-4">
                                <label class="form-label">
                                    Archives voulues
                                    <span class="text-danger">*</span>
                                </label>

                                <!-- Affichage de l'archive sélectionnée -->
                                <div id="selectedArchiveDisplay" class="mb-2">
                                    <div class="alert alert-light border">
                                        <span id="noSelectionText">Aucune archive sélectionnée</span>
                                        <div id="selectedArchiveInfo" style="display: none;">
                                            <strong id="selectedArchiveName"></strong>
                                            <small class="text-muted" id="selectedArchiveId"></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bouton pour ouvrir le modal -->
                                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#archiveModal">
                                    <i class="bi bi-search me-2"></i>Sélectionner une archive
                                </button>

                                <input type="hidden" name="record_id" id="record_id" required>
                                @error('record_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Documents sollicités -->
                            <div class="mb-4">
                                <label for="content" class="form-label d-flex justify-content-between">
                                    <span>Documents sollicités</span>
                                    <small class="text-muted">(facultatif)</small>
                                </label>
                                <textarea
                                    class="form-control"
                                    id="content"
                                    name="content"
                                    rows="3"
                                    placeholder="Précisez les documents souhaités..."
                                >{{ old('content') }}</textarea>
                                @error('content')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Copie originale -->
                            <div class="mb-4">
                                <label for="is_original" class="form-label">
                                    Type de copie
                                    <span class="text-danger">*</span>
                                </label>
                                <select
                                    name="is_original"
                                    id="is_original"
                                    class="form-select"
                                    required
                                >
                                    <option value="">Sélectionnez le type</option>
                                    <option value="1" {{ old('is_original') == '1' ? 'selected' : '' }}>
                                        Copie originale
                                    </option>
                                    <option value="0" {{ old('is_original') == '0' ? 'selected' : '' }}>
                                        Copie simple
                                    </option>
                                </select>
                                @error('is_original')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutons -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                    Retour
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Créer la demande
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de sélection d'archive -->
    <div class="modal fade" id="archiveModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sélection d'une archive</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Barre de recherche -->
                    <div class="mb-3">
                        <input
                            type="text"
                            class="form-control"
                            id="archiveSearch"
                            placeholder="Rechercher une archive..."
                            oninput="filterArchives(this.value)"
                        >
                    </div>

                    <!-- Liste des archives -->
                    <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                        @foreach($records as $record)
                            <button
                                type="button"
                                class="list-group-item list-group-item-action archive-item"
                                onclick="selectArchive('{{ $record->id }}', '{{ $record->name }}')"
                                data-id="{{ $record->id }}"
                                data-name="{{ $record->name }}"
                            >
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ $record->name }}</strong>
                                    <small class="text-muted">#{{ $record->id }}</small>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function filterArchives(query) {
            query = query.toLowerCase();
            document.querySelectorAll('.archive-item').forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const id = item.getAttribute('data-id');
                item.style.display = (name.includes(query) || id.includes(query)) ? '' : 'none';
            });
        }

        function selectArchive(id, name) {
            // Mettre à jour l'input caché
            document.getElementById('record_id').value = id;

            // Mettre à jour l'affichage
            document.getElementById('noSelectionText').style.display = 'none';
            document.getElementById('selectedArchiveInfo').style.display = 'block';
            document.getElementById('selectedArchiveName').textContent = name;
            document.getElementById('selectedArchiveId').textContent = ` #${id}`;

            // Fermer le modal
            bootstrap.Modal.getInstance(document.getElementById('archiveModal')).hide();
        }

        // Initialiser l'affichage si une valeur est déjà sélectionnée
        document.addEventListener('DOMContentLoaded', function() {
            const selectedId = document.getElementById('record_id').value;
            if (selectedId) {
                const item = document.querySelector(`.archive-item[data-id="${selectedId}"]`);
                if (item) {
                    selectArchive(selectedId, item.getAttribute('data-name'));
                }
            }
        });
    </script>
@endsection

@section('scripts')

@endsection

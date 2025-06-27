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
                        <form action="{{ route('transactions.records.store', $communication->id) }}" method="POST" id="communicationForm">
                            @csrf

                            <!-- Archives voulues -->
                            <div class="mb-4">
                                <label class="form-label">
                                    Archives voulues
                                    <span class="text-danger">*</span>
                                </label>

                                <!-- Champ de recherche avec autocomplétion -->
                                <div class="position-relative">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="archiveSearch"
                                        placeholder="Tapez au moins 3 caractères pour rechercher..."
                                        autocomplete="off"
                                    >
                                    <div id="searchResults" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></div>
                                </div>

                                <!-- Affichage de l'archive sélectionnée -->
                                <div id="selectedArchiveDisplay" class="mt-2" style="display: none;">
                                    <div class="alert alert-success border d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong id="selectedArchiveName"></strong>
                                            <small class="text-muted d-block" id="selectedArchiveId"></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelection()">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>

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

    <script>
        let searchTimeout = null;

        document.getElementById('archiveSearch').addEventListener('input', function(e) {
            const query = e.target.value;
            const resultsDiv = document.getElementById('searchResults');

            // Effacer le timeout précédent
            clearTimeout(searchTimeout);

            if (query.length < 3) {
                resultsDiv.style.display = 'none';
                return;
            }

            // Attendre 300ms après la dernière frappe pour lancer la recherche
            searchTimeout = setTimeout(() => {
                fetch(`{{ route('communications.records.search') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsDiv.innerHTML = '';

                        if (data.length === 0) {
                            resultsDiv.innerHTML = '<div class="list-group-item text-muted">Aucun résultat trouvé</div>';
                        } else {
                            data.forEach(record => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML = `
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>${record.name}</strong>
                                        <small class="text-muted">#${record.id}</small>
                                    </div>
                                    ${record.code ? `<small class="text-muted">Code: ${record.code}</small>` : ''}
                                `;
                                item.onclick = () => selectArchive(record.id, record.name, record.code);
                                resultsDiv.appendChild(item);
                            });
                        }

                        resultsDiv.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Erreur lors de la recherche:', error);
                        resultsDiv.innerHTML = '<div class="list-group-item text-danger">Erreur lors de la recherche</div>';
                        resultsDiv.style.display = 'block';
                    });
            }, 300);
        });

        function selectArchive(id, name, code) {
            // Mettre à jour l'input caché
            document.getElementById('record_id').value = id;

            // Mettre à jour l'affichage
            document.getElementById('selectedArchiveName').textContent = name;
            document.getElementById('selectedArchiveId').textContent = `#${id}${code ? ' - Code: ' + code : ''}`;
            document.getElementById('selectedArchiveDisplay').style.display = 'block';

            // Vider le champ de recherche et cacher les résultats
            document.getElementById('archiveSearch').value = '';
            document.getElementById('searchResults').style.display = 'none';
        }

        function clearSelection() {
            document.getElementById('record_id').value = '';
            document.getElementById('selectedArchiveDisplay').style.display = 'none';
        }

        // Cacher les résultats quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#archiveSearch') && !e.target.closest('#searchResults')) {
                document.getElementById('searchResults').style.display = 'none';
            }
        });

        // Validation du formulaire avant soumission
        document.getElementById('communicationForm').addEventListener('submit', function(e) {
            const recordId = document.getElementById('record_id').value;
            if (!recordId) {
                e.preventDefault();
                alert('Veuillez sélectionner une archive avant de soumettre le formulaire.');
                document.getElementById('archiveSearch').focus();
                return false;
            }
        });

        // Initialiser l'affichage si une valeur est déjà sélectionnée (pour les erreurs de validation)
        document.addEventListener('DOMContentLoaded', function() {
            const selectedId = document.getElementById('record_id').value;
            if (selectedId) {
                // Si on a un ID mais pas d'affichage, on peut faire une requête pour récupérer les infos
                // Pour l'instant, on affiche juste l'ID
                document.getElementById('selectedArchiveName').textContent = `Archive #${selectedId}`;
                document.getElementById('selectedArchiveId').textContent = `#${selectedId}`;
                document.getElementById('selectedArchiveDisplay').style.display = 'block';
            }
        });
    </script>
@endsection

@section('scripts')

@endsection

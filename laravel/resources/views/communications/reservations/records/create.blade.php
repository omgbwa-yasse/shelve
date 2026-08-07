@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Ajouter des documents à la réservation</h1>
        <p class="text-muted">Réservation: <strong>{{ $reservation->code }}</strong> - {{ $reservation->name }}</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Seuls les documents des classes associées à votre organisation actuelle (<strong>{{ Auth::user()->organisation->name ?? 'Non définie' }}</strong>) et leurs sous-classes sont disponibles.
        </div>

        <form action="{{ route('communications.reservations.records.store', $reservation->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="record_search" class="form-label">Rechercher un document *</label>
                <input type="text"
                       id="record_search"
                       class="form-control"
                       placeholder="Tapez au moins 3 caractères pour rechercher (code, titre, résumé)..."
                       autocomplete="off">
                <small class="text-muted">Recherche par code, titre ou résumé (minimum 3 caractères)</small>

                <!-- Zone de chargement -->
                <div id="search_loading" class="text-center mt-2" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <span class="ms-2">Recherche en cours...</span>
                </div>

                <!-- Résultats de recherche -->
                <div id="search_results" class="list-group mt-2" style="display: none; max-height: 400px; overflow-y: auto;"></div>

                <!-- Champ caché pour l'ID du document sélectionné -->
                <input type="hidden" name="record_id" id="record_id" required>

                <!-- Affichage du document sélectionné -->
                <div id="selected_record" class="alert alert-info mt-2" style="display: none;">
                    <strong>Document sélectionné:</strong>
                    <div id="selected_record_details"></div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="clearSelection()">
                        <i class="bi bi-x-circle"></i> Changer de document
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label for="is_original" class="form-label">Type *</label>
                <select name="is_original" id="is_original" class="form-select" required>
                    <option value="1" {{ old('is_original') == '1' ? 'selected' : '' }}>Original</option>
                    <option value="0" {{ old('is_original') == '0' ? 'selected' : '' }}>Copie</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="reservation_date" class="form-label">Date de réservation *</label>
                <input type="date" name="reservation_date" id="reservation_date" class="form-control" value="{{ old('reservation_date', date('Y-m-d')) }}" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submit_btn" disabled>
                    <i class="bi bi-check-lg"></i> Ajouter
                </button>
                <a href="{{ route('communications.reservations.show', $reservation->id) }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i> Annuler
                </a>
            </div>
        </form>
    </div>

    <script>
        let searchTimeout = null;
        const searchInput = document.getElementById('record_search');
        const searchLoading = document.getElementById('search_loading');
        const searchResults = document.getElementById('search_results');
        const recordIdInput = document.getElementById('record_id');
        const selectedRecordDiv = document.getElementById('selected_record');
        const selectedRecordDetails = document.getElementById('selected_record_details');
        const submitBtn = document.getElementById('submit_btn');

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            // Effacer le timeout précédent
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Cacher les résultats si moins de 3 caractères
            if (query.length < 3) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
                return;
            }

            // Afficher le loader après un délai
            searchTimeout = setTimeout(() => {
                searchRecords(query);
            }, 300); // Délai de 300ms pour éviter trop de requêtes
        });

        function searchRecords(query) {
            searchLoading.style.display = 'block';
            searchResults.style.display = 'none';
            searchResults.innerHTML = '';

            fetch(`/api/records/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur de recherche');
                }
                return response.json();
            })
            .then(records => {
                searchLoading.style.display = 'none';
                displayResults(records);
            })
            .catch(error => {
                console.error('Erreur:', error);
                searchLoading.style.display = 'none';
                searchResults.innerHTML = '<div class="list-group-item text-danger">Erreur lors de la recherche</div>';
                searchResults.style.display = 'block';
            });
        }

        function displayResults(records) {
            if (records.length === 0) {
                searchResults.innerHTML = '<div class="list-group-item text-muted">Aucun document trouvé</div>';
                searchResults.style.display = 'block';
                return;
            }

            searchResults.innerHTML = '';
            records.forEach(record => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1"><strong>${escapeHtml(record.code)}</strong> - ${escapeHtml(record.name)}</h6>
                    </div>
                    ${record.activity ? `<p class="mb-1 small"><span class="badge bg-secondary">${escapeHtml(record.activity)}</span></p>` : ''}
                    ${record.content ? `<p class="mb-1 small text-muted">${escapeHtml(record.content)}</p>` : ''}
                `;
                item.onclick = function(e) {
                    e.preventDefault();
                    selectRecord(record);
                };
                searchResults.appendChild(item);
            });

            searchResults.style.display = 'block';
        }

        function selectRecord(record) {
            recordIdInput.value = record.id;
            searchInput.value = '';
            searchResults.style.display = 'none';

            selectedRecordDetails.innerHTML = `
                <div><strong>Code:</strong> ${escapeHtml(record.code)}</div>
                <div><strong>Titre:</strong> ${escapeHtml(record.name)}</div>
                ${record.activity ? `<div><strong>Classe:</strong> ${escapeHtml(record.activity)}</div>` : ''}
                ${record.content ? `<div><strong>Résumé:</strong> ${escapeHtml(record.content)}</div>` : ''}
            `;

            selectedRecordDiv.style.display = 'block';
            submitBtn.disabled = false;
        }

        function clearSelection() {
            recordIdInput.value = '';
            selectedRecordDiv.style.display = 'none';
            searchInput.value = '';
            searchInput.focus();
            submitBtn.disabled = true;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Cacher les résultats quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>
@endsection

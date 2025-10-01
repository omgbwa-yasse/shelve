@extends('layouts.app')

@section('content')
    <div class="container">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1> Ajouter un document </h1>
        <form action="{{ route('slips.records.store', $slip->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                Versement : <h3>{{ $slip->code }} - {{ $slip->name }}</h3>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" class="form-control" id="code" name="code" required maxlength="10">
                </div>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date_start" class="form-label">Date Start</label>
                    <input type="text" class="form-control" id="date_start" name="date_start" maxlength="10">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date_end" class="form-label">Date End</label>
                    <input type="text" class="form-control" id="date_end" name="date_end" maxlength="10">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date_exact" class="form-label">Date Exact</label>
                    <input type="date" class="form-control" id="date_exact" name="date_exact">
                </div>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content"></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="level_id" class="form-label">Niveau de description</label>
                    <select class="form-select" id="level_id" name="level_id" required>
                        @foreach ($levels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="width" class="form-label">Width</label>
                    <input type="number" class="form-control" id="width" name="width" step="0.01">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="width_description" class="form-label">Width Description</label>
                    <input type="text" class="form-control" id="width_description" name="width_description" maxlength="100">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="support_id" class="form-label">Support de conservation</label>
                    <select class="form-select" id="support_id" name="support_id" required>
                        @foreach ($supports as $support)
                            <option value="{{ $support->id }}">{{ $support->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="activity_id" class="form-label">Activité</label>
                    <select class="form-select" id="activity_id" name="activity_id" required>
                        @foreach ($activities as $activity)
                            <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="container_search" class="form-label">Contenant</label>
                    <input type="text"
                           class="form-control"
                           id="container_search"
                           placeholder="Saisissez le code du contenant..."
                           autocomplete="off">
                    <input type="hidden" id="container_id" name="container_id">
                    <div id="container_results" class="list-group mt-1" style="position: absolute; z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                    <small class="text-muted" id="container_selected_info"></small>
                </div>
            </div>

            <!-- Keywords Field -->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="keywords" class="form-label">
                        <i class="fas fa-tags me-1"></i>Mots-clés
                    </label>
                    <input type="text"
                           id="keywords"
                           name="keywords"
                           class="form-control"
                           value="{{ old('keywords') }}"
                           placeholder="Entrez les mots-clés séparés par des points-virgules (;)">
                    <div class="form-text">
                        <i class="fas fa-info-circle"></i>
                        Séparez les mots-clés par des points-virgules. Les nouveaux mots-clés seront créés automatiquement.
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>

    <!-- Include keyword manager script -->
    <script src="{{ asset('js/keyword-manager.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof KeywordManager !== 'undefined') {
                new KeywordManager('#keywords', {
                    searchUrl: '{{ route("keywords.search") }}',
                    minChars: 2,
                    debounceDelay: 300
                });
            }
        });

        // Recherche dynamique de containers
        let searchTimeout;
        const containerSearchInput = document.getElementById('container_search');
        const containerResults = document.getElementById('container_results');
        const containerIdInput = document.getElementById('container_id');
        const containerSelectedInfo = document.getElementById('container_selected_info');

        containerSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 1) {
                containerResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                searchContainers(query);
            }, 300);
        });

        function searchContainers(query) {
            console.log('Recherche de containers avec query:', query);
            fetch(`/api/containers/search?query=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                console.log('Réponse reçue:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Données reçues:', data);
                displayContainerResults(data);
            })
            .catch(error => {
                console.error('Erreur lors de la recherche:', error);
            });
        }

        function displayContainerResults(containers) {
            if (containers.length === 0) {
                containerResults.innerHTML = '<div class="list-group-item">Aucun contenant trouvé</div>';
                containerResults.style.display = 'block';
                return;
            }

            containerResults.innerHTML = containers.map(container => `
                <a href="#" class="list-group-item list-group-item-action" data-container-id="${container.id}" data-container-code="${container.code}" data-shelf="${container.shelf_code}" data-room="${container.room_name}">
                    <strong>${container.code}</strong><br>
                    <small class="text-muted">Étagère: ${container.shelf_code} | Salle: ${container.room_name}</small>
                </a>
            `).join('');

            containerResults.style.display = 'block';

            // Ajouter les événements de clic
            containerResults.querySelectorAll('.list-group-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectContainer(this);
                });
            });
        }

        function selectContainer(element) {
            const containerId = element.dataset.containerId;
            const containerCode = element.dataset.containerCode;
            const shelf = element.dataset.shelf;
            const room = element.dataset.room;

            containerIdInput.value = containerId;
            containerSearchInput.value = containerCode;
            containerSelectedInfo.textContent = `Étagère: ${shelf} | Salle: ${room}`;
            containerResults.style.display = 'none';
        }

        // Masquer les résultats quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!containerSearchInput.contains(e.target) && !containerResults.contains(e.target)) {
                containerResults.style.display = 'none';
            }
        });
    </script>
@endsection

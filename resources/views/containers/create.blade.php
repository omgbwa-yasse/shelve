@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="">
            <div class="">
                <h2 class="mb-0">Créer un contenant d'archives</h2>
            </div>
            <!-- Formulaire - Plus large -->
            <div class="form-card fade-in-up">
                <form action="{{ route('containers.store') }}" method="POST" id="containerForm">
                    @csrf
                    <div class="form-body">
                        <div class="mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="shelve_id" class="form-label">Étagère</label>
                            <input type="hidden" id="shelve_id" name="shelve_id" required>
                            <div class="shelf-selector">
                                <button type="button" class="btn btn-outline-primary w-100 d-flex justify-content-between align-items-center" id="shelfSelectorBtn" onclick="openShelfModal()">
                                    <span id="selectedShelfText">
                                        <i class="bi bi-bookshelf me-2"></i>Sélectionner une étagère
                                    </span>
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <div class="mt-2" id="selectedShelfInfo" style="display: none;">
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <div>
                                            <strong id="selectedShelfDetails"></strong>
                                            <br><small id="selectedShelfPath" class="text-muted"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="status_id" class="form-label">Status</label>
                            <div class="select-with-search">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control search-input" placeholder="Search status...">
                                </div>
                                <select class="form-select mt-2" id="status_id" name="status_id" required>
                                    <option value="">Select a status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="property_id" class="form-label">Property</label>
                            <div class="select-with-search">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control search-input" placeholder="Search property...">
                                </div>
                                <select class="form-select mt-2" id="property_id" name="property_id" required>
                                    <option value="">Select a property</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Create Container</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 0;
        }
        .card-header {
            border-bottom: none;
        }
        .form-label {
            font-weight: 600;
        }
        .select-with-search {
            position: relative;
        }
        .select-with-search .search-input {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }
        .select-with-search .form-select {
            border-color: #ced4da;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #ced4da;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectWithSearch = document.querySelectorAll('.select-with-search');

            selectWithSearch.forEach(container => {
                const searchInput = container.querySelector('.search-input');
                const select = container.querySelector('select');
                const options = Array.from(select.options).slice(1); // Exclude the first option

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    options.forEach(option => {
                        const optionText = option.textContent.toLowerCase();
                        if (optionText.includes(searchTerm)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Reset selection and show placeholder option
                    select.selectedIndex = 0;
                    select.options[0].style.display = '';

                    // If no visible options, show a "No results" option
                    const visibleOptions = options.filter(option => option.style.display !== 'none');
                    if (visibleOptions.length === 0) {
                        const noResultsOption = select.querySelector('option[data-no-results]');
                        if (!noResultsOption) {
                            const newNoResultsOption = document.createElement('option');
                            newNoResultsOption.textContent = 'No results found';
                            newNoResultsOption.disabled = true;
                            newNoResultsOption.setAttribute('data-no-results', 'true');
                            select.appendChild(newNoResultsOption);
                        } else {
                            noResultsOption.style.display = '';
                        }
                    } else {
                        const noResultsOption = select.querySelector('option[data-no-results]');
                        if (noResultsOption) {
                            noResultsOption.style.display = 'none';
                        }
                    }
                });

                // Clear search input when select changes
                select.addEventListener('change', function() {
                    searchInput.value = '';
                    options.forEach(option => option.style.display = '');
                    const noResultsOption = select.querySelector('option[data-no-results]');
                    if (noResultsOption) {
                        noResultsOption.style.display = 'none';
                    }
                });
            });
        });
    </script>

    <!-- Modal de sélection d'étagère organisé par bâtiment -->
    <div class="modal fade" id="shelfSelectionModal" tabindex="-1" aria-labelledby="shelfSelectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shelfSelectionModalLabel">
                        <i class="bi bi-building text-primary me-2"></i>
                        Sélectionner une étagère
                        <small class="text-muted">({{ $shelves->count() }} étagères trouvées)</small>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Recherche globale -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="globalShelfSearch" placeholder="Rechercher par code d'étagère ou nom de salle...">
                        </div>
                    </div>

                    <!-- Accordéon par bâtiment -->
                    <div class="accordion" id="buildingAccordion">
                        @if($shelves->count() > 0)
                            @php
                                $shelfsByBuilding = $shelves->groupBy(function($shelf) {
                                    return $shelf->room->floor->building->name ?? 'Bâtiment inconnu';
                                });
                            @endphp

                            @foreach($shelfsByBuilding as $buildingName => $buildingShelves)
                            @php
                                $buildingId = 'building_' . $loop->index;
                                $roomsByFloor = $buildingShelves->groupBy(function($shelf) {
                                    return $shelf->room->floor->name . '|' . $shelf->room->name;
                                });
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $buildingId }}" aria-expanded="false" aria-controls="{{ $buildingId }}">
                                        <i class="bi bi-building text-primary me-2"></i>
                                        <strong>{{ $buildingName }}</strong>
                                        <span class="badge bg-primary ms-2">{{ $buildingShelves->count() }} étagères</span>
                                    </button>
                                </h2>
                                <div id="{{ $buildingId }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#buildingAccordion">
                                    <div class="accordion-body">
                                        @foreach($roomsByFloor as $roomKey => $roomShelves)
                                            @php
                                                [$floorName, $roomName] = explode('|', $roomKey);
                                                $firstShelf = $roomShelves->first();
                                            @endphp
                                            <div class="room-section mb-4">
                                                <h6 class="text-muted mb-3">
                                                    <i class="bi bi-layers me-1"></i>{{ $floorName }} →
                                                    <i class="bi bi-door-open me-1"></i>{{ $roomName }}
                                                    <span class="badge bg-secondary ms-2">{{ $roomShelves->count() }} étagères</span>
                                                </h6>
                                                <div class="row">
                                                    @foreach($roomShelves as $shelf)
                                                        <div class="col-md-6 col-lg-4 mb-2">
                                                            <div class="shelf-card border rounded p-3 cursor-pointer hover-shadow" onclick="selectShelf({{ $shelf->id }}, '{{ $shelf->code }}', '{{ $buildingName }}', '{{ $floorName }}', '{{ $roomName }}', {{ $shelf->total_capacity ?? 0 }}, {{ $shelf->occupied_spots ?? 0 }})">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <div class="fw-bold text-primary">{{ $shelf->code }}</div>
                                                                        @if($shelf->observation)
                                                                            <small class="text-muted">{{ Str::limit($shelf->observation, 30) }}</small>
                                                                        @endif
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <small class="text-muted">{{ $shelf->face ?? 0 }}×{{ $shelf->ear ?? 0 }}×{{ $shelf->shelf ?? 0 }}</small>
                                                                        @if(($shelf->total_capacity ?? 0) > 0)
                                                                            <br><small class="badge bg-{{ ($shelf->occupied_spots ?? 0) > ($shelf->total_capacity * 0.8) ? 'warning' : 'success' }}">
                                                                                {{ $shelf->occupied_spots ?? 0 }}/{{ $shelf->total_capacity }}
                                                                            </small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune étagère disponible</h5>
                                <p class="text-muted">
                                    Aucune étagère n'est associée à votre organisation actuelle.<br>
                                    Veuillez contacter l'administrateur pour ajouter des étagères à votre organisation.
                                </p>
                                <small class="text-muted">
                                    Organisation actuelle : <strong>{{ Auth::user()->currentOrganisation->name ?? 'Non définie' }}</strong>
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openShelfModal() {
            const modal = new bootstrap.Modal(document.getElementById('shelfSelectionModal'));
            modal.show();
        }

        function selectShelf(shelfId, shelfCode, buildingName, floorName, roomName, totalCapacity, occupiedSpots) {
            // Mettre à jour le champ caché
            document.getElementById('shelve_id').value = shelfId;

            // Mettre à jour le bouton de sélection
            document.getElementById('selectedShelfText').innerHTML = `
                <i class="bi bi-bookshelf me-2"></i>${shelfCode}
            `;

            // Afficher les informations détaillées
            document.getElementById('selectedShelfDetails').textContent = `Étagère ${shelfCode}`;
            document.getElementById('selectedShelfPath').textContent = `${buildingName} → ${floorName} → ${roomName}`;

            // Ajouter les informations de capacité si disponibles
            if (totalCapacity > 0) {
                const occupancyPercent = Math.round((occupiedSpots / totalCapacity) * 100);
                document.getElementById('selectedShelfPath').textContent += ` • ${occupiedSpots}/${totalCapacity} emplacements (${occupancyPercent}%)`;
            }

            document.getElementById('selectedShelfInfo').style.display = 'block';

            // Fermer le modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('shelfSelectionModal'));
            modal.hide();
        }

        // Recherche globale dans le modal
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('globalShelfSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const shelfCards = document.querySelectorAll('.shelf-card');
                    const roomSections = document.querySelectorAll('.room-section');

                    // Filtrer les cartes d'étagères
                    shelfCards.forEach(card => {
                        const cardText = card.textContent.toLowerCase();
                        const cardContainer = card.closest('.col-md-6');

                        if (cardText.includes(searchTerm)) {
                            cardContainer.style.display = 'block';
                        } else {
                            cardContainer.style.display = 'none';
                        }
                    });

                    // Masquer les sections de salles vides
                    roomSections.forEach(section => {
                        const visibleCards = section.querySelectorAll('.col-md-6[style="display: block"], .col-md-6:not([style*="display: none"])');
                        if (visibleCards.length === 0 && searchTerm !== '') {
                            section.style.display = 'none';
                        } else {
                            section.style.display = 'block';
                        }
                    });

                    // Ouvrir automatiquement les accordéons qui contiennent des résultats
                    if (searchTerm !== '') {
                        const accordionItems = document.querySelectorAll('.accordion-item');
                        accordionItems.forEach(item => {
                            const visibleSections = item.querySelectorAll('.room-section[style="display: block"], .room-section:not([style*="display: none"])');
                            const collapseElement = item.querySelector('.accordion-collapse');

                            if (visibleSections.length > 0) {
                                collapseElement.classList.add('show');
                                item.querySelector('.accordion-button').classList.remove('collapsed');
                                item.querySelector('.accordion-button').setAttribute('aria-expanded', 'true');
                            }
                        });
                    }
                });
            }
        });
    </script>

    <style>
        .shelf-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .shelf-card:hover {
            background-color: #f8f9fa;
            border-color: #007bff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .hover-shadow:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .room-section {
            border-left: 3px solid #e9ecef;
            padding-left: 1rem;
        }
    </style>
@endsection

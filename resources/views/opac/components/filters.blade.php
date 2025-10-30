{{-- Composant filtres OPAC --}}
<div class="filters-container" id="opac-filters">
    <div class="filters-header">
        <h3 class="filters-title">
            <i class="fas fa-filter"></i>
            {{ $title ?? __('Filtrer les résultats') }}
        </h3>

        @if($showToggle ?? true)
            <button type="button"
                    class="btn btn-sm btn-outline-secondary filters-toggle d-lg-none"
                    data-bs-toggle="collapse"
                    data-bs-target="#filters-content"
                    aria-expanded="false"
                    aria-controls="filters-content">
                <i class="fas fa-chevron-down"></i>
                <span class="toggle-text">{{ __('Afficher les filtres') }}</span>
            </button>
        @endif

        @if($hasActiveFilters ?? false)
            <div class="active-filters-count">
                <span class="badge bg-primary">
                    {{ $activeFiltersCount ?? 0 }} {{ __('actif(s)') }}
                </span>
            </div>
        @endif
    </div>

    <div class="collapse {{ $showByDefault ?? true ? 'show' : '' }} d-lg-block" id="filters-content">
        <form method="GET" action="{{ $actionUrl ?? request()->url() }}" class="filters-form" id="filters-form">
            {{-- Préserver les paramètres de recherche existants --}}
            @if($preserveQuery ?? true)
                @foreach(request()->except(array_merge(['page'], $excludeParams ?? [])) as $key => $value)
                    @if(!in_array($key, $filterKeys ?? []))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
            @endif

            <div class="filters-content">
                {{-- Type de document --}}
                @if($showTypeFilter ?? true)
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-file-alt"></i>
                            {{ __('Type de document') }}
                        </label>
                        <div class="filter-options">
                            <div class="form-check">
                                <input type="radio"
                                       class="form-check-input"
                                       name="type"
                                       value=""
                                       id="type-all"
                                       {{ !request('type') ? 'checked' : '' }}>
                                <label class="form-check-label" for="type-all">
                                    {{ __('Tous les types') }}
                                </label>
                            </div>
                            @foreach($documentTypes ?? $this->getDefaultDocumentTypes() as $type => $label)
                                <div class="form-check">
                                    <input type="radio"
                                           class="form-check-input"
                                           name="type"
                                           value="{{ $type }}"
                                           id="type-{{ $type }}"
                                           {{ request('type') == $type ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type-{{ $type }}">
                                        <i class="fas {{ $this->getTypeIcon($type) }}"></i>
                                        {{ $label }}
                                        @if(isset($typeCounts[$type]))
                                            <span class="filter-count">({{ $typeCounts[$type] }})</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Langue --}}
                @if($showLanguageFilter ?? true)
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-globe"></i>
                            {{ __('Langue') }}
                        </label>
                        <select name="language" class="form-select form-select-sm">
                            <option value="">{{ __('Toutes les langues') }}</option>
                            @foreach($languages ?? $this->getDefaultLanguages() as $code => $name)
                                <option value="{{ $code }}" {{ request('language') == $code ? 'selected' : '' }}>
                                    {{ $name }}
                                    @if(isset($languageCounts[$code]))
                                        ({{ $languageCounts[$code] }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Période de publication --}}
                @if($showDateFilter ?? true)
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-calendar"></i>
                            {{ __('Période de publication') }}
                        </label>
                        <div class="date-range-inputs">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="year_from" class="form-label form-label-sm">{{ __('De') }}</label>
                                    <input type="number"
                                           name="year_from"
                                           id="year_from"
                                           class="form-control form-control-sm"
                                           placeholder="1900"
                                           min="1900"
                                           max="{{ date('Y') }}"
                                           value="{{ request('year_from') }}">
                                </div>
                                <div class="col-6">
                                    <label for="year_to" class="form-label form-label-sm">{{ __('À') }}</label>
                                    <input type="number"
                                           name="year_to"
                                           id="year_to"
                                           class="form-control form-control-sm"
                                           placeholder="{{ date('Y') }}"
                                           min="1900"
                                           max="{{ date('Y') }}"
                                           value="{{ request('year_to') }}">
                                </div>
                            </div>
                        </div>

                        @if($showPresetRanges ?? true)
                            <div class="preset-ranges mt-2">
                                <div class="btn-group btn-group-sm preset-buttons" role="group">
                                    <button type="button" class="btn btn-outline-secondary preset-range" data-range="last-year">
                                        {{ __('Dernière année') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary preset-range" data-range="last-5-years">
                                        {{ __('5 dernières années') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary preset-range" data-range="last-10-years">
                                        {{ __('10 dernières années') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Disponibilité --}}
                @if($showAvailabilityFilter ?? true)
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-check-circle"></i>
                            {{ __('Disponibilité') }}
                        </label>
                        <div class="filter-options">
                            @foreach($availabilityStatuses ?? $this->getDefaultAvailabilityStatuses() as $status => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="availability[]"
                                           value="{{ $status }}"
                                           id="availability-{{ $status }}"
                                           {{ in_array($status, request('availability', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="availability-{{ $status }}">
                                        <i class="fas {{ $this->getAvailabilityIcon($status) }}"></i>
                                        {{ $label }}
                                        @if(isset($availabilityCounts[$status]))
                                            <span class="filter-count">({{ $availabilityCounts[$status] }})</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Collections --}}
                @if($showCollectionFilter ?? false && !empty($collections))
                    <div class="filter-section">
                        <label class="filter-label">
                            <i class="fas fa-folder"></i>
                            {{ __('Collections') }}
                        </label>
                        <select name="collection" class="form-select form-select-sm">
                            <option value="">{{ __('Toutes les collections') }}</option>
                            @foreach($collections as $collection)
                                <option value="{{ $collection->id }}"
                                        {{ request('collection') == $collection->id ? 'selected' : '' }}>
                                    {{ $collection->name }}
                                    @if(isset($collectionCounts[$collection->id]))
                                        ({{ $collectionCounts[$collection->id] }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Filtres personnalisés --}}
                @if(isset($customFilters) && !empty($customFilters))
                    @foreach($customFilters as $filter)
                        <div class="filter-section">
                            <label class="filter-label">
                                @if(isset($filter['icon']))
                                    <i class="fas {{ $filter['icon'] }}"></i>
                                @endif
                                {{ $filter['label'] }}
                            </label>

                            @if($filter['type'] === 'select')
                                <select name="{{ $filter['name'] }}" class="form-select form-select-sm">
                                    <option value="">{{ $filter['placeholder'] ?? __('Tous') }}</option>
                                    @foreach($filter['options'] as $value => $label)
                                        <option value="{{ $value }}"
                                                {{ request($filter['name']) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif($filter['type'] === 'checkbox')
                                <div class="filter-options">
                                    @foreach($filter['options'] as $value => $label)
                                        <div class="form-check">
                                            <input type="checkbox"
                                                   class="form-check-input"
                                                   name="{{ $filter['name'] }}[]"
                                                   value="{{ $value }}"
                                                   id="{{ $filter['name'] }}-{{ $value }}"
                                                   {{ in_array($value, request($filter['name'], [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $filter['name'] }}-{{ $value }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="filters-actions">
                <button type="submit" class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="fas fa-search"></i>
                    {{ __('Appliquer les filtres') }}
                </button>

                @if($showResetButton ?? true)
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 reset-filters">
                        <i class="fas fa-times"></i>
                        {{ __('Réinitialiser') }}
                    </button>
                @endif
            </div>
        </form>

        {{-- Filtres actifs --}}
        @if($showActiveFilters ?? true)
            <div class="active-filters" id="active-filters">
                {{-- Les filtres actifs seront injectés ici via JavaScript --}}
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.filters-container {
    background: var(--card-bg, #ffffff);
    border: var(--border-width, 1px) solid var(--border-color, #dee2e6);
    border-radius: var(--border-radius, 0.375rem);
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border-color, #dee2e6);
}

.filters-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark-color, #343a40);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filters-toggle {
    position: relative;
}

.filters-toggle .fas {
    transition: transform 0.2s ease;
}

.filters-toggle[aria-expanded="true"] .fas {
    transform: rotate(180deg);
}

.active-filters-count {
    display: flex;
    align-items: center;
}

.filter-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--light-color, #f8f9fa);
}

.filter-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.filter-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--dark-color, #343a40);
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-check-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: color 0.2s ease;
}

.form-check-label:hover {
    color: var(--primary-color, #007bff);
}

.filter-count {
    color: var(--text-muted, #6c757d);
    font-size: 0.85rem;
    margin-left: auto;
}

.date-range-inputs .form-label-sm {
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.preset-buttons {
    width: 100%;
}

.preset-range {
    flex: 1;
    font-size: 0.8rem;
}

.filters-actions {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color, #dee2e6);
}

.active-filters {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color, #dee2e6);
}

.active-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: var(--primary-color, #007bff);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius, 0.375rem);
    font-size: 0.8rem;
    margin: 0.25rem 0.25rem 0.25rem 0;
    text-decoration: none;
    transition: background-color 0.2s ease;
}

.active-filter-tag:hover {
    background: var(--primary-color-hover, #0056b3);
    color: white;
    text-decoration: none;
}

.active-filter-remove {
    background: none;
    border: none;
    color: white;
    padding: 0;
    margin-left: 0.25rem;
    cursor: pointer;
    font-size: 0.9rem;
}

.active-filter-remove:hover {
    color: #ffcccb;
}

/* Responsive design */
@media (max-width: 991.98px) {
    .filters-container {
        position: sticky;
        top: 100px; /* Ajuster selon la hauteur de la navigation */
        z-index: 1010;
    }

    .filters-header {
        cursor: pointer;
    }
}

@media (max-width: 575.98px) {
    .filters-container {
        padding: 0.75rem;
        position: static;
    }

    .filter-section {
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
    }

    .preset-buttons {
        flex-direction: column;
    }

    .preset-range {
        width: 100%;
        margin-bottom: 0.25rem;
    }

    .date-range-inputs .row {
        margin: 0;
    }
}

/* Animation des filtres */
.filter-section {
    transition: all 0.3s ease;
}

.filters-form.loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Accessibilité */
.form-check-input:focus {
    outline: 2px solid var(--primary-color, #007bff);
    outline-offset: 2px;
}

.form-select:focus,
.form-control:focus {
    border-color: var(--primary-color, #007bff);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-color-rgb, 0, 123, 255), 0.25);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersForm = document.getElementById('filters-form');
    const resetButton = document.querySelector('.reset-filters');
    const toggleButton = document.querySelector('.filters-toggle');
    const activeFiltersContainer = document.getElementById('active-filters');

    // Auto-soumission pour certains champs
    const autoSubmitFields = filtersForm.querySelectorAll('select, input[type="radio"]');
    autoSubmitFields.forEach(field => {
        field.addEventListener('change', function() {
            if (this.type !== 'number') { // Éviter l'auto-soumission pour les champs numériques
                debounceSubmit();
            }
        });
    });

    // Soumission retardée pour les champs de date
    const dateFields = filtersForm.querySelectorAll('input[type="number"]');
    dateFields.forEach(field => {
        field.addEventListener('input', function() {
            debounceSubmit(1000); // Attendre 1 seconde
        });
    });

    let submitTimeout;
    function debounceSubmit(delay = 300) {
        clearTimeout(submitTimeout);
        submitTimeout = setTimeout(() => {
            filtersForm.classList.add('loading');
            filtersForm.submit();
        }, delay);
    }

    // Réinitialisation des filtres
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            // Décocher tous les checkboxes et radios
            filtersForm.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(input => {
                input.checked = false;
            });

            // Réinitialiser les selects
            filtersForm.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
            });

            // Vider les champs de texte et nombre
            filtersForm.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => {
                input.value = '';
            });

            // Cocher le premier radio "Tous" si disponible
            const allTypeRadio = filtersForm.querySelector('input[name="type"][value=""]');
            if (allTypeRadio) {
                allTypeRadio.checked = true;
            }

            // Soumettre le formulaire
            filtersForm.submit();
        });
    }

    // Gestion des plages de dates prédéfinies
    document.querySelectorAll('.preset-range').forEach(button => {
        button.addEventListener('click', function() {
            const range = this.dataset.range;
            const currentYear = new Date().getFullYear();
            const yearFromInput = document.getElementById('year_from');
            const yearToInput = document.getElementById('year_to');

            switch (range) {
                case 'last-year':
                    yearFromInput.value = currentYear - 1;
                    yearToInput.value = currentYear;
                    break;
                case 'last-5-years':
                    yearFromInput.value = currentYear - 5;
                    yearToInput.value = currentYear;
                    break;
                case 'last-10-years':
                    yearFromInput.value = currentYear - 10;
                    yearToInput.value = currentYear;
                    break;
            }

            // Mettre en évidence le bouton sélectionné
            document.querySelectorAll('.preset-range').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            debounceSubmit();
        });
    });

    // Gestion du toggle mobile
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const toggleText = this.querySelector('.toggle-text');

            if (isExpanded) {
                toggleText.textContent = "{{ __('Masquer les filtres') }}";
            } else {
                toggleText.textContent = "{{ __('Afficher les filtres') }}";
            }
        });
    }

    // Affichage des filtres actifs
    function updateActiveFilters() {
        if (!activeFiltersContainer) return;

        const activeFilters = [];
        const formData = new FormData(filtersForm);

        // Traiter tous les champs du formulaire
        for (const [name, value] of formData.entries()) {
            if (value && name !== 'query' && !name.startsWith('_')) {
                const field = filtersForm.querySelector(`[name="${name}"]`);
                let label = name;
                let displayValue = value;

                if (field) {
                    const fieldLabel = filtersForm.querySelector(`label[for="${field.id}"]`);
                    if (fieldLabel) {
                        label = fieldLabel.textContent.trim();
                    }

                    if (field.tagName === 'SELECT') {
                        const selectedOption = field.querySelector(`option[value="${value}"]`);
                        if (selectedOption) {
                            displayValue = selectedOption.textContent;
                        }
                    }
                }

                activeFilters.push({
                    name: name,
                    value: value,
                    label: label,
                    displayValue: displayValue
                });
            }
        }

        // Générer le HTML des filtres actifs
        if (activeFilters.length > 0) {
            const filtersHTML = activeFilters.map(filter => `
                <span class="active-filter-tag">
                    ${filter.label}: ${filter.displayValue}
                    <button type="button" class="active-filter-remove" data-filter="${filter.name}" data-value="${filter.value}">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
            `).join('');

            activeFiltersContainer.innerHTML = `
                <div class="mb-2">
                    <strong>{{ __('Filtres actifs') }}:</strong>
                </div>
                ${filtersHTML}
            `;

            // Ajouter les événements de suppression
            activeFiltersContainer.querySelectorAll('.active-filter-remove').forEach(removeBtn => {
                removeBtn.addEventListener('click', function() {
                    const filterName = this.dataset.filter;
                    const filterValue = this.dataset.value;

                    // Trouver et décocher/vider le champ correspondant
                    const field = filtersForm.querySelector(`[name="${filterName}"]`);
                    if (field) {
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = false;
                        } else {
                            field.value = '';
                        }
                    }

                    // Soumettre le formulaire
                    filtersForm.submit();
                });
            });
        } else {
            activeFiltersContainer.innerHTML = '';
        }
    }

    // Mettre à jour les filtres actifs au chargement
    updateActiveFilters();

    // Validation des dates
    const yearFromInput = document.getElementById('year_from');
    const yearToInput = document.getElementById('year_to');

    if (yearFromInput && yearToInput) {
        function validateDateRange() {
            const yearFrom = parseInt(yearFromInput.value);
            const yearTo = parseInt(yearToInput.value);

            if (yearFrom && yearTo && yearFrom > yearTo) {
                yearToInput.setCustomValidity("{{ __('L\\'année de fin doit être supérieure à l\\'année de début') }}");
            } else {
                yearToInput.setCustomValidity('');
            }
        }

        yearFromInput.addEventListener('change', validateDateRange);
        yearToInput.addEventListener('change', validateDateRange);
    }
});

// Fonctions helper pour les icônes (peuvent être surchargées)
window.opacFiltersHelpers = {
    getTypeIcon: function(type) {
        const icons = {
            'book': 'fa-book',
            'article': 'fa-newspaper',
            'multimedia': 'fa-play-circle',
            'thesis': 'fa-graduation-cap',
            'report': 'fa-file-alt',
            'map': 'fa-map',
            'image': 'fa-image',
            'audio': 'fa-music',
            'video': 'fa-video'
        };
        return icons[type] || 'fa-file';
    },

    getAvailabilityIcon: function(status) {
        const icons = {
            'available': 'fa-check-circle',
            'borrowed': 'fa-clock',
            'reserved': 'fa-bookmark',
            'unavailable': 'fa-times-circle'
        };
        return icons[status] || 'fa-question-circle';
    }
};
</script>
@endpush

@php
// Méthodes helper pour le composant
if (!function_exists('getDefaultDocumentTypes')) {
    function getDefaultDocumentTypes() {
        return [
            'book' => __('Livres'),
            'article' => __('Articles'),
            'multimedia' => __('Multimédia'),
            'thesis' => __('Thèses'),
            'report' => __('Rapports'),
            'map' => __('Cartes'),
            'image' => __('Images'),
            'audio' => __('Audio'),
            'video' => __('Vidéo')
        ];
    }
}

if (!function_exists('getDefaultLanguages')) {
    function getDefaultLanguages() {
        return [
            'fr' => __('Français'),
            'en' => __('Anglais'),
            'es' => __('Espagnol'),
            'de' => __('Allemand'),
            'it' => __('Italien'),
            'pt' => __('Portugais'),
            'ar' => __('Arabe'),
            'zh' => __('Chinois'),
            'ja' => __('Japonais'),
            'ru' => __('Russe')
        ];
    }
}

if (!function_exists('getDefaultAvailabilityStatuses')) {
    function getDefaultAvailabilityStatuses() {
        return [
            'available' => __('Disponible'),
            'borrowed' => __('Emprunté'),
            'reserved' => __('Réservé'),
            'unavailable' => __('Indisponible')
        ];
    }
}
@endphp

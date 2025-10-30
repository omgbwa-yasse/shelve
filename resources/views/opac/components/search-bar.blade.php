{{-- Composant barre de recherche OPAC --}}
<div class="search-bar-container">
    <form method="GET" action="{{ route('opac.search') }}" class="search-form" role="search">
        <div class="search-input-group">
            <div class="search-input-wrapper">
                <input
                    type="text"
                    name="query"
                    id="opac-search-input"
                    class="form-control search-input"
                    placeholder="{{ $placeholder ?? 'Rechercher dans le catalogue...' }}"
                    value="{{ request('query') }}"
                    autocomplete="off"
                    aria-label="Recherche dans le catalogue"
                >

                @if($showAdvancedToggle ?? true)
                    <button
                        type="button"
                        class="btn btn-outline-secondary advanced-toggle"
                        data-bs-toggle="collapse"
                        data-bs-target="#advanced-search"
                        aria-expanded="false"
                        aria-controls="advanced-search"
                        title="Recherche avancée"
                    >
                        <i class="fas fa-cog"></i>
                    </button>
                @endif

                <button
                    type="submit"
                    class="btn btn-primary search-submit"
                    title="Rechercher"
                >
                    <i class="fas fa-search"></i>
                    <span class="d-none d-md-inline">{{ $buttonText ?? 'Rechercher' }}</span>
                </button>
            </div>

            @if($showFilters ?? true)
                <div class="search-filters-quick">
                    <select name="type" class="form-select form-select-sm" aria-label="Type de document">
                        <option value="">Tous les types</option>
                        <option value="book" {{ request('type') == 'book' ? 'selected' : '' }}>Livres</option>
                        <option value="article" {{ request('type') == 'article' ? 'selected' : '' }}>Articles</option>
                        <option value="multimedia" {{ request('type') == 'multimedia' ? 'selected' : '' }}>Multimédia</option>
                        <option value="thesis" {{ request('type') == 'thesis' ? 'selected' : '' }}>Thèses</option>
                    </select>

                    <select name="sort" class="form-select form-select-sm" aria-label="Ordre de tri">
                        <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Pertinence</option>
                        <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Plus récent</option>
                        <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Plus ancien</option>
                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Titre A-Z</option>
                        <option value="author_asc" {{ request('sort') == 'author_asc' ? 'selected' : '' }}>Auteur A-Z</option>
                    </select>
                </div>
            @endif
        </div>

        @if($showAdvanced ?? true)
            <div class="collapse" id="advanced-search">
                <div class="advanced-search-panel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="author" class="form-label">Auteur</label>
                            <input
                                type="text"
                                name="author"
                                id="author"
                                class="form-control"
                                value="{{ request('author') }}"
                                placeholder="Nom de l'auteur"
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="subject" class="form-label">Sujet</label>
                            <input
                                type="text"
                                name="subject"
                                id="subject"
                                class="form-control"
                                value="{{ request('subject') }}"
                                placeholder="Mots-clés du sujet"
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="year_from" class="form-label">Année de</label>
                            <input
                                type="number"
                                name="year_from"
                                id="year_from"
                                class="form-control"
                                value="{{ request('year_from') }}"
                                min="1900"
                                max="{{ date('Y') }}"
                                placeholder="1900"
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="year_to" class="form-label">Année à</label>
                            <input
                                type="number"
                                name="year_to"
                                id="year_to"
                                class="form-control"
                                value="{{ request('year_to') }}"
                                min="1900"
                                max="{{ date('Y') }}"
                                placeholder="{{ date('Y') }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="language" class="form-label">Langue</label>
                            <select name="language" id="language" class="form-select">
                                <option value="">Toutes les langues</option>
                                <option value="fr" {{ request('language') == 'fr' ? 'selected' : '' }}>Français</option>
                                <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>Anglais</option>
                                <option value="es" {{ request('language') == 'es' ? 'selected' : '' }}>Espagnol</option>
                                <option value="de" {{ request('language') == 'de' ? 'selected' : '' }}>Allemand</option>
                            </select>
                        </div>
                    </div>

                    <div class="advanced-search-actions mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetAdvancedSearch()">
                            <i class="fas fa-times"></i> Effacer
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </form>

    @if($showAutoComplete ?? true)
        <div class="search-suggestions" id="search-suggestions" style="display: none;">
            {{-- Les suggestions seront injectées ici via JavaScript --}}
        </div>
    @endif
</div>

@push('styles')
<style>
.search-bar-container {
    margin-bottom: 2rem;
}

.search-input-group {
    position: relative;
}

.search-input-wrapper {
    display: flex;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    background: var(--input-bg, #ffffff);
    border: var(--border-width) solid var(--input-border-color, #ced4da);
    transition: all 0.15s ease-in-out;
}

.search-input-wrapper:focus-within {
    border-color: var(--input-focus-border-color, #80bdff);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-color-rgb, 0, 123, 255), 0.25);
}

.search-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.search-input:focus {
    outline: none;
    box-shadow: none;
}

.advanced-toggle {
    border: none;
    border-left: var(--border-width) solid var(--input-border-color, #ced4da);
    border-radius: 0;
    padding: 0.75rem;
}

.search-submit {
    border: none;
    border-left: var(--border-width) solid var(--input-border-color, #ced4da);
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
    padding: 0.75rem 1.25rem;
    background: var(--primary-color, #007bff);
    color: white;
    transition: background-color 0.15s ease-in-out;
}

.search-submit:hover {
    background: var(--primary-color-hover, #0056b3);
}

.search-filters-quick {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.search-filters-quick .form-select {
    width: auto;
    min-width: 120px;
}

.advanced-search-panel {
    background: var(--light-color, #f8f9fa);
    border: var(--border-width) solid var(--border-color, #dee2e6);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-top: 1rem;
}

.advanced-search-actions {
    text-align: right;
    padding-top: 1rem;
    border-top: var(--border-width) solid var(--border-color, #dee2e6);
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: var(--border-width) solid var(--border-color, #dee2e6);
    border-top: none;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
    box-shadow: var(--box-shadow);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

.suggestion-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f4;
    transition: background-color 0.15s ease;
}

.suggestion-item:hover,
.suggestion-item.active {
    background-color: var(--light-color, #f8f9fa);
}

.suggestion-item:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .search-filters-quick {
        flex-direction: column;
    }

    .search-filters-quick .form-select {
        width: 100%;
        min-width: auto;
    }

    .advanced-search-panel .row {
        margin: 0;
    }

    .advanced-search-actions {
        text-align: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('opac-search-input');
    const suggestionsContainer = document.getElementById('search-suggestions');

    @if($showAutoComplete ?? true)
    // Auto-complétion
    let suggestionTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(suggestionTimeout);
        const query = this.value.trim();

        if (query.length >= 3) {
            suggestionTimeout = setTimeout(() => {
                fetchSuggestions(query);
            }, 300);
        } else {
            hideSuggestions();
        }
    });

    searchInput.addEventListener('keydown', function(e) {
        const activeItem = suggestionsContainer.querySelector('.suggestion-item.active');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextItem = activeItem ? activeItem.nextElementSibling : suggestionsContainer.firstElementChild;
            if (nextItem) {
                if (activeItem) activeItem.classList.remove('active');
                nextItem.classList.add('active');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevItem = activeItem ? activeItem.previousElementSibling : suggestionsContainer.lastElementChild;
            if (prevItem) {
                if (activeItem) activeItem.classList.remove('active');
                prevItem.classList.add('active');
            }
        } else if (e.key === 'Enter' && activeItem) {
            e.preventDefault();
            searchInput.value = activeItem.textContent;
            hideSuggestions();
            searchInput.closest('form').submit();
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    // Masquer les suggestions en cliquant à l'extérieur
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });

    function fetchSuggestions(query) {
        fetch(`{{ route('opac.autocomplete') }}?query=${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            showSuggestions(data.suggestions || []);
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des suggestions:', error);
            hideSuggestions();
        });
    }

    function showSuggestions(suggestions) {
        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }

        const html = suggestions.map(suggestion =>
            `<div class="suggestion-item" data-value="${suggestion.value}">
                ${suggestion.label}
            </div>`
        ).join('');

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';

        // Ajouter les événements de clic
        suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                searchInput.value = this.dataset.value;
                hideSuggestions();
                searchInput.closest('form').submit();
            });
        });
    }

    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
        suggestionsContainer.innerHTML = '';
    }
    @endif
});

function resetAdvancedSearch() {
    const form = document.querySelector('.search-form');
    const advancedInputs = form.querySelectorAll('#advanced-search input, #advanced-search select');

    advancedInputs.forEach(input => {
        if (input.type === 'text' || input.type === 'number') {
            input.value = '';
        } else if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        }
    });
}
</script>
@endpush

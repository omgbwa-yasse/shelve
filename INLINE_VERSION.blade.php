{{-- Alternative avec CSS et JS inline pour create.blade.php --}}

{{-- Remplacer la section @push('styles') par : --}}
@push('styles')
<style>
/* Styles pour l'autocomplétion des records */
.autocomplete-suggestions {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    border-radius: 0 0 6px 6px;
    border: 1px solid #dee2e6;
    border-top: none;
    background-color: #fff;
}

.autocomplete-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.15s ease-in-out;
    font-size: 14px;
    color: #495057;
}

.autocomplete-item:last-child {
    border-bottom: none;
    border-radius: 0 0 6px 6px;
}

.autocomplete-item:hover,
.autocomplete-item.bg-light {
    background-color: #f8f9fa !important;
    color: #212529;
}

.autocomplete-item:active {
    background-color: #e9ecef !important;
}

/* Animation d'apparition */
.autocomplete-suggestions:not(.d-none) {
    animation: fadeInDown 0.15s ease-out;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Styles pour le champ de recherche */
.record-search-container {
    position: relative;
}

.record-search-input:focus + .autocomplete-suggestions {
    border-color: #80bdff;
}
</style>
@endpush

{{-- Remplacer la section @push('scripts') par : --}}
@push('scripts')
<script>
/**
 * Autocomplétion pour la recherche de records
 */
class RecordAutocomplete {
    constructor(searchInputId, hiddenInputId, suggestionsContainerId, options = {}) {
        this.searchInput = document.getElementById(searchInputId);
        this.hiddenInput = document.getElementById(hiddenInputId);
        this.suggestionsContainer = document.getElementById(suggestionsContainerId);
        this.currentSelection = -1;
        this.searchTimeout = null;

        // Options par défaut
        this.options = {
            minChars: 3,
            maxResults: 5,
            delay: 300,
            apiUrl: '/records/autocomplete',
            ...options
        };

        this.init();
    }

    init() {
        if (!this.searchInput || !this.hiddenInput || !this.suggestionsContainer) {
            console.error('RecordAutocomplete: Éléments requis introuvables');
            return;
        }

        this.bindEvents();
    }

    bindEvents() {
        // Événement de saisie
        this.searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            if (query !== this.searchInput.dataset.selectedLabel) {
                this.hiddenInput.value = '';
            }
            this.search(query);
        });

        // Navigation au clavier
        this.searchInput.addEventListener('keydown', (e) => {
            this.handleKeyNavigation(e);
        });

        // Fermer les suggestions en cliquant ailleurs
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && !this.suggestionsContainer.contains(e.target)) {
                this.hideSuggestions();
            }
        });

        // Validation lors de la soumission du formulaire
        const form = this.searchInput.closest('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!this.hiddenInput.value && this.searchInput.value) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un document dans la liste de suggestions.');
                    this.searchInput.focus();
                }
            });
        }
    }

    search(query) {
        if (query.length < this.options.minChars) {
            this.hideSuggestions();
            return;
        }

        // Annuler la recherche précédente
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Délai avant de faire la recherche
        this.searchTimeout = setTimeout(() => {
            const url = `${this.options.apiUrl}?q=${encodeURIComponent(query)}&limit=${this.options.maxResults}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        this.showSuggestions(data.data);
                    } else {
                        this.hideSuggestions();
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la recherche:', error);
                    this.hideSuggestions();
                });
        }, this.options.delay);
    }

    showSuggestions(suggestions) {
        this.suggestionsContainer.innerHTML = '';

        suggestions.forEach((suggestion, index) => {
            const item = this.createSuggestionItem(suggestion, index);
            this.suggestionsContainer.appendChild(item);
        });

        this.suggestionsContainer.classList.remove('d-none');
        this.currentSelection = -1;
    }

    createSuggestionItem(suggestion, index) {
        const item = document.createElement('div');
        item.className = 'autocomplete-item p-2 cursor-pointer border-bottom';
        item.style.cursor = 'pointer';
        item.textContent = suggestion.label;
        item.dataset.value = suggestion.id;
        item.dataset.label = suggestion.label;
        item.dataset.index = index;

        // Événements de survol
        item.addEventListener('mouseenter', () => {
            this.clearSelection();
            item.classList.add('bg-light');
            this.currentSelection = index;
        });

        item.addEventListener('mouseleave', () => {
            item.classList.remove('bg-light');
        });

        // Événement de clic
        item.addEventListener('click', () => {
            this.selectSuggestion(suggestion.id, suggestion.label);
        });

        return item;
    }

    hideSuggestions() {
        this.suggestionsContainer.classList.add('d-none');
        this.currentSelection = -1;
    }

    clearSelection() {
        const items = this.suggestionsContainer.querySelectorAll('.autocomplete-item');
        items.forEach(item => item.classList.remove('bg-light'));
    }

    selectSuggestion(value, label) {
        this.hiddenInput.value = value;
        this.searchInput.value = label;
        this.searchInput.dataset.selectedLabel = label;
        this.hideSuggestions();
    }

    handleKeyNavigation(e) {
        const items = this.suggestionsContainer.querySelectorAll('.autocomplete-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (this.currentSelection < items.length - 1) {
                this.clearSelection();
                this.currentSelection++;
                items[this.currentSelection].classList.add('bg-light');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (this.currentSelection > 0) {
                this.clearSelection();
                this.currentSelection--;
                items[this.currentSelection].classList.add('bg-light');
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (this.currentSelection >= 0 && items[this.currentSelection]) {
                const selectedItem = items[this.currentSelection];
                this.selectSuggestion(selectedItem.dataset.value, selectedItem.dataset.label);
            }
        } else if (e.key === 'Escape') {
            this.hideSuggestions();
        }
    }
}

// Initialisation automatique si les éléments sont présents
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('record_search_input')) {
        new RecordAutocomplete('record_search_input', 'record_id', 'record_suggestions');
    }
});
</script>
@endpush

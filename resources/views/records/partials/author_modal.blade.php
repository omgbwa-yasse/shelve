<!-- Author Modal -->
<div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorModalLabel">{{ __('manage_authors') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="select-tab" data-bs-toggle="tab" data-bs-target="#select-authors" type="button" role="tab">
                            <i class="bi bi-list-check me-2"></i>{{ __('select_authors') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add-author" type="button" role="tab">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('add_new_author') }}
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content">
                    <!-- Select Authors Tab -->
                    <div class="tab-pane fade show active" id="select-authors" role="tabpanel">
                        <!-- Alphabet Filter -->
                        <div class="alphabet-filter mb-3 border-bottom pb-2">
                            <div class="d-flex flex-wrap gap-1 mb-2" id="alphabet-buttons">
                                <!-- Les boutons seront générés par JavaScript -->
                            </div>
                        </div>

                        <!-- Search Bar -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="author-search" class="form-control" placeholder="{{ __('search_authors') }}">
                            </div>
                        </div>

                        <!-- Authors List -->
                        <div class="author-list-container" style="max-height: 400px; overflow-y: auto;">
                            <div class="list-group" id="author-list">
                                @foreach ($authors as $author)
                                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                       data-id="{{ $author->id }}"
                                       data-type="{{ $author->authorType->name ?? '' }}"
                                       data-initial="{{ Str::upper(Str::substr($author->name, 0, 1)) }}">
                                        <div>
                                            <div class="fw-bold">{{ $author->name }}</div>
                                            @if($author->authorType)
                                                <small class="text-muted">{{ $author->authorType->name }}</small>
                                            @endif
                                        </div>
                                        <span class="selection-indicator">
                                            <i class="bi bi-check2-circle text-success d-none"></i>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">{{ __('selected_authors_count') }}: <span id="selected-count">0</span></small>
                        </div>
                    </div>

                    <!-- Add Author Tab (reste inchangé) -->
                    <div class="tab-pane fade" id="add-author" role="tabpanel">
                        <!-- Le contenu du formulaire reste le même -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto" id="selection-info"></small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>{{ __('close') }}
                </button>
                <button type="button" class="btn btn-primary" id="save-authors">
                    <i class="bi bi-check2-circle me-2"></i>{{ __('save_selection') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeAuthorModal();
});

function initializeAuthorModal() {
    const modal = document.getElementById('authorModal');
    const searchInput = document.getElementById('author-search');
    const authorList = document.getElementById('author-list');
    const selectedCountElement = document.getElementById('selected-count');
    const selectionInfoElement = document.getElementById('selection-info');
    const saveButton = document.getElementById('save-authors');
    const alphabetContainer = document.getElementById('alphabet-buttons');

    let selectedAuthors = new Set();
    let currentLetter = null;
    let searchTimeout = null;

    // Générer les boutons alphabétiques
    function generateAlphabetButtons() {
        const alphabet = '#ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
        const buttons = alphabet.map(letter => {
            const isHash = letter === '#';
            return `
                <button type="button"
                        class="btn btn-outline-primary btn-sm alphabet-btn ${currentLetter === letter ? 'active' : ''}"
                        data-letter="${letter}">
                    ${isHash ? __('other') : letter}
                </button>
            `;
        });
        alphabetContainer.innerHTML = buttons.join('');

        // Ajouter les événements aux boutons
        document.querySelectorAll('.alphabet-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.alphabet-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentLetter = btn.dataset.letter;
                filterAuthors();
            });
        });
    }

    // Filtrer les auteurs avec délai
    function filterAuthors() {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        searchTimeout = setTimeout(() => {
            const searchText = searchInput.value.toLowerCase();
            const items = authorList.querySelectorAll('.list-group-item');

            items.forEach(item => {
                const authorName = item.querySelector('.fw-bold').textContent.toLowerCase();
                const authorType = item.dataset.type.toLowerCase();
                const initial = item.dataset.initial;

                let shouldShow = true;

                // Filtre par lettre
                if (currentLetter) {
                    if (currentLetter === '#') {
                        shouldShow = !/^[A-Z]/.test(initial);
                    } else {
                        shouldShow = initial === currentLetter;
                    }
                }

                // Filtre par recherche
                if (shouldShow && searchText) {
                    shouldShow = authorName.includes(searchText) || authorType.includes(searchText);
                }

                item.style.display = shouldShow ? '' : 'none';
            });
        }, 200); // Délai de 200ms
    }

    // Initialiser les filtres
    generateAlphabetButtons();

    // Event listeners
    searchInput.addEventListener('input', filterAuthors);

    function updateAuthorSelection(item) {
        const authorId = item.dataset.id;
        const indicator = item.querySelector('.selection-indicator i');

        if (selectedAuthors.has(authorId)) {
            selectedAuthors.delete(authorId);
            item.classList.remove('active');
            indicator.classList.add('d-none');
        } else {
            selectedAuthors.add(authorId);
            item.classList.add('active');
            indicator.classList.remove('d-none');
        }

        updateSelectionInfo();
    }

    function updateSelectionInfo() {
        const count = selectedAuthors.size;
        selectedCountElement.textContent = count;

        if (count > 0) {
            selectionInfoElement.textContent = `${count} ${count === 1 ? __('author_selected') : __('authors_selected')}`;
            saveButton.disabled = false;
        } else {
            selectionInfoElement.textContent = __('no_authors_selected');
            saveButton.disabled = true;
        }
    }

    // Event listener pour la sélection des auteurs
    authorList.addEventListener('click', (e) => {
        const item = e.target.closest('.list-group-item');
        if (item) {
            e.preventDefault();
            updateAuthorSelection(item);
        }
    });

    // Sauvegarde de la sélection
    saveButton.addEventListener('click', () => {
        const selectedItems = authorList.querySelectorAll('.list-group-item.active');
        const displayInput = document.getElementById('selected-authors-display');
        const idsInput = document.getElementById('author-ids');

        const names = Array.from(selectedItems).map(item =>
            item.querySelector('.fw-bold').textContent.trim()
        );
        const ids = Array.from(selectedItems).map(item => item.dataset.id);

        displayInput.value = names.join('; ');
        idsInput.value = ids.join(',');

        bootstrap.Modal.getInstance(modal).hide();
    });

    // Réinitialiser les filtres lors de l'ouverture du modal
    modal.addEventListener('shown.bs.modal', () => {
        currentLetter = null;
        searchInput.value = '';
        document.querySelectorAll('.alphabet-btn').forEach(btn => btn.classList.remove('active'));
        filterAuthors();
    });
}

// Les fonctions showToast et refreshAuthorList restent inchangées
</script>


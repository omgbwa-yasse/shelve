{{-- partials/author_modal.blade.php --}}
<div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorModalLabel">{{ __('manage_authors') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#select-authors">
                            {{ __('select_authors') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#add-author">
                            {{ __('add_new_author') }}
                        </a>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content mt-3">
                    <!-- Select Authors Tab -->
                    <div class="tab-pane fade show active" id="select-authors">
                        <div class="mb-3">
                            <input type="text" id="author-search" class="form-control" placeholder="{{ __('search_authors') }}">
                        </div>
                        <div class="list-group" id="author-list">
                            <!-- Authors will be loaded here via AJAX -->
                        </div>
                    </div>

                    <!-- Add Author Tab -->
                    <div class="tab-pane fade" id="add-author">
                        <form id="add-author-form">
                            @csrf
                            <div class="mb-3">
                                <label for="author_type" class="form-label">{{ __('author_type') }}</label>
                                <select class="form-select" id="author_type" name="type_id" required>
                                    <!-- Author types will be loaded here via AJAX -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="author_name" class="form-label">{{ __('name') }}</label>
                                <input type="text" class="form-control" id="author_name" name="name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('add_author') }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-primary" id="save-authors">{{ __('save') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedAuthors = new Set();

            // Charge les auteurs au chargement du modal
            async function loadAuthors() {
                try {
                    const response = await fetch('/api/authors');
                    const authors = await response.json();
                    renderAuthors(authors);
                } catch (error) {
                    console.error('Error loading authors:', error);
                }
            }

            // Charge les types d'auteurs
            async function loadAuthorTypes() {
                try {
                    const response = await fetch('/api/author-types');
                    const types = await response.json();
                    const select = document.getElementById('author_type');
                    types.forEach(type => {
                        const option = new Option(type.name, type.id);
                        select.add(option);
                    });
                } catch (error) {
                    console.error('Error loading author types:', error);
                }
            }

            // Rendu de la liste des auteurs
            function renderAuthors(authors) {
                const list = document.getElementById('author-list');
                list.innerHTML = authors.map(author => `
            <a href="#" class="list-group-item list-group-item-action" data-id="${author.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${author.name}</strong>
                        <br>
                        <small class="text-muted">${author.type_name}</small>
                    </div>
                    <div class="selected-indicator" style="display: none;">
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </div>
                </div>
            </a>
        `).join('');

                // Rétablir les sélections
                document.querySelectorAll('#author-list .list-group-item').forEach(item => {
                    if (selectedAuthors.has(item.dataset.id)) {
                        item.classList.add('active');
                        item.querySelector('.selected-indicator').style.display = 'block';
                    }
                });
            }

            // Gestionnaire de recherche
            document.getElementById('author-search').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                document.querySelectorAll('#author-list .list-group-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });

            // Gestionnaire de sélection d'auteur
            document.getElementById('author-list').addEventListener('click', function(e) {
                const item = e.target.closest('.list-group-item');
                if (!item) return;

                e.preventDefault();
                const authorId = item.dataset.id;
                const indicator = item.querySelector('.selected-indicator');

                if (selectedAuthors.has(authorId)) {
                    selectedAuthors.delete(authorId);
                    item.classList.remove('active');
                    indicator.style.display = 'none';
                } else {
                    selectedAuthors.add(authorId);
                    item.classList.add('active');
                    indicator.style.display = 'block';
                }
            });

            // Gestionnaire d'ajout d'auteur
            document.getElementById('add-author-form').addEventListener('submit', async function(e) {
                e.preventDefault();

                try {
                    const formData = new FormData(this);
                    const response = await fetch('/api/authors', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    });

                    if (response.ok) {
                        const author = await response.json();
                        await loadAuthors();
                        this.reset();
                        // Basculer vers l'onglet de sélection
                        const selectTab = document.querySelector('[href="#select-authors"]');
                        bootstrap.Tab.getOrCreateInstance(selectTab).show();
                    }
                } catch (error) {
                    console.error('Error adding author:', error);
                }
            });

            // Gestionnaire de sauvegarde
            document.getElementById('save-authors').addEventListener('click', function() {
                const selectedItems = document.querySelectorAll('#author-list .list-group-item.active');
                const names = Array.from(selectedItems).map(item =>
                    item.querySelector('strong').textContent
                );
                const ids = Array.from(selectedItems).map(item => item.dataset.id);

                document.getElementById('author-ids').value = ids.join(',');
                document.getElementById('selected-authors-display').value = names.join('; ');

                bootstrap.Modal.getInstance(document.getElementById('authorModal')).hide();
            });

            // Chargement initial
            document.getElementById('authorModal').addEventListener('show.bs.modal', function() {
                loadAuthors();
                loadAuthorTypes();
            });
        });
    </script>
@endpush

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
                        <!-- Alphabet filter -->
                        <div class="mb-3 d-flex flex-wrap gap-1 alphabet-filter">
                            <button class="btn btn-sm btn-outline-primary active" data-filter="all">{{ __('all') }}</button>
                            <!-- Alphabet buttons will be added by JavaScript -->
                        </div>
                        <div class="list-group" id="author-list">
                            <!-- Authors will be loaded here via AJAX -->
                        </div>
                        <div class="mt-2 text-center" id="pagination">
                            <!-- Pagination will be handled by JavaScript -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('close') }}</button>
                            <button type="button" class="btn btn-primary btn-sm" id="save-authors">{{ __('save') }}</button>
                        </div>
                    </div>

                    <!-- Add Author Tab -->
                    <div class="tab-pane fade" id="add-author">
                        <form id="add-author-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="author_type" class="form-label">{{ __('type') }}</label>
                                    <select class="form-select form-select-sm" id="author_type" name="type_id" required>
                                        <!-- Author types will be loaded here -->
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="author_name" class="form-label">{{ __('name') }}</label>
                                    <input type="text" class="form-control form-control-sm" id="author_name" name="name" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('parallel_name') }}</label>
                                    <input type="text" name="parallel_name" class="form-control form-control-sm">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('other_name') }}</label>
                                    <input type="text" name="other_name" class="form-control form-control-sm">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('lifespan') }}</label>
                                    <input type="text" name="lifespan" class="form-control form-control-sm">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('locations') }}</label>
                                    <input type="text" name="locations" class="form-control form-control-sm">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('parent_author') }}</label>
                                    <div class="input-group input-group-sm">
                                        <input type="hidden" name="parent_id" id="parent_id">
                                        <input type="text" id="parent_name" class="form-control">
                                        <button type="button" class="btn btn-outline-secondary" id="selectParent">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" id="clearParent">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary btn-sm"> Enregistrer </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables
        const authorModal = document.getElementById('authorModal');
        const authorSearch = document.getElementById('author-search');
        const authorList = document.getElementById('author-list');
        const pagination = document.getElementById('pagination');
        const alphabetFilter = document.querySelector('.alphabet-filter');
        const saveAuthorsBtn = document.getElementById('save-authors');
        const addAuthorForm = document.getElementById('add-author-form');
        const parentSearchBtn = document.getElementById('selectParent');
        const clearParentBtn = document.getElementById('clearParent');

        let currentPage = 1;
        let selectedAuthors = [];
        let currentFilter = 'all';

        // Initialize the modal
        if (authorModal) {
            authorModal.addEventListener('show.bs.modal', function() {
                loadAuthorTypes();
                initializeSelectedAuthorsFromForm();
                loadAuthors();
                initializeAlphabetFilter();
            });
        }

        // Initialize selected authors from form
        function initializeSelectedAuthorsFromForm() {
            selectedAuthors = [];
            const formAuthors = document.querySelectorAll('#selected-authors-container .selected-author');

            formAuthors.forEach(authorElement => {
                const authorId = parseInt(authorElement.dataset.id);
                const authorName = authorElement.querySelector('span').textContent.split(' (')[0]; // Extract name without type
                const authorTypeText = authorElement.querySelector('span').textContent.includes('(') ?
                    authorElement.querySelector('span').textContent.split(' (')[1].replace(')', '') : '';

                const author = {
                    id: authorId,
                    name: authorName,
                    authorType: authorTypeText ? { name: authorTypeText } : null
                };

                selectedAuthors.push(author);
            });
        }

        // Initialize alphabet filter
        function initializeAlphabetFilter() {
            // Clear existing buttons except "All"
            const allButton = alphabetFilter.querySelector('[data-filter="all"]');
            alphabetFilter.innerHTML = '';
            alphabetFilter.appendChild(allButton);

            // Add alphabet buttons
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').forEach(letter => {
                const button = document.createElement('button');
                button.className = 'btn btn-sm btn-outline-primary';
                button.textContent = letter;
                button.dataset.filter = letter;
                button.addEventListener('click', function() {
                    setActiveFilter(letter);
                    loadAuthors(1, letter);
                });
                alphabetFilter.appendChild(button);
            });

            // Set "All" button event
            allButton.addEventListener('click', function() {
                setActiveFilter('all');
                loadAuthors(1, 'all');
            });
        }

        // Set active filter button
        function setActiveFilter(filter) {
            currentFilter = filter;
            alphabetFilter.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.filter === filter) {
                    btn.classList.add('active');
                }
            });
        }

        // Load authors via AJAX
        function loadAuthors(page = 1, filter = 'all') {
            currentPage = page;
            authorList.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div></div>';

            const searchTerm = authorSearch.value.trim();
            const url = new URL(`{{ route('author-handler.list') }}`);
            url.searchParams.append('page', page);
            url.searchParams.append('filter', filter);
            if (searchTerm) {
                url.searchParams.append('search', searchTerm);
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    renderAuthors(data);
                    renderPagination(data);
                })
                .catch(error => {
                    authorList.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
                });
        }

        // Render authors list
        function renderAuthors(data) {
            if (data.data.length === 0) {
                authorList.innerHTML = `<div class="alert alert-info">${data.message || 'No authors found'}</div>`;
                return;
            }

            authorList.innerHTML = '';
            data.data.forEach(author => {
                const isSelected = selectedAuthors.some(a => a.id === author.id);
                const item = document.createElement('div');
                item.className = `list-group-item list-group-item-action d-flex justify-content-between align-items-center ${isSelected ? 'active' : ''}`;
                item.dataset.id = author.id;

                const authorInfo = document.createElement('div');
                authorInfo.innerHTML = `
                    <strong>${author.name}</strong>
                    ${author.authorType ? `<span class="badge bg-secondary ms-2">${author.authorType.name}</span>` : ''}
                    ${author.lifespan ? `<small class="text-muted d-block">${author.lifespan}</small>` : ''}
                `;

                const selectBtn = document.createElement('button');
                selectBtn.className = `btn btn-sm ${isSelected ? 'btn-danger' : 'btn-primary'}`;
                selectBtn.innerHTML = isSelected ? 'Remove' : 'Select';
                selectBtn.onclick = function() {
                    toggleAuthorSelection(author, item);
                };

                item.appendChild(authorInfo);
                item.appendChild(selectBtn);
                authorList.appendChild(item);
            });
        }

        // Render pagination
        function renderPagination(data) {
            if (!data.pagination || data.pagination.total_pages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            pagination.innerHTML = '';
            const nav = document.createElement('nav');
            const ul = document.createElement('ul');
            ul.className = 'pagination pagination-sm justify-content-center';

            // Previous button
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${data.pagination.current_page === 1 ? 'disabled' : ''}`;
            const prevLink = document.createElement('a');
            prevLink.className = 'page-link';
            prevLink.href = '#';
            prevLink.textContent = 'Previous';
            if (data.pagination.current_page > 1) {
                prevLink.onclick = function(e) {
                    e.preventDefault();
                    loadAuthors(data.pagination.current_page - 1, currentFilter);
                };
            }
            prevLi.appendChild(prevLink);
            ul.appendChild(prevLi);

            // Page numbers
            const totalPages = data.pagination.total_pages;
            const currentPage = data.pagination.current_page;

            // Display logic for page numbers
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageLi = document.createElement('li');
                pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
                const pageLink = document.createElement('a');
                pageLink.className = 'page-link';
                pageLink.href = '#';
                pageLink.textContent = i;
                pageLink.onclick = function(e) {
                    e.preventDefault();
                    loadAuthors(i, currentFilter);
                };
                pageLi.appendChild(pageLink);
                ul.appendChild(pageLi);
            }

            // Next button
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${data.pagination.current_page === data.pagination.total_pages ? 'disabled' : ''}`;
            const nextLink = document.createElement('a');
            nextLink.className = 'page-link';
            nextLink.href = '#';
            nextLink.textContent = 'Next';
            if (data.pagination.current_page < data.pagination.total_pages) {
                nextLink.onclick = function(e) {
                    e.preventDefault();
                    loadAuthors(data.pagination.current_page + 1, currentFilter);
                };
            }
            nextLi.appendChild(nextLink);
            ul.appendChild(nextLi);

            nav.appendChild(ul);
            pagination.appendChild(nav);
        }

        // Toggle author selection
        function toggleAuthorSelection(author, element) {
            console.log('Toggle sélection pour auteur:', author);
            const index = selectedAuthors.findIndex(a => a.id === author.id);
            if (index === -1) {
                // Ajouter l'auteur
                selectedAuthors.push(author);
                element.classList.add('active');
                element.querySelector('button').className = 'btn btn-sm btn-danger';
                element.querySelector('button').textContent = 'Remove';
                console.log('Auteur ajouté à la sélection:', author.name);
            } else {
                // Retirer l'auteur
                selectedAuthors.splice(index, 1);
                element.classList.remove('active');
                element.querySelector('button').className = 'btn btn-sm btn-primary';
                element.querySelector('button').textContent = 'Select';
                console.log('Auteur retiré de la sélection:', author.name);
            }
            console.log('Auteurs actuellement sélectionnés:', selectedAuthors);
        }

        // Load author types
        function loadAuthorTypes() {
            const typeSelect = document.getElementById('author_type');
            fetch(`{{ route('author-handler.types') }}`)
                .then(response => response.json())
                .then(data => {
                    typeSelect.innerHTML = '<option value="">Select type</option>';
                    data.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.name;
                        typeSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading author types:', error);
                });
        }

        // Save selected authors
        if (saveAuthorsBtn) {
            saveAuthorsBtn.addEventListener('click', function() {
                console.log('Bouton Save cliqué, auteurs sélectionnés:', selectedAuthors);
                const modal = bootstrap.Modal.getInstance(authorModal);

                // Déclencher l'événement avec les auteurs sélectionnés
                const event = new CustomEvent('authorsSelected', {
                    detail: { authors: selectedAuthors }
                });
                document.dispatchEvent(event);
                console.log('Événement authorsSelected déclenché');

                modal.hide();
            });
        }

        // Author search functionality
        if (authorSearch) {
            authorSearch.addEventListener('input', debounce(function() {
                loadAuthors(1, currentFilter);
            }, 500));
        }




        // Add author form submission
        if (addAuthorForm) {
                // Utiliser un drapeau pour empêcher les soumissions en double
                let estEnSoumission = false;

                addAuthorForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Si déjà en soumission, ne pas traiter à nouveau
                    if (estEnSoumission) return;
                    estEnSoumission = true;

                    // Désactiver le bouton de soumission
                    const submitButton = addAuthorForm.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Ajout en cours...';

                    const formData = new FormData(addAuthorForm);

                    fetch(`{{ route('author-handler.store') }}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Le serveur a répondu avec ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {

                        // Réinitialiser le statut de soumission
                        estEnSoumission = false;
                        submitButton.disabled = false;
                        submitButton.innerHTML = '{{ __("add") }}';

                        if (data.success) {
                            // Ajouter l'auteur nouvellement créé aux auteurs sélectionnés
                            selectedAuthors.push(data.author);

                            // Afficher un message de succès
                            const successAlert = document.createElement('div');
                            successAlert.className = 'alert alert-success alert-dismissible fade show';
                            successAlert.innerHTML = `
                                ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            addAuthorForm.prepend(successAlert);

                            // Réinitialiser le formulaire
                            addAuthorForm.reset();
                            document.getElementById('parent_id').value = '';
                            document.getElementById('parent_name').value = '';

                            // Passer à l'onglet de sélection des auteurs
                            const selectTab = new bootstrap.Tab(document.querySelector('a[href="#select-authors"]'));
                            selectTab.show();

                            // Recharger la liste des auteurs
                            loadAuthors();
                        } else {
                            // Afficher un message d'erreur
                            const errorAlert = document.createElement('div');
                            errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                            errorAlert.innerHTML = `
                                ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            addAuthorForm.prepend(errorAlert);
                        }
                    })
                    .catch(error => {
                        // Réinitialiser le statut de soumission
                        estEnSoumission = false;
                        submitButton.disabled = false;
                        submitButton.innerHTML = '{{ __("add") }}';

                        console.error('Erreur lors de l\'ajout de l\'auteur:', error);

                        // Afficher un message d'erreur
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                        errorAlert.innerHTML = `
                            Une erreur s'est produite lors du traitement de votre demande: ${error.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        addAuthorForm.prepend(errorAlert);
                    });
                });
            }








        // Debounce function to prevent excessive API calls
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }





        // Parent author selection functionality
        if (parentSearchBtn && clearParentBtn) {
            // Create results dropdown container
            const resultsContainer = document.createElement('div');
            resultsContainer.className = 'dropdown-menu w-100';
            resultsContainer.style.display = 'none';
            resultsContainer.style.position = 'absolute';
            resultsContainer.style.zIndex = '1000';
            document.getElementById('parent_name').parentNode.style.position = 'relative';
            document.getElementById('parent_name').parentNode.appendChild(resultsContainer);

            // Search input event
            document.getElementById('parent_name').addEventListener('input', debounce(function() {
                const searchTerm = this.value.trim();

                if (searchTerm.length < 2) {
                    resultsContainer.style.display = 'none';
                    return;
                }

                fetch(`{{ route('author-handler.list') }}?search=${searchTerm}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';

                        // Limit results to between 3 and 7 authors
                        const authors = data.data.slice(0, 7);
                        if (authors.length < 3) {
                            // If less than 3 results, try to get more with a less strict search
                            const broadSearchTerm = searchTerm.substring(0, Math.max(2, searchTerm.length - 1));
                            if (broadSearchTerm !== searchTerm && broadSearchTerm.length >= 2) {
                                fetch(`{{ route('author-handler.list') }}?search=${broadSearchTerm}`)
                                    .then(response => response.json())
                                    .then(broadData => {
                                        displayAuthorResults(broadData.data.slice(0, 7));
                                    });
                                return;
                            }
                        }

                        displayAuthorResults(authors);
                    });
            }, 300));

            // Display author results
            function displayAuthorResults(authors) {
                resultsContainer.innerHTML = '';

                if (authors.length > 0) {
                    authors.forEach(author => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'dropdown-item';
                        item.innerHTML = `<strong>${author.name}</strong>${author.type ? ` <span class="badge bg-secondary">${author.type.name}</span>` : ''}`;
                        item.onclick = function(e) {
                            e.preventDefault();
                            document.getElementById('parent_id').value = author.id;
                            document.getElementById('parent_name').value = author.name;
                            resultsContainer.style.display = 'none';
                        };
                        resultsContainer.appendChild(item);
                    });
                    resultsContainer.style.display = 'block';
                } else {
                    resultsContainer.style.display = 'none';
                }
            }

            // Search button click
            parentSearchBtn.addEventListener('click', function() {
                const searchTerm = document.getElementById('parent_name').value.trim();
                if (searchTerm.length > 0) {
                    fetch(`{{ route('author-handler.list') }}?search=${searchTerm}`)
                        .then(response => response.json())
                        .then(data => {
                            displayAuthorResults(data.data.slice(0, 7));
                        });
                } else {
                    // If empty search, show a selection of authors
                    fetch(`{{ route('author-handler.list') }}`)
                        .then(response => response.json())
                        .then(data => {
                            displayAuthorResults(data.data.slice(0, 7));
                        });
                }
            });

            // Clear button click
            clearParentBtn.addEventListener('click', function() {
                document.getElementById('parent_id').value = '';
                document.getElementById('parent_name').value = '';
                resultsContainer.style.display = 'none';
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(event) {
                const parentInput = document.getElementById('parent_name');
                if (!parentInput.contains(event.target) &&
                    !resultsContainer.contains(event.target) &&
                    !parentSearchBtn.contains(event.target)) {
                    resultsContainer.style.display = 'none';
                }
            });

        }


    });



    </script>
@endpush

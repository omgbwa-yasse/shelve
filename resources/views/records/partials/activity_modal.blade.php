{{-- partials/activity_modal.blade.php --}}
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">{{ __('select_activity') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <input type="text" id="activity-search" class="form-control" placeholder="{{ __('search_activity') }}">
                    </div>
                    <div class="col-auto">
                        <button id="activity-search-clear" class="btn btn-secondary">{{ __('clear') }}</button>
                    </div>
                </div>

                <!-- Alphabet filter -->
                <div class="mb-3 d-flex flex-wrap gap-1 alphabet-filter">
                    <button class="btn btn-sm btn-outline-primary active" data-filter="all">{{ __('all') }}</button>
                    <!-- Alphabet buttons will be added by JS -->
                </div>

                <!-- Breadcrumb navigation -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb" id="activity-breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">{{ __('root_level') }}</li>
                    </ol>
                </nav>

                <div id="activity-list" class="list-group">
                <!-- Activities will be loaded here -->
                    <div class="text-center">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="mt-2 text-center" id="activity-pagination">
                    <!-- Pagination will be added here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-primary" id="save-activity">{{ __('save') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables
        const activityModal = document.getElementById('activityModal');
        const activitySearch = document.getElementById('activity-search');
        const activitySearchClear = document.getElementById('activity-search-clear');
        const activityList = document.getElementById('activity-list');
        const activityPagination = document.getElementById('activity-pagination');
        const alphabetFilter = document.querySelector('.alphabet-filter');
        const saveActivityBtn = document.getElementById('save-activity');
        const activityBreadcrumb = document.getElementById('activity-breadcrumb');

        let currentPage = 1;
        let currentFilter = 'all';
        let currentParentId = null;
        let breadcrumbTrail = [];
        let selectedActivity = null;

        // Initialize the modal
        if (activityModal) {
            console.log('Activity modal found, adding event listener');
            // Vérifier si Bootstrap est disponible
            if (typeof bootstrap !== 'undefined') {
                activityModal.addEventListener('show.bs.modal', function() {
                    console.log('Modal show event triggered');
                    initializeAlphabetFilter();
                    resetBreadcrumb();
                    loadActivities();
                });
            } else {
                console.warn('Bootstrap non disponible - modal activity ne peut pas être initialisé');
            }
        } else {
            console.error('Activity modal not found!');
        }

        // Initialize alphabet filter
        function initializeAlphabetFilter() {
            // Clear existing buttons except "All"
            const allButton = alphabetFilter.querySelector('[data-filter="all"]');
            alphabetFilter.innerHTML = '';
            alphabetFilter.appendChild(allButton);

            // Ajouter une div pour la ligne des chiffres
            const numbersDiv = document.createElement('div');
            numbersDiv.className = 'w-100 mb-1 d-flex flex-wrap gap-1';
            alphabetFilter.appendChild(numbersDiv);

            // Ajouter les boutons pour les chiffres (1-9)
            '123456789'.split('').forEach(number => {
                const button = document.createElement('button');
                button.className = 'btn btn-sm btn-outline-primary';
                button.textContent = number;
                button.dataset.filter = number;
                button.addEventListener('click', function() {
                    setActiveFilter(number);
                    loadActivities(1, number, currentParentId);
                });
                numbersDiv.appendChild(button);
            });

            // Ajouter le bouton # pour les caractères spéciaux
            const specialButton = document.createElement('button');
            specialButton.className = 'btn btn-sm btn-outline-primary';
            specialButton.textContent = '#';
            specialButton.dataset.filter = '#';
            specialButton.addEventListener('click', function() {
                setActiveFilter('#');
                loadActivities(1, '#', currentParentId);
            });
            numbersDiv.appendChild(specialButton);

            // Ajouter une div pour la ligne des lettres
            const alphabetDiv = document.createElement('div');
            alphabetDiv.className = 'w-100 d-flex flex-wrap gap-1';
            alphabetFilter.appendChild(alphabetDiv);

            // Add alphabet buttons
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').forEach(letter => {
                const button = document.createElement('button');
                button.className = 'btn btn-sm btn-outline-primary';
                button.textContent = letter;
                button.dataset.filter = letter;
                button.addEventListener('click', function() {
                    setActiveFilter(letter);
                    loadActivities(1, letter, currentParentId);
                });
                alphabetDiv.appendChild(button);
            });

            // Set "All" button event
            allButton.addEventListener('click', function() {
                setActiveFilter('all');
                loadActivities(1, 'all', currentParentId);
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

        // Reset breadcrumb to root level
        function resetBreadcrumb() {
            breadcrumbTrail = [];
            currentParentId = null;
            activityBreadcrumb.innerHTML = '<li class="breadcrumb-item active" aria-current="page">{{ __("root_level") }}</li>';
        }

        // Update breadcrumb with a new level
        function updateBreadcrumb(activity) {
            // Add this activity to the breadcrumb trail
            breadcrumbTrail.push({
                id: activity.id,
                name: activity.name,
                code: activity.code
            });

            // Rebuild the breadcrumb UI
            activityBreadcrumb.innerHTML = '';

            // Add root level
            const rootItem = document.createElement('li');
            rootItem.className = 'breadcrumb-item';
            const rootLink = document.createElement('a');
            rootLink.href = '#';
            rootLink.textContent = '{{ __("root_level") }}';
            rootLink.addEventListener('click', function(e) {
                e.preventDefault();
                resetBreadcrumb();
                loadActivities(1, currentFilter);
            });
            rootItem.appendChild(rootLink);
            activityBreadcrumb.appendChild(rootItem);

            // Add each level in the trail
            breadcrumbTrail.forEach((item, index) => {
                const li = document.createElement('li');

                if (index === breadcrumbTrail.length - 1) {
                    li.className = 'breadcrumb-item active';
                    li.setAttribute('aria-current', 'page');
                    li.textContent = `${item.code} - ${item.name}`;
                } else {
                    li.className = 'breadcrumb-item';
                    const link = document.createElement('a');
                    link.href = '#';
                    link.textContent = `${item.code} - ${item.name}`;
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Trim the breadcrumb trail to this level
                        breadcrumbTrail = breadcrumbTrail.slice(0, index + 1);
                        currentParentId = item.id;
                        updateBreadcrumbFromTrail();
                        loadActivities(1, currentFilter, currentParentId);
                    });
                    li.appendChild(link);
                }

                activityBreadcrumb.appendChild(li);
            });
        }

        // Update breadcrumb from trail without adding new items
        function updateBreadcrumbFromTrail() {
            activityBreadcrumb.innerHTML = '';

            // Add root level
            const rootItem = document.createElement('li');
            rootItem.className = 'breadcrumb-item';
            const rootLink = document.createElement('a');
            rootLink.href = '#';
            rootLink.textContent = '{{ __("root_level") }}';
            rootLink.addEventListener('click', function(e) {
                e.preventDefault();
                resetBreadcrumb();
                loadActivities(1, currentFilter);
            });
            rootItem.appendChild(rootLink);
            activityBreadcrumb.appendChild(rootItem);

            // Add each level in the trail
            breadcrumbTrail.forEach((item, index) => {
                const li = document.createElement('li');

                if (index === breadcrumbTrail.length - 1) {
                    li.className = 'breadcrumb-item active';
                    li.setAttribute('aria-current', 'page');
                    li.textContent = `${item.code} - ${item.name}`;
                } else {
                    li.className = 'breadcrumb-item';
                    const link = document.createElement('a');
                    link.href = '#';
                    link.textContent = `${item.code} - ${item.name}`;
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Trim the breadcrumb trail to this level
                        breadcrumbTrail = breadcrumbTrail.slice(0, index + 1);
                        currentParentId = item.id;
                        updateBreadcrumbFromTrail();
                        loadActivities(1, currentFilter, currentParentId);
                    });
                    li.appendChild(link);
                }

                activityBreadcrumb.appendChild(li);
            });
        }

        // Load activities via AJAX
        function loadActivities(page = 1, filter = 'all', parentId = null) {
            currentPage = page;
            currentFilter = filter;
            currentParentId = parentId;

            console.log('Loading activities:', { page, filter, parentId });
            activityList.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div></div>';

            const searchTerm = activitySearch.value.trim();
            const routeUrl = `{{ route('activity-handler.list') }}`;
            console.log('Route URL:', routeUrl);

            const url = new URL(routeUrl);
            url.searchParams.append('page', page);
            url.searchParams.append('filter', filter);

            if (searchTerm) {
                url.searchParams.append('search', searchTerm);
            }

            if (parentId !== null) {
                url.searchParams.append('parent_id', parentId);
            }

            console.log('Final URL:', url.toString());

            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    renderActivities(data);
                    renderPagination(data);
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    activityList.innerHTML = `<div class="alert alert-danger">Erreur de chargement: ${error.message}</div>`;
                });
        }

        // Render activities list with hierarchy support
        function renderActivities(data) {
            console.log('Rendering activities:', data);

            if (!data || !data.data) {
                console.error('Invalid data structure:', data);
                activityList.innerHTML = `<div class="alert alert-danger">Structure de données invalide</div>`;
                return;
            }

            if (data.data.length === 0) {
                activityList.innerHTML = `<div class="alert alert-info">${data.message || 'Aucune activité trouvée'}</div>`;
                return;
            }

            activityList.innerHTML = '';
            console.log('Processing', data.data.length, 'activities');

            data.data.forEach((activity, index) => {
                console.log('Processing activity', index, ':', activity);

                const isSelected = selectedActivity && selectedActivity.id === activity.id;
                const item = document.createElement('div');
                item.className = `list-group-item list-group-item-action d-flex justify-content-between align-items-center ${isSelected ? 'active' : ''}`;
                item.dataset.id = activity.id;

                // Create container for activity info and expand/collapse button
                const leftSection = document.createElement('div');
                leftSection.className = 'd-flex align-items-center flex-grow-1';

                // Add expand/collapse button for items with children
                if (activity.has_children) {
                    const expandBtn = document.createElement('button');
                    expandBtn.className = 'btn btn-sm btn-outline-secondary me-2';
                    expandBtn.innerHTML = '<i class="bi bi-plus-circle"></i>';
                    expandBtn.style.width = '30px';
                    expandBtn.setAttribute('title', 'Afficher les sous-activités');
                    expandBtn.onclick = function(e) {
                        e.stopPropagation();
                        // Navigate to children
                        currentParentId = activity.id;
                        updateBreadcrumb(activity);
                        loadActivities(1, 'all', activity.id);
                    };
                    leftSection.appendChild(expandBtn);
                } else {
                    // Spacer for alignment
                    const spacer = document.createElement('div');
                    spacer.style.width = '30px';
                    spacer.className = 'me-2';
                    leftSection.appendChild(spacer);
                }

                // Activity info
                const activityInfo = document.createElement('div');
                activityInfo.className = 'activity-info';
                activityInfo.innerHTML = `
                    <strong>${activity.code} - ${activity.name}</strong>
                    ${activity.observation ? `<small class="text-muted d-block">${activity.observation}</small>` : ''}
                `;

                // Make the activity info clickable to select the activity
                activityInfo.style.cursor = 'pointer';
                activityInfo.onclick = function() {
                    selectActivity(activity, item);
                };

                leftSection.appendChild(activityInfo);
                item.appendChild(leftSection);

                // Add select button
                const rightSection = document.createElement('div');
                const selectBtn = document.createElement('button');
                selectBtn.className = `btn btn-sm ${isSelected ? 'btn-danger' : 'btn-primary'}`;
                selectBtn.innerHTML = isSelected ? 'Désélectionner' : 'Sélectionner';
                selectBtn.onclick = function(e) {
                    e.stopPropagation();
                    selectActivity(activity, item);
                };
                rightSection.appendChild(selectBtn);
                item.appendChild(rightSection);

                activityList.appendChild(item);
            });

            console.log('Rendered', data.data.length, 'activities to DOM');
        }

        // Render pagination
        function renderPagination(data) {
            if (!data.pagination || data.pagination.total_pages <= 1) {
                activityPagination.innerHTML = '';
                return;
            }

            activityPagination.innerHTML = '';
            const nav = document.createElement('nav');
            const ul = document.createElement('ul');
            ul.className = 'pagination pagination-sm justify-content-center';

            // Previous button
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${data.pagination.current_page === 1 ? 'disabled' : ''}`;
            const prevLink = document.createElement('a');
            prevLink.className = 'page-link';
            prevLink.href = '#';
            prevLink.textContent = 'Précédent';
            if (data.pagination.current_page > 1) {
                prevLink.onclick = function(e) {
                    e.preventDefault();
                    loadActivities(data.pagination.current_page - 1, currentFilter, currentParentId);
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
                    loadActivities(i, currentFilter, currentParentId);
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
            nextLink.textContent = 'Suivant';
            if (data.pagination.current_page < data.pagination.total_pages) {
                nextLink.onclick = function(e) {
                    e.preventDefault();
                    loadActivities(data.pagination.current_page + 1, currentFilter, currentParentId);
                };
            }
            nextLi.appendChild(nextLink);
            ul.appendChild(nextLi);

            nav.appendChild(ul);
            activityPagination.appendChild(nav);
        }

        // Select an activity
        function selectActivity(activity, element) {
            // Remove active class from previously selected
            if (selectedActivity) {
                const previouslySelected = activityList.querySelector(`.list-group-item[data-id="${selectedActivity.id}"]`);
                if (previouslySelected) {
                    previouslySelected.classList.remove('active');
                    const btn = previouslySelected.querySelector('button');
                    if (btn) {
                        btn.className = 'btn btn-sm btn-primary';
                        btn.textContent = 'Sélectionner';
                    }
                }
            }

            // If selecting the same activity, deselect it
            if (selectedActivity && selectedActivity.id === activity.id) {
                selectedActivity = null;
                element.classList.remove('active');
                const btn = element.querySelector('button');
                if (btn) {
                    btn.className = 'btn btn-sm btn-primary';
                    btn.textContent = 'Sélectionner';
                }
            } else {
                // Select the new activity
                selectedActivity = activity;
                element.classList.add('active');
                const btn = element.querySelector('button');
                if (btn) {
                    btn.className = 'btn btn-sm btn-danger';
                    btn.textContent = 'Désélectionner';
                }
            }
        }

        // Save selected activity
        if (saveActivityBtn) {
            saveActivityBtn.addEventListener('click', function() {
                if (selectedActivity) {
                    // Update the hidden input and display field on the main form
                    document.getElementById('activity-id').value = selectedActivity.id;
                    document.getElementById('selected-activity-display').value = `${selectedActivity.code} - ${selectedActivity.name}`;

                    // Close the modal
                    if (typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(activityModal);
                        if (modal) {
                            modal.hide();
                        }
                    }
                } else {
                    alert('Veuillez sélectionner une activité.');
                }
            });
        }

        // Activity search functionality
        if (activitySearch) {
            activitySearch.addEventListener('input', debounce(function() {
                if (this.value.trim().length > 0) {
                    // Reset parent ID when searching
                    resetBreadcrumb();
                }
                loadActivities(1, currentFilter, currentParentId);
            }, 500));
        }

        // Clear search button
        if (activitySearchClear) {
            activitySearchClear.addEventListener('click', function() {
                activitySearch.value = '';
                loadActivities(1, currentFilter, currentParentId);
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
    });
</script>

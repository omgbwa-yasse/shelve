@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Description</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('records.store') }}" method="POST">
            @csrf

            @if (!empty($record))
                <input type="hidden" name="parent_id" value="{{$record->id}}">
            @endif
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="identification-tab" data-toggle="tab" href="#identification" role="tab" aria-controls="identification" aria-selected="true">Identification</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contexte-tab" data-toggle="tab" href="#contexte" role="tab" aria-controls="contexte" aria-selected="false">Contexte</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contenu-tab" data-toggle="tab" href="#contenu" role="tab" aria-controls="contenu" aria-selected="false">Contenu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="condition-tab" data-toggle="tab" href="#condition" role="tab" aria-controls="condition" aria-selected="false">Condition d'accès</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sources-tab" data-toggle="tab" href="#sources" role="tab" aria-controls="sources" aria-selected="false">Sources complémentaires</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">Notes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="controle-tab" data-toggle="tab" href="#controle" role="tab" aria-controls="controle" aria-selected="false">Contrôle de description</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="indexation-tab" data-toggle="tab" href="#indexation" role="tab" aria-controls="indexation" aria-selected="false">Indexation</a>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="identification" role="tabpanel" aria-labelledby="identification-tab">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="level_id" class="form-label">Level</label>
                            <select name="level_id" id="level_id" class="form-select" required>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="support_id" class="form-label">Support</label>
                            <select name="support_id" id="support_id" class="form-select" required>
                                @foreach ($supports as $support)
                                    <option value="{{ $support->id }}">{{ $support->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" name="code" id="code" class="form-control" required maxlength="10">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <textarea name="name" id="name" class="form-control" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_start" class="form-label">Date Start</label>
                            <input type="text" name="date_start" id="date_start" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_end" class="form-label">Date End</label>
                            <input type="text" name="date_end" id="date_end" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_exact" class="form-label">Date Exact</label>
                            <input type="date" name="date_exact" id="date_exact" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="width" class="form-label">Width</label>
                            <input type="number" name="width" id="width" class="form-control" step="0.01" min="0" max="9999999999.99">
                        </div>
                        <div class="col-md-10 mb-3">
                            <label for="width_description" class="form-label">Width Description</label>
                            <input type="text" name="width_description" id="width_description" class="form-control" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="contexte" role="tabpanel" aria-labelledby="contexte-tab">
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="author" class="form-label">Producteurs</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="selected-authors-display" readonly>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authorModal">
                                    Sélectionner
                                </button>
                            </div>
                            <input type="hidden" name="author_ids[]" id="author-ids">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="biographical_history" class="form-label">Biographical History</label>
                        <textarea name="biographical_history" id="biographical_history" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="archival_history" class="form-label">Archival History</label>
                        <textarea name="archival_history" id="archival_history" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="acquisition_source" class="form-label">Acquisition Source</label>
                        <textarea name="acquisition_source" id="acquisition_source" class="form-control"></textarea>
                    </div>
                </div>
                <!-- Onglet "contenu" -->
                <div class="tab-pane fade" id="contenu" role="tabpanel" aria-labelledby="contenu-tab">
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea name="content" id="content" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="appraisal" class="form-label">Appraisal</label>
                        <textarea name="appraisal" id="appraisal" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="accrual" class="form-label">Accrual</label>
                        <textarea name="accrual" id="accrual" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="arrangement" class="form-label">Arrangement</label>
                        <textarea name="arrangement" id="arrangement" class="form-control"></textarea>
                    </div>
                </div>

                <!-- Onglet "condition" -->
                <div class="tab-pane fade" id="condition" role="tabpanel" aria-labelledby="condition-tab">
                    <div class="mb-3">
                        <label for="access_conditions" class="form-label">Access Conditions</label>
                        <input type="text" name="access_conditions" id="access_conditions" class="form-control" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="reproduction_conditions" class="form-label">Reproduction Conditions</label>
                        <input type="text" name="reproduction_conditions" id="reproduction_conditions" class="form-control" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="language_material" class="form-label">Language Material</label>
                        <input type="text" name="language_material" id="language_material" class="form-control" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="characteristic" class="form-label">Characteristic</label>
                        <input type="text" name="characteristic" id="characteristic" class="form-control" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="finding_aids" class="form-label">Finding Aids</label>
                        <input type="text" name="finding_aids" id="finding_aids" class="form-control" maxlength="100">
                    </div>
                </div>

                <!-- Onglet "sources" -->
                <div class="tab-pane fade" id="sources" role="tabpanel" aria-labelledby="sources-tab">
                    <div class="mb-3">
                        <label for="location_original" class="form-label">Location Original</label>
                        <input type="text" name="location_original" id="location_original" class="form-control" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="location_copy" class="form-label">Location Copy</label>
                        <input type="text" name="location_copy" id="location_copy" class="form-control" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="related_unit" class="form-label">Related Unit</label>
                        <input type="text" name="related_unit" id="related_unit" class="form-control" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="publication_note" class="form-label">Publication Note</label>
                        <textarea name="publication_note" id="publication_note" class="form-control"></textarea>
                    </div>
                </div>

                <!-- Onglet "notes" -->
                <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" class="form-control"></textarea>
                    </div>
                </div>

                <!-- Onglet "controle" -->
                <div class="tab-pane fade" id="controle" role="tabpanel" aria-labelledby="controle-tab">
                    <div class="mb-3">
                        <label for="archivist_note" class="form-label">Archivist Note</label>
                        <textarea name="archivist_note" id="archivist_note" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rule_convention" class="form-label">Rule Convention</label>
                        <input type="text" name="rule_convention" id="rule_convention" class="form-control" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="status_id" class="form-label">Status</label>
                        <select name="status_id" id="status_id" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Onglet "indexation" -->
                <div class="tab-pane fade" id="indexation" role="tabpanel" aria-labelledby="indexation-tab">
                    <div class="mb-3">
                        <label for="term_id" class="form-label">Thésaurus</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="selected-terms-display" readonly>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#termModal">
                                Sélectionner
                            </button>
                        </div>
                        <input type="hidden" name="term_ids[]" id="term-ids">
                    </div>


                    <div class="mb-3">
                        <label for="activity_id" class="form-label">Activités</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="selected-activity-display" readonly>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal">
                                Sélectionner
                            </button>
                        </div>
                        <input type="hidden" name="activity_id" id="activity-id">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>




    <div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authorModalLabel">Sélectionner les producteurs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="author-search" class="form-control mb-3" placeholder="Rechercher un producteur">
                    <div id="author-list" class="list-group">
                        @foreach ($authors as $author)
                            <a href="#" class="list-group-item list-group-item-action" data-id="{{ $author->id }}">
                                {{ $author->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="save-authors">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour le thésaurus -->
    <div class="modal fade" id="termModal" tabindex="-1" aria-labelledby="termModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termModalLabel">Sélectionner les termes du thésaurus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="term-search" class="form-control mb-3" placeholder="Rechercher un terme">
                    <div id="term-list" class="list-group">
                        @foreach ($terms as $term)
                            <a href="#" class="list-group-item list-group-item-action" data-id="{{ $term->id }}">
                                {{ $term->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="save-terms">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les activités -->
    <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="activityModalLabel">Sélectionner une activité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="activity-search" class="form-control mb-3" placeholder="Rechercher une activité">
                    <div id="activity-list" class="list-group">
                        @foreach ($activities as $activity)
                            <a href="#" class="list-group-item list-group-item-action" data-id="{{ $activity->id }}">
                                {{ $activity->code }} - {{ $activity->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="save-activity">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Ajoutez le code JavaScript pour gérer les champs dynamiques, comme dans le formulaire de création
        const authors = @json($authors);

        document.getElementById('author').addEventListener('input', function () {
            let query = this.value.toLowerCase();
            let suggestions = document.getElementById('suggestions');
            suggestions.innerHTML = '';

            if (query.length >= 2) {
                let filteredProducers = authors.filter(author => author.name.toLowerCase().includes(query));
                filteredProducers.forEach(author => {
                    let item = document.createElement('a');
                    item.classList.add('list-group-item', 'list-group-item-action');
                    item.textContent = author.name;
                    item.onclick = function () {
                        addProducer(author);
                    };
                    suggestions.appendChild(item);
                });
            }
        });

        function addProducer(author) {
            let selectedProducers = document.getElementById('selected-authors');
            let authorItem = document.createElement('div');
            authorItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

            let authorName = document.createElement('span');
            authorName.textContent = author.name;

            let removeButton = document.createElement('button');
            removeButton.classList.add('btn', 'btn-sm', 'btn-danger');
            removeButton.textContent = 'Supprimer';
            removeButton.onclick = function () {
                authorItem.remove();
            };

            authorItem.appendChild(authorName);
            authorItem.appendChild(removeButton);
            selectedProducers.appendChild(authorItem);
            document.getElementById('suggestions').innerHTML = '';
            document.getElementById('author').value = '';

            // Ajouter l'ID de l'auteur au champ caché author_ids[]
            let authorIdsInput = document.getElementById('author-ids');
            authorIdsInput.value += author.id + ',';
        }

        const terms = @json($terms);

        document.getElementById('term_id').addEventListener('change', function () {
            let selectedOptions = Array.from(this.selectedOptions);
            selectedOptions.forEach(option => {
                addTerm(option.text, option.value);
            });
            this.selectedOptions = [];
        });

        function addTerm(termName, termId) {
            let selectedTerms = document.getElementById('selected-terms');
            let termItem = document.createElement('div');
            termItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

            let termNameSpan = document.createElement('span');
            termNameSpan.textContent = termName;

            let removeButton = document.createElement('button');
            removeButton.classList.add('btn', 'btn-sm', 'btn-danger');
            removeButton.textContent = 'Supprimer';
            removeButton.onclick = function () {
                termItem.remove();
            };

            termItem.appendChild(termNameSpan);
            termItem.appendChild(removeButton);
            selectedTerms.appendChild(termItem);

            let termIdsInput = document.getElementById('term-ids');
            termIdsInput.value += termId + ',';
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction pour filtrer les éléments d'une liste
            function filterList(searchInput, listItems) {
                const filter = searchInput.value.toLowerCase();
                listItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filter) ? '' : 'none';
                });
            }

            // Gestionnaire pour le modal des producteurs
            const authorModal = document.getElementById('authorModal');
            const authorSearch = document.getElementById('author-search');
            const authorList = document.getElementById('author-list');
            const authorItems = authorList.querySelectorAll('.list-group-item');
            const saveAuthors = document.getElementById('save-authors');
            const selectedAuthorsDisplay = document.getElementById('selected-authors-display');
            const authorIds = document.getElementById('author-ids');

            let selectedAuthors = new Set();

            authorSearch.addEventListener('input', () => filterList(authorSearch, authorItems));

            authorItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    item.classList.toggle('active');
                    const authorId = item.dataset.id;
                    if (selectedAuthors.has(authorId)) {
                        selectedAuthors.delete(authorId);
                    } else {
                        selectedAuthors.add(authorId);
                    }
                });
            });

            saveAuthors.addEventListener('click', () => {
                const selectedAuthorNames = Array.from(authorItems)
                    .filter(item => item.classList.contains('active'))
                    .map(item => item.textContent.trim());
                selectedAuthorsDisplay.value = selectedAuthorNames.join(', ');
                authorIds.value = Array.from(selectedAuthors).join(',');
                bootstrap.Modal.getInstance(authorModal).hide();
            });

            // Gestionnaire pour le modal du thésaurus (similaire aux producteurs)
            const termModal = document.getElementById('termModal');
            const termSearch = document.getElementById('term-search');
            const termList = document.getElementById('term-list');
            const termItems = termList.querySelectorAll('.list-group-item');
            const saveTerms = document.getElementById('save-terms');
            const selectedTermsDisplay = document.getElementById('selected-terms-display');
            const termIds = document.getElementById('term-ids');

            let selectedTerms = new Set();

            termSearch.addEventListener('input', () => filterList(termSearch, termItems));

            termItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    item.classList.toggle('active');
                    const termId = item.dataset.id;
                    if (selectedTerms.has(termId)) {
                        selectedTerms.delete(termId);
                    } else {
                        selectedTerms.add(termId);
                    }
                });
            });

            saveTerms.addEventListener('click', () => {
                const selectedTermNames = Array.from(termItems)
                    .filter(item => item.classList.contains('active'))
                    .map(item => item.textContent.trim());
                selectedTermsDisplay.value = selectedTermNames.join(', ');
                termIds.value = Array.from(selectedTerms).join(',');
                bootstrap.Modal.getInstance(termModal).hide();
            });

            // Gestionnaire pour le modal des activités
            const activityModal = document.getElementById('activityModal');
            const activitySearch = document.getElementById('activity-search');
            const activityList = document.getElementById('activity-list');
            const activityItems = activityList.querySelectorAll('.list-group-item');
            const saveActivity = document.getElementById('save-activity');
            const selectedActivityDisplay = document.getElementById('selected-activity-display');
            const activityId = document.getElementById('activity-id');

            activitySearch.addEventListener('input', () => filterList(activitySearch, activityItems));

            activityItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    activityItems.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                });
            });

            saveActivity.addEventListener('click', () => {
                const selectedActivity = activityList.querySelector('.list-group-item.active');
                if (selectedActivity) {
                    selectedActivityDisplay.value = selectedActivity.textContent.trim();
                    activityId.value = selectedActivity.dataset.id;
                }
                bootstrap.Modal.getInstance(activityModal).hide();
            });
        });
    </script>

@endsection

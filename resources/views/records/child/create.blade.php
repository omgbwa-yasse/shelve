@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Description </h1>
        <form action="{{ route('records.store') }}" method="POST">
            @csrf
            <input type="hidden" name="parent_id" value="{{$parent->id}}">

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
                <div class="tab-pane fade show active " id="identification" role="tabpanel" aria-labelledby="identification-tab">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="level_id" class="form-label">Level</label>
                            <select name="level_id" id="level_id" class="form-select" required>
                                <option value="" disabled selected>Enter the level</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="support_id" class="form-label">Support</label>
                            <select name="support_id" id="support_id" class="form-select" required>
                                <option value="" disabled selected>Enter the support</option>
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
                            <label for="author" class="form-label">Producteur</label>
                            <input type="text" class="form-control" id="author" autocomplete="off">
                            <div id="suggestions" class="list-group mt-2"></div>
                        </div>
                        <div id="selected-authors" class="mt-3"></div>
                        <input type="hidden" name="author_ids[]" id="author-ids">
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
                <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" class="form-control"></textarea>
                    </div>
                </div>
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
                            <option value="" disabled selected>Enter the status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="tab-pane fade" id="indexation" role="tabpanel" aria-labelledby="indexation-tab">

                    <div class="mt-3">
                        <label for="term_search" class="form-label">Rechercher un terme</label>
                        <input type="text" id="term_search" class="form-control" placeholder="Taper pour rechercher...">
                    </div>

                    <div class="mt-3">
                        <label for="term_id" class="form-label">Thésaurus</label>
                        <select name="term_id" id="term_id" class="form-select" multiple required>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="selected-terms" class="mt-3"></div>
                    <button type="button" class="btn btn-secondary mt-3" onclick="clearTerms()">Vider les termes</button>
                    <input type="hidden" name="term_ids[]" id="term-ids">

                    <div class="mb-3">
                        <label for="activity_id" class="form-label"> Activités </label>
                        <div class="select-with-search">
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control search-input" placeholder="Search activity...">
                            </div>
                            <select name="activity_id" id="activity_id" class="form-select" required>
                                <option value="" disabled selected>Enter the activity</option>
                                @foreach ($activities as $activity)
                                    <option value="{{ $activity->id }}">{{ $activity->code }} - {{ $activity->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>

    <style>
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
        const authors = @json($authors);
        document.getElementById('term_id').addEventListener('change', function () {
            let selectedOptions = Array.from(this.selectedOptions);
            selectedOptions.forEach(option => {
                addTerm(option.text, option.value);
            });
            this.selectedOptions = [];
        });

        document.getElementById('term_search').addEventListener('input', function () {
            let searchQuery = this.value.toLowerCase();
            let termOptions = document.getElementById('term_id').options;
            for (let i = 0; i < termOptions.length; i++) {
                let option = termOptions[i];
                option.style.display = option.text.toLowerCase().includes(searchQuery) ? 'block' : 'none';
            }
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

        function clearTerms() {
            document.getElementById('selected-terms').innerHTML = '';
            document.getElementById('term-ids').value = '';
        }

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

        function createProducer() {
            // Logique pour créer un nouveau producteur
            alert('Créer un nouveau producteur');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selectWithSearchElements = document.querySelectorAll('.select-with-search');

            selectWithSearchElements.forEach(selectWithSearch => {
                const searchInput = selectWithSearch.querySelector('.search-input');
                const select = selectWithSearch.querySelector('select');
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

@endsection

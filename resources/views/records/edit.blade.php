@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ __('edit_description') }}</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('records.update', $record->id) }}" method="POST">
            @csrf
            @method('PUT')
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="identification-tab" data-toggle="tab" href="#identification" role="tab" aria-controls="identification" aria-selected="true">{{ __('identification') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contexte-tab" data-toggle="tab" href="#contexte" role="tab" aria-controls="contexte" aria-selected="false">{{ __('context') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contenu-tab" data-toggle="tab" href="#contenu" role="tab" aria-controls="contenu" aria-selected="false">{{ __('content') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="condition-tab" data-toggle="tab" href="#condition" role="tab" aria-controls="condition" aria-selected="false">{{ __('access_conditions') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sources-tab" data-toggle="tab" href="#sources" role="tab" aria-controls="sources" aria-selected="false">{{ __('allied_materials_area') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">{{ __('notes') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="controle-tab" data-toggle="tab" href="#controle" role="tab" aria-controls="controle" aria-selected="false">{{ __('description_control') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="indexation-tab" data-toggle="tab" href="#indexation" role="tab" aria-controls="indexation" aria-selected="false">{{ __('indexing') }}</a>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active " id="identification" role="tabpanel" aria-labelledby="identification-tab">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="level_id" class="form-label">{{ __('level') }}</label>
                            <select name="level_id" id="level_id" class="form-select" required>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" {{ $record->level_id == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="support_id" class="form-label">{{ __('support') }}</label>
                            <select name="support_id" id="support_id" class="form-select" required>
                                @foreach ($supports as $support)
                                    <option value="{{ $support->id }}" {{ $record->support_id == $support->id ? 'selected' : '' }}>{{ $support->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" name="code" id="code" class="form-control" required maxlength="10" value="{{ $record->code }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <textarea name="name" id="name" class="form-control" required>{{ isset($suggestedTitle) ? $suggestedTitle : $record->name }}</textarea>
                        @if(isset($suggestedTitle))
                            <div class="alert alert-info mt-2">
                                <i class="bi bi-info-circle me-2"></i>{{ __('suggested_title_applied') ?? 'Un titre reformulé a été appliqué. Vous pouvez le modifier si nécessaire.' }}
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_start" class="form-label">Date Start</label>
                            <input type="text" name="date_start" id="date_start" class="form-control" maxlength="10" value="{{ $record->date_start }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_end" class="form-label">Date End</label>
                            <input type="text" name="date_end" id="date_end" class="form-control" maxlength="10" value="{{ $record->date_end }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_exact" class="form-label">Date Exact</label>
                            <input type="date" name="date_exact" id="date_exact" class="form-control" value="{{ $record->date_exact }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="width" class="form-label">Width</label>
                            <input type="number" name="width" id="width" class="form-control" step="0.01" min="0" max="9999999999.99" value="{{ $record->width }}">
                        </div>
                        <div class="col-md-10 mb-3">
                            <label for="width_description" class="form-label">Width Description</label>
                            <input type="text" name="width_description" id="width_description" class="form-control" maxlength="100" value="{{ $record->width_description }}">
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="contexte" role="tabpanel" aria-labelledby="contexte-tab">
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="author" class="form-label">Producteur</label>
                            <input type="text" class="form-control" id="author" autocomplete="off" value="{{ old('author') }}">
                            <div id="suggestions" class="list-group mt-2"></div>
                        </div>
                        <div id="selected-authors" class="mt-3">
                            @foreach($record->authors as $author)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $author->name }}</span>
                                    <button type="button" class="btn btn-sm btn-danger remove-author" data-author-id="{{ $author->id }}">Supprimer</button>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="author_ids[]" id="author-ids" >
                    </div>
                    <div class="mb-3">
                        <label for="biographical_history" class="form-label">Biographical History</label>
                        <textarea name="biographical_history" id="biographical_history" class="form-control">{{ $record->biographical_history }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="archival_history" class="form-label">Archival History</label>
                        <textarea name="archival_history" id="archival_history" class="form-control">{{ $record->archival_history }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="acquisition_source" class="form-label">Acquisition Source</label>
                        <textarea name="acquisition_source" id="acquisition_source" class="form-control">{{ $record->acquisition_source }}</textarea>
                    </div>
                </div>
                <!-- Onglet "contenu" -->
                <div class="tab-pane fade" id="contenu" role="tabpanel" aria-labelledby="contenu-tab">
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea name="content" id="content" class="form-control">{{ $record->content }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="appraisal" class="form-label">Appraisal</label>
                        <textarea name="appraisal" id="appraisal" class="form-control">{{ $record->appraisal }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="accrual" class="form-label">Accrual</label>
                        <textarea name="accrual" id="accrual" class="form-control">{{ $record->accrual }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="arrangement" class="form-label">Arrangement</label>
                        <textarea name="arrangement" id="arrangement" class="form-control">{{ $record->arrangement }}</textarea>
                    </div>
                </div>

                <!-- Onglet "condition" -->
                <div class="tab-pane fade" id="condition" role="tabpanel" aria-labelledby="condition-tab">
                    <div class="mb-3">
                        <label for="access_conditions" class="form-label">Access Conditions</label>
                        <input type="text" name="access_conditions" id="access_conditions" class="form-control" maxlength="50" value="{{ $record->access_conditions }}">
                    </div>
                    <div class="mb-3">
                        <label for="reproduction_conditions" class="form-label">Reproduction Conditions</label>
                        <input type="text" name="reproduction_conditions" id="reproduction_conditions" class="form-control" maxlength="50" value="{{ $record->reproduction_conditions }}">
                    </div>
                    <div class="mb-3">
                        <label for="language_material" class="form-label">Language Material</label>
                        <input type="text" name="language_material" id="language_material" class="form-control" maxlength="50" value="{{ $record->language_material }}">
                    </div>
                    <div class="mb-3">
                        <label for="characteristic" class="form-label">Characteristic</label>
                        <input type="text" name="characteristic" id="characteristic" class="form-control" maxlength="100" value="{{ $record->characteristic }}">
                    </div>
                    <div class="mb-3">
                        <label for="finding_aids" class="form-label">Finding Aids</label>
                        <input type="text" name="finding_aids" id="finding_aids" class="form-control" maxlength="100" value="{{ $record->finding_aids }}">
                    </div>
                </div>

                <!-- Onglet "sources" -->
                <div class="tab-pane fade" id="sources" role="tabpanel" aria-labelledby="sources-tab">
                    <div class="mb-3">
                        <label for="location_original" class="form-label">Location Original</label>
                        <input type="text" name="location_original" id="location_original" class="form-control" maxlength="100" value="{{ $record->location_original }}">
                    </div>
                    <div class="mb-3">
                        <label for="location_copy" class="form-label">Location Copy</label>
                        <input type="text" name="location_copy" id="location_copy" class="form-control" maxlength="100" value="{{ $record->location_copy }}">
                    </div>
                    <div class="mb-3">
                        <label for="related_unit" class="form-label">Related Unit</label>
                        <input type="text" name="related_unit" id="related_unit" class="form-control" maxlength="100" value="{{ $record->related_unit }}">
                    </div>
                    <div class="mb-3">
                        <label for="publication_note" class="form-label">Publication Note</label>
                        <textarea name="publication_note" id="publication_note" class="form-control">{{ $record->publication_note }}</textarea>
                    </div>
                </div>

                <!-- Onglet "notes" -->
                <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" class="form-control">{{ $record->note }}</textarea>
                    </div>
                </div>

                <!-- Onglet "controle" -->
                <div class="tab-pane fade" id="controle" role="tabpanel" aria-labelledby="controle-tab">
                    <div class="mb-3">
                        <label for="archivist_note" class="form-label">Archivist Note</label>
                        <textarea name="archivist_note" id="archivist_note" class="form-control">{{ $record->archivist_note }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rule_convention" class="form-label">Rule Convention</label>
                        <input type="text" name="rule_convention" id="rule_convention" class="form-control" maxlength="100" value="{{ $record->rule_convention }}">
                    </div>
                    <div class="mb-3">
                        <label for="status_id" class="form-label">Status</label>
                        <select name="status_id" id="status_id" class="form-select" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" {{ $record->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Onglet "indexation" -->
                <div class="tab-pane fade" id="indexation" role="tabpanel" aria-labelledby="indexation-tab">
                    <div class="mb-3">
                        <label for="thesaurus-search" class="form-label">Thésaurus</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="thesaurus-search" placeholder="Rechercher dans le thésaurus..." autocomplete="off">
                            <div id="thesaurus-suggestions" class="position-absolute w-100 bg-white border border-top-0 shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;">
                                <!-- Les suggestions apparaîtront ici -->
                            </div>
                        </div>
                        <small class="text-muted">Tapez au moins 3 caractères pour rechercher. Cliquez sur un terme pour l'ajouter.</small>
                    </div>

                    <!-- Zone d'affichage des termes sélectionnés -->
                    <div id="selected-terms-container" class="mt-3">
                        @foreach($record->thesaurusConcepts as $concept)
                            <div class="selected-term badge bg-primary me-2 mb-2 p-2" data-id="{{ $concept->id }}">
                                <span>{{ $concept->preferred_label }}</span>
                                <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.8em;" onclick="removeTerm(this)"></button>
                            </div>
                        @endforeach
                    </div>

                    <!-- Champs cachés pour stocker les ID des termes sélectionnés -->
                    <div id="term-ids-container">
                        @foreach($record->thesaurusConcepts as $concept)
                            <input type="hidden" name="term_ids[]" value="{{ $concept->id }}">
                        @endforeach
                    </div>


                    <div class="mb-3">
                        <label for="activity_id" class="form-label">Activités</label>
                        <select name="activity_id" id="activity_id" class="form-select" required>
                            @foreach ($activities as $activity)
                                <option value="{{ $activity->id }}" {{ $record->activity_id == $activity->id ? 'selected' : '' }}>{{ $activity->code }} - {{ $activity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
            <button type="submit" class="btn btn-primary">save</button>
        </form>
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

        // Supprimer un auteur existant
        document.querySelectorAll('.remove-author').forEach(function(button) {
            button.addEventListener('click', function() {
                let authorId = this.dataset.authorId;
                let authorIdsInput = document.getElementById('author-ids');
                authorIdsInput.value = authorIdsInput.value.replace(authorId + ',', '');
                this.parentElement.remove();
            });
        });

        // === AJAX Thesaurus Search Implementation ===
        let thesaurusTimeout;
        const thesaurusSearchInput = document.getElementById('thesaurus-search');
        const thesaurusSuggestions = document.getElementById('thesaurus-suggestions');

        if (thesaurusSearchInput) {
            thesaurusSearchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(thesaurusTimeout);

                if (query.length < 3) {
                    thesaurusSuggestions.style.display = 'none';
                    return;
                }

                thesaurusTimeout = setTimeout(() => {
                    searchThesaurus(query);
                }, 300);
            });

            // Masquer les suggestions quand on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!thesaurusSearchInput.contains(e.target) && !thesaurusSuggestions.contains(e.target)) {
                    thesaurusSuggestions.style.display = 'none';
                }
            });
        }

        function searchThesaurus(query) {
            fetch(`{{ route('records.terms.autocomplete') }}?q=${encodeURIComponent(query)}&limit=10`)
                .then(response => response.json())
                .then(data => {
                    displayThesaurusSuggestions(data);
                })
                .catch(error => {
                    console.error('Erreur lors de la recherche dans le thésaurus:', error);
                    thesaurusSuggestions.style.display = 'none';
                });
        }

        function displayThesaurusSuggestions(suggestions) {
            thesaurusSuggestions.innerHTML = '';

            if (suggestions.length === 0) {
                thesaurusSuggestions.innerHTML = '<div class="p-2 text-muted">Aucun résultat trouvé</div>';
                thesaurusSuggestions.style.display = 'block';
                return;
            }

            suggestions.forEach(suggestion => {
                const div = document.createElement('div');
                div.className = 'p-2 cursor-pointer border-bottom';
                div.style.cursor = 'pointer';
                div.innerHTML = `
                    <div class="fw-bold">${suggestion.text}</div>
                    <small class="text-muted">${suggestion.scheme || 'Thésaurus'}</small>
                `;

                div.addEventListener('click', () => {
                    addTermToSelection(suggestion);
                    thesaurusSearchInput.value = '';
                    thesaurusSuggestions.style.display = 'none';
                });

                div.addEventListener('mouseover', () => {
                    div.style.backgroundColor = '#f8f9fa';
                });

                div.addEventListener('mouseout', () => {
                    div.style.backgroundColor = '';
                });

                thesaurusSuggestions.appendChild(div);
            });

            thesaurusSuggestions.style.display = 'block';
        }

        function addTermToSelection(term) {
            const container = document.getElementById('selected-terms-container');
            const termIdsInput = document.getElementById('term-ids');

            // Vérifier si le terme n'est pas déjà sélectionné
            const existingTerms = container.querySelectorAll('.selected-term');
            for (let existingTerm of existingTerms) {
                if (existingTerm.dataset.id === term.id.toString()) {
                    return; // Terme déjà sélectionné
                }
            }

            // Créer l'élément du terme
            const termElement = document.createElement('div');
            termElement.className = 'selected-term badge bg-primary me-2 mb-2 p-2';
            termElement.dataset.id = term.id;
            termElement.innerHTML = `
                <span>${term.text}</span>
                <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.8em;" onclick="removeTerm(this)"></button>
            `;

            container.appendChild(termElement);
            updateTermIds();
        }

        function removeTerm(button) {
            button.closest('.selected-term').remove();
            updateTermIds();
        }

        function updateTermIds() {
            const container = document.getElementById('selected-terms-container');
            const terms = container.querySelectorAll('.selected-term');
            const termIdsContainer = document.getElementById('term-ids-container');

            // Vider les champs cachés existants
            termIdsContainer.innerHTML = '';

            // Créer un champ caché pour chaque terme sélectionné
            terms.forEach(term => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'term_ids[]';
                hiddenInput.value = term.dataset.id;
                termIdsContainer.appendChild(hiddenInput);
            });
        }
    </script>

@endsection

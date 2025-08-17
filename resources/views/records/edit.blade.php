@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ session('records.back_url', route('records.index')) }}" class="btn btn-outline-secondary btn-sm" title="{{ __('back') }}">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="mb-0">{{ __('edit_description') }}</h1>
            </div>

            {{-- Le mode IA est déterminé par les paramètres globaux (Admin MCP) --}}
        </div>

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
                    <a class="nav-link active" id="identification-tab" data-bs-toggle="tab" href="#identification" role="tab" aria-controls="identification" aria-selected="true">{{ __('identification') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contexte-tab" data-bs-toggle="tab" href="#contexte" role="tab" aria-controls="contexte" aria-selected="false">{{ __('context') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contenu-tab" data-bs-toggle="tab" href="#contenu" role="tab" aria-controls="contenu" aria-selected="false">{{ __('content') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="condition-tab" data-bs-toggle="tab" href="#condition" role="tab" aria-controls="condition" aria-selected="false">{{ __('access_conditions') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sources-tab" data-bs-toggle="tab" href="#sources" role="tab" aria-controls="sources" aria-selected="false">{{ __('allied_materials_area') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notes-tab" data-bs-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">{{ __('notes') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="controle-tab" data-bs-toggle="tab" href="#controle" role="tab" aria-controls="controle" aria-selected="false">{{ __('description_control') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="indexation-tab" data-bs-toggle="tab" href="#indexation" role="tab" aria-controls="indexation" aria-selected="false">{{ __('indexing') }}</a>
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
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="name" class="form-label mb-0">Name</label>
                        </div>
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
                    <div class="mb-3">
                        <label for="container_ids" class="form-label">{{ __('containers') }}</label>
                        <select id="container_ids" name="container_ids[]" class="form-select" multiple>
                            @php $currentContainers = $record->containers->pluck('id')->toArray(); @endphp
                            @foreach($containers as $c)
                                <option value="{{ $c->id }}" {{ in_array($c->id, $currentContainers) ? 'selected' : '' }}>{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('you_can_select_multiple') }}</small>
                    </div>
                </div>
                <div class="tab-pane fade" id="contexte" role="tabpanel" aria-labelledby="contexte-tab">
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="author" class="form-label">{{ __('producers') }} *</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authorModal">
                                    <i class="bi bi-plus-circle me-1"></i>{{ __('select') }}
                                </button>
                            </div>

                            <!-- Zone d'affichage des auteurs sélectionnés -->
                            <div id="selected-authors-container" class="mt-2">
                                @foreach($record->authors as $author)
                                    <div class="selected-author badge bg-primary me-2 mb-2 p-2" data-id="{{ $author->id }}">
                                        <span>{{ $author->name }}{{ $author->authorType ? ' (' . $author->authorType->name . ')' : '' }}</span>
                                        <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.8em;" onclick="removeAuthor(this)"></button>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Champs cachés pour stocker les ID des auteurs sélectionnés -->
                            <div id="author-ids-container">
                                @foreach($record->authors as $author)
                                    <input type="hidden" name="author_ids[]" value="{{ $author->id }}">
                                @endforeach
                            </div>
                        </div>
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
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="content" class="form-label mb-0">Content</label>
                        </div>
                        <textarea name="content" id="content" class="form-control" rows="6">{{ $record->content }}</textarea>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Le résumé sera généré selon l'élément 3.3.1 ISAD(G) - Portée et contenu
                        </small>
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

        // === Gestion des auteurs ===

        // Fonction pour ajouter un auteur à la sélection
        function addAuthorToSelection(author) {
            const container = document.getElementById('selected-authors-container');

            // Vérifier si l'auteur n'est pas déjà sélectionné
            const existingAuthors = container.querySelectorAll('.selected-author');
            for (let existingAuthor of existingAuthors) {
                if (existingAuthor.dataset.id === author.id.toString()) {
                    return; // Auteur déjà sélectionné
                }
            }

            // Créer l'élément de l'auteur
            const authorElement = document.createElement('div');
            authorElement.className = 'selected-author badge bg-primary me-2 mb-2 p-2';
            authorElement.dataset.id = author.id;
            authorElement.innerHTML = `
                <span>${author.name}${author.authorType ? ' (' + author.authorType.name + ')' : ''}</span>
                <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.8em;" onclick="removeAuthor(this)"></button>
            `;

            container.appendChild(authorElement);
            updateAuthorIds();
        }

        // Fonction pour supprimer un auteur
        function removeAuthor(button) {
            button.closest('.selected-author').remove();
            updateAuthorIds();
        }

        // Fonction pour mettre à jour les champs cachés des auteurs
        function updateAuthorIds() {
            const container = document.getElementById('selected-authors-container');
            const authors = container.querySelectorAll('.selected-author');
            const authorIdsContainer = document.getElementById('author-ids-container');

            // Vider les champs cachés existants
            authorIdsContainer.innerHTML = '';

            // Créer un champ caché pour chaque auteur sélectionné
            authors.forEach(author => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'author_ids[]';
                hiddenInput.value = author.dataset.id;
                authorIdsContainer.appendChild(hiddenInput);
            });
        }

        // Écouter l'événement authorsSelected du modal
        document.addEventListener('authorsSelected', function(e) {
            const selectedAuthors = e.detail.authors;
            selectedAuthors.forEach(author => {
                addAuthorToSelection(author);
            });
        });
    </script>

    <!-- Modals - inclus pour la gestion des auteurs et activités -->
    @include('records.partials.author_modal')
    @include('records.partials.activity_modal')

    <style>
        /* Style pour les auteurs sélectionnés */
        .selected-author {
            display: inline-flex;
            align-items: center;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin: 0.125rem;
            font-size: 0.875rem;
        }

        .selected-author .remove-author {
            background: none;
            border: none;
            color: #6c757d;
            font-weight: bold;
            margin-left: 0.5rem;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
        }

        .selected-author .remove-author:hover {
            color: #dc3545;
        }
    </style>

{{-- Scripts JavaScript consolidés --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // S'assurer que jQuery est disponible pour les scripts legacy
    if (typeof $ === 'undefined' && typeof window.jQuery !== 'undefined') {
        window.$ = window.jQuery;
    }

    // Initialiser les onglets Bootstrap 5
    function initializeTabs() {
        if (typeof bootstrap !== 'undefined') {
            // Initialiser tous les onglets
            const triggerTabList = document.querySelectorAll('#myTab a[data-bs-toggle="tab"]');
            triggerTabList.forEach(triggerEl => {
                const tabTrigger = new bootstrap.Tab(triggerEl);

                triggerEl.addEventListener('click', event => {
                    event.preventDefault();
                    tabTrigger.show();
                });
            });
        } else {
            console.warn('Bootstrap non disponible - les onglets pourraient ne pas fonctionner');
        }
    }

    // Attendre que Bootstrap soit disponible
    function initializeMcpButtons() {
        // Initialiser les tooltips seulement si Bootstrap est disponible
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            if (tooltipTriggerList.length > 0) {
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    try {
                        new bootstrap.Tooltip(tooltipTriggerEl);
                    } catch (e) {
                        console.warn('Erreur initialisation tooltip:', e);
                    }
                });
            }
        }

        // Gestionnaire pour les boutons d'action MCP (seulement s'ils existent)
        const mcpActionBtns = document.querySelectorAll('.mcp-action-btn');
        if (mcpActionBtns.length > 0) {
            mcpActionBtns.forEach(button => {
                button.addEventListener('click', handleMcpActionWithMode);
            });
        }

        // Mode IA global: plus de bascule côté vue
    }

    // Mode IA global: plus de bascule côté vue edit

    // Initialiser les onglets immédiatement
    initializeTabs();

    // Démarrer l'initialisation MCP après un délai pour s'assurer que tout est chargé
    setTimeout(initializeMcpButtons, 100);
});

// Mode IA global: plus de switch côté client

// Mode IA global: plus de switch côté client

// Gestionnaire principal pour les actions MCP - TOUJOURS en mode preview d'abord
function handleMcpActionWithMode(event) {
    event.preventDefault();

    const button = event.currentTarget;
    const action = button.dataset.action;
    const recordId = button.dataset.recordId;
    const apiPrefix = button.dataset.apiPrefix || '/api/mcp';

    if (!recordId) {
        showMcpNotification('Erreur: ID du record manquant', 'error');
        return;
    }

    // Désactiver le bouton pendant le traitement
    setButtonState(button, 'processing');

    // TOUJOURS utiliser le mode preview d'abord pour validation
    let endpoint, method = 'POST';

    switch(action) {
        case 'title':
        case 'title-preview':
            endpoint = `${apiPrefix}/records/${recordId}/title/preview`;
            break;
        case 'thesaurus':
        case 'thesaurus-suggest':
            endpoint = `${apiPrefix}/records/${recordId}/thesaurus/index`;
            break;
        case 'summary':
        case 'summary-preview':
            endpoint = `${apiPrefix}/records/${recordId}/summary/preview`;
            break;
        case 'all-preview':
            endpoint = `${apiPrefix}/records/${recordId}/preview`;
            break;
        case 'all-apply':
            endpoint = `${apiPrefix}/records/${recordId}/process`;
            break;
        default:
            setButtonState(button, 'error');
            showMcpNotification('Action inconnue: ' + action, 'error');
            return;
    }

    // Effectuer la requête
    fetch(endpoint, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            features: action.startsWith('all') ? ['title', 'thesaurus', 'summary'] : [action.split('-')[0]]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.message || 'Erreur inconnue');
        }

        setButtonState(button, 'success');

        // Message de succès personnalisé selon le mode
        const mode = apiPrefix.includes('mistral') ? 'Mistral' : 'MCP';
        showMcpNotification(`${mode}: ${data.message || 'Aperçu généré'}`, 'success');

        // Afficher les tokens utilisés si disponible (Mistral)
        if (data.tokens_used) {
            console.log(`Tokens utilisés (${mode}):`, data.tokens_used);
        }

        // TOUJOURS afficher l'aperçu pour validation
        showMcpPreviewWithValidation(data, mode, action, recordId, apiPrefix);
    })
    .catch(error => {
        setButtonState(button, 'error');
        const mode = apiPrefix.includes('mistral') ? 'Mistral' : 'MCP';
        showMcpNotification(`Erreur ${mode}: ${error.message}`, 'error');
        console.error(`Erreur ${mode}:`, error);
    });
}

// Gestion des états des boutons
function setButtonState(button, state) {
    button.classList.remove('mcp-processing', 'mcp-success', 'mcp-error');

    const existingSpinner = button.querySelector('.spinner-border');
    if (existingSpinner) {
        existingSpinner.remove();
    }

    switch(state) {
        case 'processing':
            button.classList.add('mcp-processing');
            button.disabled = true;
            button.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1" role="status"></span>');
            break;
        case 'success':
            button.classList.add('mcp-success');
            button.disabled = false;
            setTimeout(() => {
                button.classList.remove('mcp-success');
            }, 3000);
            break;
        case 'error':
            button.classList.add('mcp-error');
            button.disabled = false;
            setTimeout(() => {
                button.classList.remove('mcp-error');
            }, 5000);
            break;
        default:
            button.disabled = false;
    }
}

// Afficher les notifications
function showMcpNotification(message, type = 'info') {
    if (typeof bootstrap === 'undefined') {
        console.log(`${type.toUpperCase()}: ${message}`);
        return;
    }

    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';

    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${bgClass} text-white border-0">
                <i class="bi bi-robot me-2"></i>
                <strong class="me-auto">IA Processing</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    try {
        const toast = new bootstrap.Toast(document.getElementById(toastId));
        toast.show();

        document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    } catch (e) {
        console.warn('Erreur création toast:', e);
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// Créer le conteneur de toast
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

// Afficher l'aperçu avec validation obligatoire AVANT application
function showMcpPreviewWithValidation(data, mode, action, recordId, apiPrefix) {
    if (typeof bootstrap === 'undefined') {
        console.log('Bootstrap non disponible, affichage en console:', data);
        return;
    }

    let modal = document.getElementById('mcpPreviewModal');
    if (!modal) {
        modal = createPreviewModalWithValidation();
    }

    const modalTitle = modal.querySelector('.modal-title');
    modalTitle.innerHTML = `<i class="bi bi-exclamation-triangle text-warning me-2"></i>Validation requise - ${mode}`;

    const modalBody = modal.querySelector('.modal-body');
    let content = `
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Validation requise :</strong> Vérifiez les modifications avant de les appliquer au formulaire
        </div>
    `;

    // Formater l'aperçu selon le type d'action
    if (action.includes('title')) {
        content += formatTitlePreview(data);
    } else if (action.includes('thesaurus')) {
        content += formatThesaurusPreview(data);
    } else if (action.includes('summary')) {
        content += formatSummaryPreview(data);
    } else if (data.previews) {
        Object.entries(data.previews).forEach(([feature, preview]) => {
            content += formatPreviewContent(feature, preview);
        });
    }

    if (data.tokens_used) {
        content += `<div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Tokens utilisés :</strong> ${data.tokens_used}
        </div>`;
    }

    modalBody.innerHTML = content;

    // Stocker les données pour l'application
    modal.dataset.previewData = JSON.stringify(data);
    modal.dataset.mode = mode;
    modal.dataset.action = action;
    modal.dataset.recordId = recordId;
    modal.dataset.apiPrefix = apiPrefix;

    try {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } catch (e) {
        console.warn('Erreur création modal:', e);
        console.log('Aperçu des données:', data);
    }
}

// Formater l'aperçu spécifique pour le titre
function formatTitlePreview(data) {
    if (data.preview && data.preview.suggested_title) {
        const currentTitle = document.getElementById('name')?.value || 'Non défini';
        return `
            <div class="mb-3 border rounded p-3 bg-light">
                <h6 class="text-primary"><i class="bi bi-magic me-2"></i>Reformulation du titre</h6>
                <div class="row">
                    <div class="col-6">
                        <strong>Titre actuel :</strong><br>
                        <span class="text-muted">${currentTitle}</span>
                    </div>
                    <div class="col-6">
                        <strong>Titre suggéré :</strong><br>
                        <span class="text-success fw-bold">${data.preview.suggested_title}</span>
                    </div>
                </div>
            </div>
        `;
    }
    return '<div class="alert alert-warning">Aucune suggestion de titre reçue</div>';
}

// Formater l'aperçu spécifique pour le résumé
function formatSummaryPreview(data) {
    if (data.preview && data.preview.suggested_summary) {
        const currentContent = document.getElementById('content')?.value || 'Vide';
        return `
            <div class="mb-3 border rounded p-3 bg-light">
                <h6 class="text-primary"><i class="bi bi-file-text me-2"></i>Résumé ISAD(G) - Portée et contenu (3.3.1)</h6>
                <div class="row">
                    <div class="col-6">
                        <strong>Contenu actuel :</strong><br>
                        <div class="bg-white p-2 border rounded" style="max-height: 150px; overflow-y: auto;">
                            <span class="text-muted">${currentContent}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <strong>Résumé suggéré :</strong><br>
                        <div class="bg-white p-2 border rounded" style="max-height: 150px; overflow-y: auto;">
                            <span class="text-success fw-bold">${data.preview.suggested_summary}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Le résumé sera ajouté dans le champ "Content" selon la norme ISAD(G)
                    </small>
                </div>
            </div>
        `;
    }
    return '<div class="alert alert-warning">Aucun résumé généré</div>';
}

// Formater l'aperçu spécifique pour l'indexation thésaurus
function formatThesaurusPreview(data) {
    if (data.preview && data.preview.concepts && data.preview.concepts.length > 0) {
        let content = `
            <div class="mb-3 border rounded p-3 bg-light">
                <h6 class="text-primary"><i class="bi bi-tags me-2"></i>Indexation automatique</h6>
                <p><strong>Concepts trouvés :</strong> ${data.preview.concepts.length}</p>
                <div class="mb-3">
                    <strong>Mots-clés suggérés :</strong>
                    <div class="mt-2">
        `;

        data.preview.concepts.forEach(concept => {
            const weight = concept.weight ? Math.round(concept.weight * 100) : 'N/A';
            content += `
                <span class="badge bg-success me-2 mb-2 p-2">
                    ${concept.preferred_label}
                    <small>(${weight}%)</small>
                </span>
            `;
        });

        content += `
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Ces termes seront ajoutés à la sélection du thésaurus
                    </small>
                </div>
            </div>
        `;
        return content;
    }
    return '<div class="alert alert-warning">Aucun concept trouvé dans le thésaurus</div>';
}

// Afficher l'aperçu des modifications (ancienne fonction - gardée pour compatibilité)
function showMcpPreview(data, mode = 'MCP') {
    if (typeof bootstrap === 'undefined') {
        console.log('Bootstrap non disponible, affichage en console:', data);
        return;
    }

    let modal = document.getElementById('mcpPreviewModal');
    if (!modal) {
        modal = createPreviewModal();
    }

    const modalTitle = modal.querySelector('.modal-title');
    modalTitle.innerHTML = `<i class="bi bi-robot me-2"></i>Aperçu ${mode}`;

    const modalBody = modal.querySelector('.modal-body');
    let content = `<h6>Aperçu des modifications (${mode}) :</h6>`;

    if (data.previews) {
        Object.entries(data.previews).forEach(([feature, preview]) => {
            content += formatPreviewContent(feature, preview);
        });
    } else if (data.preview) {
        content += formatPreviewContent('single', data.preview);
    }

    if (data.tokens_used) {
        content += `<div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Tokens utilisés :</strong> ${data.tokens_used}
        </div>`;
    }

    modalBody.innerHTML = content;

    try {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } catch (e) {
        console.warn('Erreur création modal:', e);
        console.log('Aperçu des données:', data);
    }
}

// Formater le contenu de l'aperçu
function formatPreviewContent(feature, preview) {
    let content = `<div class="mb-3 border rounded p-3">`;
    content += `<h6 class="text-primary">${feature.charAt(0).toUpperCase() + feature.slice(1)}</h6>`;

    if (typeof preview === 'object') {
        if (preview.original_title && preview.suggested_title) {
            content += `
                <div class="row">
                    <div class="col-6">
                        <strong>Actuel :</strong><br>
                        <span class="text-muted">${preview.original_title}</span>
                    </div>
                    <div class="col-6">
                        <strong>Suggéré :</strong><br>
                        <span class="text-success">${preview.suggested_title}</span>
                    </div>
                </div>`;
        } else if (preview.concepts_found !== undefined) {
            content += `<p><strong>Concepts trouvés :</strong> ${preview.concepts_found}</p>`;
            if (preview.concepts && preview.concepts.length > 0) {
                content += '<p><strong>Principaux concepts :</strong></p><ul>';
                preview.concepts.slice(0, 5).forEach(concept => {
                    const weight = concept.weight ? Math.round(concept.weight * 100) : 'N/A';
                    content += `<li>${concept.preferred_label} (${weight}%)</li>`;
                });
                content += '</ul>';
            }
        } else if (preview.current_summary && preview.suggested_summary) {
            content += `
                <div class="row">
                    <div class="col-6">
                        <strong>Résumé actuel :</strong><br>
                        <span class="text-muted">${preview.current_summary || 'Aucun'}</span>
                    </div>
                    <div class="col-6">
                        <strong>Résumé suggéré :</strong><br>
                        <span class="text-success">${preview.suggested_summary}</span>
                    </div>
                </div>`;
        } else {
            content += `<pre class="bg-light p-2 rounded">${JSON.stringify(preview, null, 2)}</pre>`;
        }
    } else {
        content += `<p class="bg-light p-2 rounded">${preview}</p>`;
    }

    content += '</div>';
    return content;
}

// Créer la modal d'aperçu avec validation
function createPreviewModalWithValidation() {
    const modalHtml = `
        <div class="modal fade" id="mcpPreviewModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark">
                            <i class="bi bi-exclamation-triangle me-2"></i>Validation requise
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-success" onclick="applyValidatedChanges()">
                            <i class="bi bi-check-circle me-1"></i>Valider et appliquer au formulaire
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    return document.getElementById('mcpPreviewModal');
}

// Créer la modal d'aperçu (ancienne fonction - gardée pour compatibilité)
function createPreviewModal() {
    return createPreviewModalWithValidation();
}

// Appliquer les changements validés aux champs du formulaire
function applyValidatedChanges() {
    const modal = document.getElementById('mcpPreviewModal');
    if (!modal) return;

    const previewData = JSON.parse(modal.dataset.previewData || '{}');
    const action = modal.dataset.action || '';

    // DEBUG: Afficher les données exactes pour diagnostic
    console.log('Application des changements:', {
        action: action,
        previewData: previewData,
        timestamp: new Date().toISOString()
    });

    // Appliquer selon le type d'action
    if (action.includes('title')) {
        applyTitleChanges(previewData);
    } else if (action.includes('thesaurus')) {
        applyThesaurusChanges(previewData);
    } else if (action.includes('summary')) {
        applySummaryChanges(previewData);
    }

    // Fermer la modal
    const bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) {
        bsModal.hide();
    }
}

// Appliquer les changements de titre au formulaire
function applyTitleChanges(data) {
    if (data.preview && data.preview.suggested_title) {
        const titleField = document.getElementById('name');
        if (titleField) {
            titleField.value = data.preview.suggested_title;
            titleField.style.backgroundColor = '#d4edda'; // Vert clair pour indiquer le changement

            showMcpNotification('✅ Titre appliqué au formulaire ! N\'oubliez pas de sauvegarder.', 'success');

            // Retirer la couleur après 3 secondes
            setTimeout(() => {
                titleField.style.backgroundColor = '';
            }, 3000);
        }
    } else {
        showMcpNotification('❌ Aucun titre à appliquer', 'error');
    }
}

// Appliquer les changements de résumé au formulaire (dans le champ Content)
function applySummaryChanges(data) {
    console.log('applySummaryChanges appelée avec:', data);

    if (data.preview && data.preview.suggested_summary) {
        const contentField = document.getElementById('content');
        if (contentField) {
            const oldContent = contentField.value;
            const newContent = data.preview.suggested_summary;

            // REMPLACER complètement le contenu existant
            contentField.value = newContent;
            contentField.style.backgroundColor = '#d4edda'; // Vert clair pour indiquer le changement

            console.log('Résumé appliqué:', {
                ancien_contenu: oldContent,
                nouveau_contenu: newContent,
                champ_actuel: contentField.value
            });

            showMcpNotification('✅ Résumé REMPLACÉ dans le champ Content ! N\'oubliez pas de sauvegarder.', 'success');

            // Scroller vers le champ pour le rendre visible
            document.getElementById('contenu-tab')?.click(); // Aller à l'onglet Contenu
            setTimeout(() => {
                contentField.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Flash du champ pour attirer l'attention
                contentField.style.border = '3px solid #28a745';
                setTimeout(() => {
                    contentField.style.border = '';
                }, 2000);
            }, 300);

            // Retirer la couleur après 5 secondes
            setTimeout(() => {
                contentField.style.backgroundColor = '';
            }, 5000);
        } else {
            console.error('Champ content introuvable !');
            showMcpNotification('❌ Erreur: Champ content introuvable', 'error');
        }
    } else {
        console.error('Données de résumé manquantes:', data);
        showMcpNotification('❌ Aucun résumé à appliquer (données manquantes)', 'error');
    }
}

// Appliquer les changements d'indexation au formulaire (ajouter les termes)
function applyThesaurusChanges(data) {
    console.log('applyThesaurusChanges appelée avec:', data);

    if (data.preview && data.preview.concepts && data.preview.concepts.length > 0) {
        const container = document.getElementById('selected-terms-container');
        if (!container) {
            console.error('Conteneur selected-terms-container introuvable !');
            showMcpNotification('❌ Erreur: Conteneur des termes introuvable', 'error');
            return;
        }

        let addedCount = 0;
        let skippedCount = 0;

        data.preview.concepts.forEach((concept, index) => {
            console.log(`Traitement concept ${index + 1}:`, concept);

            // Vérifier si le terme n'est pas déjà sélectionné
            const existingTerm = container.querySelector(`[data-id="${concept.id}"]`);
            if (!existingTerm) {
                // Créer l'élément du terme
                const termElement = document.createElement('div');
                termElement.className = 'selected-term badge bg-success me-2 mb-2 p-2';
                termElement.dataset.id = concept.id;
                termElement.style.backgroundColor = '#28a745'; // Vert pour nouveau terme
                termElement.innerHTML = `
                    <span>${concept.preferred_label || concept.label || concept.literal_form || 'Terme sans nom'}</span>
                    <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.8em;" onclick="removeTerm(this)"></button>
                `;

                container.appendChild(termElement);
                addedCount++;
                console.log(`Terme ajouté: ${concept.preferred_label || concept.label}`);
            } else {
                skippedCount++;
                console.log(`Terme déjà présent: ${concept.preferred_label || concept.label}`);
            }
        });

        // Mettre à jour les champs cachés
        if (typeof updateTermIds === 'function') {
            updateTermIds();
        } else {
            console.warn('Fonction updateTermIds non disponible');
            // Fallback: créer manuellement les champs cachés
            const termIdsContainer = document.getElementById('term-ids-container');
            if (termIdsContainer) {
                termIdsContainer.innerHTML = '';
                container.querySelectorAll('.selected-term').forEach(termEl => {
                    const termId = termEl.dataset.id;
                    if (termId) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'term_ids[]';
                        hiddenInput.value = termId;
                        termIdsContainer.appendChild(hiddenInput);
                    }
                });
            }
        }

        console.log(`Résumé indexation: ${addedCount} ajoutés, ${skippedCount} ignorés`);

        if (addedCount > 0) {
            showMcpNotification(`✅ ${addedCount} terme(s) ajouté(s) au thésaurus ! N\'oubliez pas de sauvegarder.`, 'success');

            // Scroller vers la section indexation
            document.getElementById('indexation-tab')?.click(); // Aller à l'onglet Indexation
            setTimeout(() => {
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);

            // Retirer la couleur verte après 3 secondes
            setTimeout(() => {
                data.preview.concepts.forEach(concept => {
                    const termElement = container.querySelector(`[data-id="${concept.id}"]`);
                    if (termElement) {
                        termElement.style.backgroundColor = '';
                        termElement.className = 'selected-term badge bg-primary me-2 mb-2 p-2';
                    }
                });
            }, 3000);
        } else if (skippedCount > 0) {
            showMcpNotification(`ℹ️ Tous les ${skippedCount} terme(s) suggérés sont déjà sélectionnés`, 'info');
        } else {
            showMcpNotification('❌ Aucun terme valide à ajouter', 'error');
        }
    } else {
        console.error('Données de thésaurus manquantes ou vides:', data);
        showMcpNotification('❌ Aucun terme à ajouter (données manquantes)', 'error');
    }
}

// Appliquer les changements de l'aperçu (ancienne fonction - gardée pour compatibilité)
function applyPreviewChanges() {
    applyValidatedChanges();
}
</script>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('container_ids');
        if(!select) return;
        let timeout=null;
        const parent = select.parentElement;
        const search = document.createElement('input');
        search.type='text';
        search.className='form-control form-control-sm mb-1';
        search.placeholder='{{ __('search') }} containers...';
        parent.insertBefore(search, select);
        const list=document.createElement('div');
        list.className='list-group';
        parent.appendChild(list);

        function load(term){
                const url='{{ route('api.containers') }}'+(term?('?q='+encodeURIComponent(term)):'');
                fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}})
                    .then(r=>r.json())
                    .then(data=>{
                        list.innerHTML='';
                        data.forEach(c=>{
                            if([...select.selectedOptions].some(o=>o.value==c.id)) return;
                            const btn=document.createElement('button');
                            btn.type='button';
                            btn.className='list-group-item list-group-item-action py-1';
                            btn.textContent=c.code+' - '+c.name;
                            btn.onclick=()=>{ let opt=[...select.options].find(o=>o.value==c.id); if(!opt){ opt=document.createElement('option'); opt.value=c.id; opt.textContent=btn.textContent; select.appendChild(opt);} opt.selected=true; render(); };
                            list.appendChild(btn);
                        });
                    });
        }
        function render(){
                let zone=parent.querySelector('.selected-containers');
                if(!zone){ zone=document.createElement('div'); zone.className='selected-containers mt-2'; parent.appendChild(zone);} zone.innerHTML='';
                [...select.selectedOptions].forEach(o=>{
                    const tag=document.createElement('span'); tag.className='badge bg-secondary me-1 mb-1'; tag.textContent=o.textContent; const rm=document.createElement('button'); rm.type='button'; rm.className='btn-close btn-close-white ms-1'; rm.style.fontSize='0.6rem'; rm.onclick=()=>{o.selected=false; render();}; tag.appendChild(rm); zone.appendChild(tag);
                });
        }
        search.addEventListener('input',()=>{clearTimeout(timeout); timeout=setTimeout(()=>load(search.value.trim()),250);});
        load('');
        render();
});
</script>
@endpush



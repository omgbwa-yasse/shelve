@extends('layouts.app')

@php($metadataFields = $record->type ? app(\App\Services\MetadataValidationService::class)->getRecordMetadataFields($record->type) : [])

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-plus-circle"></i> Nouvelle notice fille —
                <a href="{{ route('records.show', $record) }}">{{ $record->code }} : {{ $record->name }}</a>
            </h1>
            <a href="{{ route('record-child.index', $record) }}" class="btn btn-outline-secondary">Retour</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('record-child.store', $record) }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" value="{{ old('code') }}" class="form-control" maxlength="30" placeholder="Auto si vide">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Intitulé <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Niveau</label>
                            <select name="level_id" class="form-select">
                                <option value="">— Hérité du parent —</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Statut</label>
                            <select name="status_id" class="form-select">
                                <option value="">—</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" @selected(old('status_id') == $status->id)>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Activité</label>
                            <select name="activity_id" class="form-select">
                                <option value="">— Héritée du parent —</option>
                                @foreach($activities as $activity)
                                    <option value="{{ $activity->id }}" @selected(old('activity_id') == $activity->id)>{{ $activity->code }} - {{ $activity->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date début</label>
                            <input type="text" name="date_start" value="{{ old('date_start') }}" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date fin</label>
                            <input type="text" name="date_end" value="{{ old('date_end') }}" class="form-control" maxlength="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date exacte</label>
                            <input type="date" name="date_exact" value="{{ old('date_exact') }}" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Producteurs</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="selected-authors-display" readonly>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#authorModal">
                                    Sélectionner
                                </button>
                            </div>
                            <input type="hidden" name="author_ids[]" id="author-ids">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thésaurus</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="selected-terms-display" readonly>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#termModal">
                                    Sélectionner
                                </button>
                            </div>
                            <input type="hidden" name="term_ids[]" id="term-ids">
                        </div>
                    </div>

                    {{-- Métadonnées du type (hérité du parent, système + personnalisées) --}}
                    @include('records.partials.metadata-fields', ['record' => null, 'metadataFields' => $metadataFields])

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Créer</button>
                    </div>
                </form>
            </div>
        </div>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function filterList(searchInput, listItems) {
                const filter = searchInput.value.toLowerCase();
                listItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filter) ? '' : 'none';
                });
            }

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
                    selectedAuthors.has(authorId) ? selectedAuthors.delete(authorId) : selectedAuthors.add(authorId);
                });
            });
            saveAuthors.addEventListener('click', () => {
                const names = Array.from(authorItems).filter(i => i.classList.contains('active')).map(i => i.textContent.trim());
                selectedAuthorsDisplay.value = names.join(', ');
                authorIds.value = Array.from(selectedAuthors).join(',');
                bootstrap.Modal.getInstance(authorModal).hide();
            });

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
                    selectedTerms.has(termId) ? selectedTerms.delete(termId) : selectedTerms.add(termId);
                });
            });
            saveTerms.addEventListener('click', () => {
                const names = Array.from(termItems).filter(i => i.classList.contains('active')).map(i => i.textContent.trim());
                selectedTermsDisplay.value = names.join(', ');
                termIds.value = Array.from(selectedTerms).join(',');
                bootstrap.Modal.getInstance(termModal).hide();
            });
        });
    </script>
@endpush

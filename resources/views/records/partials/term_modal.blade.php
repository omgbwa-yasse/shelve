{{-- partials/term_modal.blade.php --}}
<div class="modal fade" id="termModal" tabindex="-1" aria-labelledby="termModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termModalLabel">{{ __('select_thesaurus_terms') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Recherche et filtres -->
                <div class="row mb-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="text" id="term-search-input" class="form-control" placeholder="{{ __('search_term') }}">
                            <button class="btn btn-outline-secondary" type="button" id="term-search-button">
                                <i class="bi bi-search"></i> {{ __('search') }}
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select id="term-category-filter" class="form-select">
                            <option value="">{{ __('all_facets') }}</option>
                            <!-- Les catégories seront chargées via AJAX -->
                        </select>
                    </div>
                </div>

                <!-- Message de chargement et résultats -->
                <div id="term-loading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('loading') }}</span>
                    </div>
                </div>

                <div id="term-results-container">
                    <div id="term-list" class="list-group">
                        @foreach ($terms as $term)
                            <a href="#" class="list-group-item list-group-item-action"
                               data-id="{{ $term->id }}"
                               data-category="{{ $term->category_id ?? '' }}"
                               data-formatted-name="{{ $term->name }}{{ isset($term->category) ? '(' . ucfirst($term->category->name) . ')' : '' }}">
                                <span>{{ $term->name }}</span>
                                @if(isset($term->category))
                                    <span class="ms-1 text-muted">({{ ucfirst($term->category->name) }})</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                    <div id="term-no-results" class="alert alert-info mt-3" style="display: none;">
                        {{ __('no_terms_found') }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-primary" id="save-terms">{{ __('save') }}</button>
            </div>
        </div>
    </div>
</div>

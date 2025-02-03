{{-- partials/term_modal.blade.php --}}
<div class="modal fade" id="termModal" tabindex="-1" aria-labelledby="termModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termModalLabel">{{ __('select_thesaurus_terms') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="term-search" class="form-control mb-3" placeholder="{{ __('search_term') }}">
                <div id="term-list" class="list-group">
                    @foreach ($terms as $term)
                        <a href="#" class="list-group-item list-group-item-action" data-id="{{ $term->id }}">
                            {{ $term->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-primary" id="save-terms">{{ __('save') }}</button>
            </div>
        </div>
    </div>
</div>

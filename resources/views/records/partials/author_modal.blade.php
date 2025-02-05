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
                    </div>

                    <!-- Add Author Tab -->
                    <div class="tab-pane fade" id="add-author">
                        <form id="add-author-form">
                            @csrf
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
                                        <input type="text" id="parent_name" class="form-control" readonly>
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
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('add') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-primary btn-sm" id="save-authors">{{ __('save') }}</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
@endpush

@push('scripts')

@endpush

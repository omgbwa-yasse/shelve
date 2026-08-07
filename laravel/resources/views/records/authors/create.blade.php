@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="">
                <div class="pgp artisan serve
                shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">{{ __('add_new_author') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('record-author.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ __('type') }}</label>
                                <div class="input-group">
                                    <input type="hidden" name="type_id" id="selected_type_id" required>
                                    <input type="text" id="selected_type_name" class="form-control" readonly
                                           placeholder="{{ __('select_type') }}" required>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#typeModal">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('name') }}</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('parallel_name') }}</label>
                                <input type="text" name="parallel_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('other_name') }}</label>
                                <input type="text" name="other_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('lifespan') }}</label>
                                <input type="text" name="lifespan" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('locations') }}</label>
                                <input type="text" name="locations" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('parent_author') }}</label>
                                <div class="input-group">
                                    <input type="hidden" name="parent_id" id="selected_parent_id">
                                    <input type="text" id="selected_parent_name" class="form-control" readonly
                                           placeholder="{{ __('select_parent') }}">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#parentModal">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="clearParent">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('create_author') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Type Selection Modal -->
    <div class="modal fade" id="typeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('select_type') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="typeSearch"
                                   placeholder="{{ __('search_type') }}">
                        </div>
                    </div>
                    <div class="list-group" id="typesList">
                        @foreach ($types as $type)
                            <button type="button" class="list-group-item list-group-item-action type-item"
                                    data-id="{{ $type->id }}"
                                    data-name="{{ $type->name }}">
                                {{ $type->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parent Selection Modal -->
    <div class="modal fade" id="parentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('select_parent') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="parentSearch"
                                   placeholder="{{ __('search_parent') }}">
                        </div>
                    </div>
                    <div class="list-group" id="parentsList">
                        @foreach ($parents as $parent)
                            <button type="button" class="list-group-item list-group-item-action parent-item"
                                    data-id="{{ $parent->id }}"
                                    data-name="{{ $parent->name }}"
                                    data-type="{{ $parent->type->name }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $parent->name }}</h6>
                                    <span class="badge bg-secondary">{{ $parent->type->name }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .card {
                border: 0;
            }
            .modal-body {
                max-height: calc(100vh - 200px);
                overflow-y: auto;
            }
            .list-group {
                max-height: 300px;
                overflow-y: auto;
            }
            .list-group-item:hover {
                background-color: var(--bs-gray-100);
                cursor: pointer;
            }
            .list-group-item h6 {
                margin-bottom: 0;
                font-size: 0.9rem;
            }
            .badge {
                font-size: 0.75rem;
                font-weight: 500;
            }
            .input-group-text {
                background-color: #fff;
                border-right: 0;
            }
            .form-control:read-only {
                background-color: var(--bs-gray-100);
            }
            .form-label {
                color: var(--bs-gray-700);
                font-weight: 500;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Fonction de recherche générique
                function setupSearch(searchId, listId, itemClass) {
                    const searchInput = document.getElementById(searchId);
                    const listItems = document.querySelectorAll(`.${itemClass}`);

                    searchInput?.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        listItems.forEach(item => {
                            const text = item.textContent.toLowerCase();
                            item.style.display = text.includes(searchTerm) ? '' : 'none';
                        });
                    });
                }

                // Configuration des recherches
                setupSearch('typeSearch', 'typesList', 'type-item');
                setupSearch('parentSearch', 'parentsList', 'parent-item');

                // Sélection du type
                document.querySelectorAll('.type-item').forEach(item => {
                    item.addEventListener('click', function() {
                        document.getElementById('selected_type_id').value = this.dataset.id;
                        document.getElementById('selected_type_name').value = this.dataset.name;
                        bootstrap.Modal.getInstance(document.getElementById('typeModal')).hide();
                    });
                });

                // Sélection du parent
                document.querySelectorAll('.parent-item').forEach(item => {
                    item.addEventListener('click', function() {
                        document.getElementById('selected_parent_id').value = this.dataset.id;
                        document.getElementById('selected_parent_name').value =
                            `${this.dataset.name} (${this.dataset.type})`;
                        bootstrap.Modal.getInstance(document.getElementById('parentModal')).hide();
                    });
                });

                // Clear parent selection
                document.getElementById('clearParent')?.addEventListener('click', function() {
                    document.getElementById('selected_parent_id').value = '';
                    document.getElementById('selected_parent_name').value = '';
                });

                // Reset search on modal close
                ['typeModal', 'parentModal'].forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    modal?.addEventListener('hidden.bs.modal', function() {
                        const searchInput = this.querySelector('input[type="text"]');
                        if (searchInput) {
                            searchInput.value = '';
                            this.querySelectorAll('.list-group-item').forEach(item => {
                                item.style.display = '';
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

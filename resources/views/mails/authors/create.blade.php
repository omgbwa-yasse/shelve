@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('add_new_author') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('mail-author.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <!-- Type d'entité -->
                        <div class="col-md-6">
                            <label class="form-label">{{ __('type_of_entity') }}</label>
                            <div class="input-group">
                                <input type="hidden" name="type_id" id="selected_type_id" required>
                                <input type="text" id="selected_type_name" class="form-control" readonly required>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#typeModal">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Nom -->
                        <div class="col-md-6">
                            <label class="form-label">{{ __('name') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <!-- Nom équivalent -->
                        <div class="col-md-6">
                            <label class="form-label">{{ __('equivalent_name') }}</label>
                            <input type="text" name="parallel_name" class="form-control">
                        </div>

                        <!-- Autre nom -->
                        <div class="col-md-6">
                            <label class="form-label">{{ __('other_name') }}</label>
                            <input type="text" name="other_name" class="form-control">
                        </div>

                        <!-- Période de vie -->
                        <div class="col-md-6">
                            <label class="form-label">{{ __('lifespan') }}</label>
                            <input type="text" name="lifespan" class="form-control">
                        </div>

                        <!-- Résidence -->
                        <div class="col-md-6">
                            <label class="form-label">{{ __('locations') }}</label>
                            <input type="text" name="locations" class="form-control">
                        </div>

                        <!-- Entité parente -->
                        <div class="col-12">
                            <label class="form-label">{{ __('parent_entity') }}</label>
                            <div class="input-group">
                                <input type="hidden" name="parent_id" id="selected_parent_id">
                                <input type="text" id="selected_parent_name" class="form-control" readonly>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#parentModal">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">{{ __('save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Type d'entité -->
    <div class="modal fade" id="typeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('select_entity_type') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="typeSearch"
                               placeholder="{{ __('search_type') }}">
                    </div>
                    <div class="list-group" id="typesList">
                        @foreach ($authorTypes as $type)
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

    <!-- Modal Entité parente -->
    <div class="modal fade" id="parentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('select_parent_entity') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="parentSearch"
                               placeholder="{{ __('search_parent') }}">
                    </div>
                    <div class="list-group" id="parentsList">
                        @foreach ($parents as $parent)
                            <button type="button" class="list-group-item list-group-item-action parent-item"
                                    data-id="{{ $parent->id }}"
                                    data-name="{{ $parent->name }}"
                                    data-type="{{ $parent->authorType->name }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ $parent->name }}</strong>
                                    <small class="text-muted">{{ $parent->authorType->name }}</small>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gestionnaire de recherche pour le type d'entité
                const typeSearch = document.getElementById('typeSearch');
                const typesList = document.getElementById('typesList');
                const typeItems = typesList.querySelectorAll('.type-item');

                typeSearch?.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    typeItems.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });

                // Gestionnaire de recherche pour l'entité parente
                const parentSearch = document.getElementById('parentSearch');
                const parentsList = document.getElementById('parentsList');
                const parentItems = parentsList.querySelectorAll('.parent-item');

                parentSearch?.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    parentItems.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });

                // Sélection du type
                typeItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        document.getElementById('selected_type_id').value = id;
                        document.getElementById('selected_type_name').value = name;
                        bootstrap.Modal.getInstance(document.getElementById('typeModal')).hide();
                    });
                });

                // Sélection du parent
                parentItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        document.getElementById('selected_parent_id').value = id;
                        document.getElementById('selected_parent_name').value = name;
                        bootstrap.Modal.getInstance(document.getElementById('parentModal')).hide();
                    });
                });

                // Réinitialisation de la recherche à la fermeture des modals
                ['typeModal', 'parentModal'].forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    modal?.addEventListener('hidden.bs.modal', function() {
                        const searchInput = modal.querySelector('input[type="text"]');
                        if (searchInput) {
                            searchInput.value = '';
                            modal.querySelectorAll('.list-group-item').forEach(item => {
                                item.style.display = '';
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

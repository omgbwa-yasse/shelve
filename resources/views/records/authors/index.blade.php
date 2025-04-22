@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people-fill me-2"></i>
                        {{ __('physical_legal_persons_list') }}
                    </h5>
                    <a href="{{ route('record-author.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('add_author') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="p-3 border-bottom">
                    <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                        <input type="text" class="form-control border-start-0"
                               id="searchInput"
                               placeholder="{{ __('search_authors') }}">
                    </div>
                </div>

                <div class="author-list">
                    @forelse ($authors as $author)
                        <div class="author-item border-bottom" data-author-name="{{ strtolower($author->name) }}">
                            <div class="d-flex align-items-center p-3">
                                <div class="author-info flex-grow-1" style="cursor: pointer;"
                                     onclick="window.location='{{ route('record-author.show', $author->id) }}'">
                                    <div class="d-flex align-items-center">
                                        <span class="fw-medium">{{ $author->name }}</span>
                                        <span class="badge bg-secondary ms-2 text-white">
                                        {{ $author->type->name }}
                                    </span>
                                    </div>
                                    @if($author->parent)
                                        <small class="text-muted mt-1 d-block">
                                            <i class="bi bi-diagram-2 me-1"></i>
                                            {{ __('parent') }}: {{ $author->parent->name }}
                                        </small>
                                    @endif
                                    @if($author->lifespan)
                                        <small class="text-muted mt-1 d-block">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ $author->lifespan }}
                                        </small>
                                    @endif
                                </div>
                                <div class="ms-auto d-flex gap-2 align-items-center">
                                    <a href="{{ route('records.sort') }}?categ=author&id={{ $author->id}}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-archive me-1"></i>
                                        {{ __('view_archives') }}
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('record-author.show', $author->id) }}">
                                                    <i class="bi bi-eye me-2"></i>
                                                    {{ __('view_details') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('record-author.edit', $author->id) }}">
                                                    <i class="bi bi-pencil me-2"></i>
                                                    {{ __('edit') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">{{ __('no_authors_found') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .author-item:hover {
                background-color: #f8f9fa;
            }
            .author-info:hover {
                color: #0d6efd;
            }
            .badge {
                font-weight: 500;
                font-size: 0.75rem;
            }
            .author-list {
                max-height: calc(100vh - 250px);
                overflow-y: auto;
            }
            .dropdown-item:hover {
                background-color: #f8f9fa;
            }
            .alert {
                border: none;
                border-radius: 0;
            }
            .btn-light:hover {
                background-color: #f8f9fa;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const authorItems = document.querySelectorAll('.author-item');

                searchInput?.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    authorItems.forEach(item => {
                        const authorName = item.dataset.authorName;
                        item.style.display = authorName.includes(searchTerm) ? '' : 'none';
                    });
                });

                // Fermeture automatique des alertes après 5 secondes
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }, 5000);
                });
            });
        </script>
    @endpush
@endsection

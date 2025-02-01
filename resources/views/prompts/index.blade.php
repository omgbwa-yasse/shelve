@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3">Bibliothèque de Prompts</h1>
                    <a href="{{ route('prompts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Nouveau Prompt
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text"
                           class="form-control"
                           id="promptSearch"
                           placeholder="Rechercher un prompt...">
                    <button class="btn btn-outline-secondary dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown">
                        Filtres
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-filter="all">Tous</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="public">Publics</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="draft">Brouillons</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="archived">Archivés</a></li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-3">
                <!-- Sidebar for Categories -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Catégories</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Tous
                            <span class="badge bg-primary rounded-pill">{{ $prompts->total() }}</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Mes Prompts
                            <span class="badge bg-secondary rounded-pill">{{ $prompts->where('user_id', Auth::id())->count() }}</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Favoris
                            <span class="badge bg-warning rounded-pill">0</span>
                        </a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statistiques</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Prompts Publics</span>
                            <strong>{{ $prompts->where('is_public', true)->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Brouillons</span>
                            <strong>{{ $prompts->where('is_draft', true)->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Archives</span>
                            <strong>{{ $prompts->where('is_archived', true)->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($prompts as $prompt)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="{{ route('prompts.show', $prompt) }}"
                                                   class="text-decoration-none">
                                                    {{ $prompt->name }}
                                                </a>
                                            </h5>
                                            <p class="mb-1 text-muted small">
                                                <i class="bi bi-person"></i> {{ $prompt->user->name }} |
                                                <i class="bi bi-clock"></i> {{ $prompt->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex gap-1">
                                                @if($prompt->is_draft)
                                                    <span class="badge bg-warning">Brouillon</span>
                                                @endif
                                                @if($prompt->is_public)
                                                    <span class="badge bg-success">Public</span>
                                                @endif
                                            </div>
                                            <div class="btn-group">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="Exécuter"
                                                        onclick="executePrompt('{{ $prompt->id }}')">
                                                    <i class="bi bi-play-fill"></i>
                                                </button>
                                                @can('update', $prompt)
                                                    <a href="{{ route('prompts.edit', $prompt) }}"
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center py-4">
                                    <p class="text-muted mb-0">Aucun prompt trouvé</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @if($prompts->hasPages())
                        <div class="card-footer">
                            {{ $prompts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Search functionality
                const searchInput = document.getElementById('promptSearch');
                searchInput.addEventListener('input', function(e) {
                    // Implement real-time search here
                });

                // Execute prompt function
                window.executePrompt = function(promptId) {
                    // Implement prompt execution logic
                };
            });
        </script>
    @endpush
@endsection

<!-- resources/views/bulletin-boards/archives.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Archives</h2>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="bi bi-funnel"></i> Filtrer
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-sort-down"></i> Trier
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="?sort=date_desc">Plus récent</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="?sort=date_asc">Plus ancien</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="?sort=name">Alphabétique</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des archives -->
        <div class="row">
            @forelse($archivedPosts as $post)
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">Archivé le {{ $post->deleted_at->format('d/m/Y') }}</span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-dark p-0" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <form action="{{ route('bulletin-boards.restore', $post) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurer
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('bulletin-boards.delete-permanently', $post) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"
                                                        onclick="return confirm('Cette action est irréversible. Continuer ?')">
                                                    <i class="bi bi-trash"></i> Supprimer définitivement
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $post->name }}</h5>
                            <p class="card-text text-muted mb-3">{{ Str::limit($post->description, 150) }}</p>

                            @if($post->type === 'event')
                                <div class="mb-3">
                                    <div class="small text-muted">
                                        <i class="bi bi-calendar3"></i>
                                        {{ $post->start_date->format('d/m/Y H:i') }}
                                        @if($post->end_date)
                                            - {{ $post->end_date->format('d/m/Y H:i') }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="small text-muted">
                                Par {{ $post->user->name }} •
                                Archivé {{ $post->deleted_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Aucune publication archivée
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $archivedPosts->links() }}
        </div>
    </div>

    <!-- Modal de filtres -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtres</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('bulletin-boards.archives') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">Tous</option>
                                <option value="post">Publications</option>
                                <option value="event">Événements</option>
                                <option value="announcement">Annonces</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date d'archivage</label>
                            <input type="date" name="archived_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Organisation</label>
                            <select name="organisation" class="form-select">
                                <option value="">Toutes</option>
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Appliquer les filtres</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

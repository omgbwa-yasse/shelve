@extends('layouts.app')

@section('styles')
    <style>
        .bulletin-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .bulletin-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .bulletin-card.event {
            border-left-color: #4CAF50;
        }
        .bulletin-card.announcement {
            border-left-color: #2196F3;
        }
        .bulletin-card.post {
            border-left-color: #FF9800;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .nav-pills .nav-link.active {
            background-color: #2196F3;
        }
        .action-buttons {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .bulletin-card:hover .action-buttons {
            opacity: 1;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        <!-- En-tête avec titre et boutons d'action -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-0">Babillard</h2>
                <p class="text-muted small mb-0">{{ $bulletinBoards->total() }} publications</p>
            </div>
            <div class="d-flex gap-2">
                @can('create', App\Models\BulletinBoard::class)
                    <a href="{{ route('bulletin-boards.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Nouvelle publication
                    </a>
                @endcan
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </div>

        <!-- Barre de recherche et filtres -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form action="{{ route('bulletin-boards.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Rechercher</button>
                </form>
            </div>
            <div class="col-md-4">
                <select name="organisation" class="form-select" onchange="this.form.submit()">
                    <option value="">Toutes les organisations</option>
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ request('organisation') == $organisation->id ? 'selected' : '' }}>
                            {{ $organisation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Navigation par onglets -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link {{ request('type') == '' ? 'active' : '' }}" href="{{ route('bulletin-boards.index') }}">
                    Tout <span class="badge bg-primary ms-1">{{ $bulletinBoards->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('type') == 'announcement' ? 'active' : '' }}"
                   href="{{ route('bulletin-boards.index', ['type' => 'announcement']) }}">
                    Annonces
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('type') == 'event' ? 'active' : '' }}"
                   href="{{ route('bulletin-boards.index', ['type' => 'event']) }}">
                    Événements
                </a>
            </li>
        </ul>

        <!-- Liste des publications -->
        <div class="row">
            @forelse($bulletinBoards as $board)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card bulletin-card {{ $board->type }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">{{ $board->name }}</h5>
                                <span class="badge {{ $board->status == 'published' ? 'bg-success' : 'bg-warning' }} status-badge">
                            {{ ucfirst($board->status) }}
                        </span>
                            </div>

                            <p class="card-text text-muted mb-3">{{ Str::limit($board->description, 100) }}</p>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    <i class="bi bi-person"></i> {{ $board->user->name }}
                                    <br>
                                    <i class="bi bi-clock"></i> {{ $board->created_at->diffForHumans() }}
                                </div>
                                <div class="action-buttons">
                                    <a href="{{ route('bulletin-boards.show', $board) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $board)
                                        <a href="{{ route('bulletin-boards.edit', $board) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $board)
                                        <form action="{{ route('bulletin-boards.destroy', $board) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr ?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        Aucune publication trouvée
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $bulletinBoards->links() }}
        </div>
    </div>

    <!-- Modal de filtres -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtres avancés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('bulletin-boards.index') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label">État</label>
                            <select name="status" class="form-select">
                                <option value="">Tous</option>
                                <option value="draft">Brouillon</option>
                                <option value="published">Publié</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date de création</label>
                            <input type="date" name="date" class="form-control">
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

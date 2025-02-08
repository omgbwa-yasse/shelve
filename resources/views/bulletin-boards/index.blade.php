<!-- resources/views/bulletin-boards/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Babillard</h2>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-lg"></i> Créer
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('bulletin-boards.posts.create') }}">
                                    <i class="bi bi-file-text"></i> Publication
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('bulletin-boards.events.create') }}">
                                    <i class="bi bi-calendar-event"></i> Événement
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('bulletin-boards.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                            <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">Tout le contenu</option>
                            <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>Publications</option>
                            <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Événements</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="organisation" class="form-select">
                            <option value="">Toutes les organisations</option>
                            @foreach($organisations as $organisation)
                                <option value="{{ $organisation->id }}" {{ request('organisation') == $organisation->id ? 'selected' : '' }}>
                                    {{ $organisation->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des contenus -->
        <div class="row">
            @forelse($bulletinBoards as $board)
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-{{ $board->events->count() > 0 ? 'success' : 'primary' }}">
                            {{ $board->events->count() > 0 ? 'Événement' : 'Publication' }}
                        </span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-dark p-0" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('bulletin-boards.show', $board) }}">
                                                <i class="bi bi-eye"></i> Voir
                                            </a>
                                        </li>
                                        @can('update', $board)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('bulletin-boards.edit', $board) }}">
                                                    <i class="bi bi-pencil"></i> Modifier
                                                </a>
                                            </li>
                                        @endcan
                                        @can('delete', $board)
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <form action="{{ route('bulletin-boards.destroy', $board) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')">
                                                        <i class="bi bi-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $board->name }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($board->description, 150) }}</p>

                            @if($board->events->count() > 0)
                                @php $event = $board->events->first() @endphp
                                <div class="mt-3">
                                    <div class="small text-muted">
                                        <i class="bi bi-calendar3"></i>
                                        {{ $event->start_date->format('d/m/Y H:i') }}
                                        @if($event->end_date)
                                            - {{ $event->end_date->format('d/m/Y H:i') }}
                                        @endif
                                    </div>
                                    @if($event->location)
                                        <div class="small text-muted">
                                            <i class="bi bi-geo-alt"></i> {{ $event->location }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($board->posts->count() > 0)
                                @php $post = $board->posts->first() @endphp
                                <div class="mt-3">
                                    <div class="small text-muted">
                                        <i class="bi bi-clock"></i>
                                        Publié {{ $post->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    <i class="bi bi-person"></i> {{ $board->user->name }}
                                </div>
                                @if($board->organisations->count() > 0)
                                    <div class="small">
                                        @foreach($board->organisations as $organisation)
                                            <span class="badge bg-light text-dark">{{ $organisation->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Aucun contenu trouvé
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $bulletinBoards->links() }}
        </div>
    </div>
@endsection

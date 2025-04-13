@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between align-items-center mb-4">
        <div class="col-auto">
            <h1>Tableaux d'affichage</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('bulletin-boards.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Créer un tableau
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @forelse($bulletinBoards as $board)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $board->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Créé par {{ $board->creator->name }} · {{ $board->created_at->diffForHumans() }}
                        </p>
                        <p class="card-text">{{ Str::limit($board->description, 100) }}</p>

                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <span class="small text-muted d-block">Événements</span>
                                <span class="fw-bold">{{ $board->events->count() }}</span>
                            </div>
                            <div class="me-3">
                                <span class="small text-muted d-block">Publications</span>
                                <span class="fw-bold">{{ $board->posts->count() }}</span>
                            </div>
                            <div>
                                <span class="small text-muted d-block">Utilisateurs</span>
                                <span class="fw-bold">{{ $board->organisations->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('bulletin-boards.show', $board->id) }}" class="btn btn-sm btn-outline-primary">
                                Voir les détails
                            </a>

                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('bulletin-boards.edit', $board->id) }}">
                                                <i class="fas fa-edit fa-fw me-1"></i> Modifier
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('bulletin-boards.destroy', $board->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce tableau ?')">
                                                    <i class="fas fa-trash fa-fw me-1"></i> Supprimer
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>

                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i> Aucun tableau d'affichage n'est disponible pour le moment.
                </div>
            </div>
        @endforelse
    </div>

    <nav aria-label="Pagination">
        <ul class="pagination justify-content-center">
          @if ($bulletinBoards->onFirstPage())
            <li class="page-item disabled">
              <span class="page-link">Précédent</span>
            </li>
          @else
            <li class="page-item">
              <a class="page-link" href="{{ $bulletinBoards->previousPageUrl() }}" rel="prev">Précédent</a>
            </li>
          @endif

          @foreach ($bulletinBoards->getUrlRange(1, $bulletinBoards->lastPage()) as $page => $url)
            <li class="page-item {{ $page == $bulletinBoards->currentPage() ? 'active' : '' }}">
              <a class="page-link" href="{{ $url }}">{{ $page }}</a>
            </li>
          @endforeach

          @if ($bulletinBoards->hasMorePages())
            <li class="page-item">
              <a class="page-link" href="{{ $bulletinBoards->nextPageUrl() }}" rel="next">Suivant</a>
            </li>
          @else
            <li class="page-item disabled">
              <span class="page-link">Suivant</span>
            </li>
          @endif
        </ul>
      </nav>

</div>
@endsection

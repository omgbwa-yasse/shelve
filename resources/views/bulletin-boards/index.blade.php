<!-- resources/views/bulletin-boards/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="bi bi-pin-board"></i> Tableaux d'affichage</h1>
        </div>
        <div class="col-md-6 text-end">
            @can('create', App\Models\BulletinBoard::class)
            <a href="{{ route('bulletin-boards.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nouveau tableau
            </a>
            @endcan
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('bulletin-boards.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="organisation_id" class="form-select">
                        <option value="">Toutes les organisations</option>
                        @foreach($organisations as $organisation)
                            <option value="{{ $organisation->id }}" @selected(request('organisation_id') == $organisation->id)>
                                {{ $organisation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input type="checkbox" name="admin_only" class="form-check-input" id="adminOnly"
                               @checked(request('admin_only'))>
                        <label class="form-check-label" for="adminOnly">
                            Mes tableaux administrés uniquement
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-funnel"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des tableaux -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($bulletinBoards as $board)
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ $board->name }}</h5>
                    <p class="card-text text-muted">
                        {{ Str::limit($board->description, 100) }}
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Créé par {{ $board->user->name }}
                        </small>
                        <a href="{{ route('bulletin-boards.show', $board) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Voir
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                Aucun tableau d'affichage trouvé.
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $bulletinBoards->links() }}
    </div>
</div>
@endsection






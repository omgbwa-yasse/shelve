@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Publications - {{ $bulletinBoard->name }}</h1>
            <p class="text-muted">{{ $bulletinBoard->description }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('bulletin-boards.posts.create', $bulletinBoard) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle publication
            </a>
            <a href="{{ route('bulletin-boards.show', $bulletinBoard) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('bulletin-boards.posts.index', $bulletinBoard) }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publié</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
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
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"> </i>
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if($posts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Statut</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Créateur</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $post)
                                <tr>
                                    <td>
                                        <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard, $post]) }}">
                                            {{ $post->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($post->status == 'published')
                                            <span class="badge bg-success">Publié</span>
                                        @elseif($post->status == 'draft')
                                            <span class="badge bg-warning">Brouillon</span>
                                        @elseif($post->status == 'cancelled')
                                            <span class="badge bg-danger">Annulé</span>
                                        @endif
                                    </td>
                                    <td>{{ $post->start_date->format('d/m/Y') }}</td>
                                    <td>{{ $post->end_date ? $post->end_date->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $post->creator->name }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('bulletin-boards.posts.show', [$bulletinBoard, $post]) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('bulletin-boards.posts.edit', [$bulletinBoard, $post]) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $post->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $posts->links() }}
            @else
                <div class="text-center p-4">
                    <p>Aucune publication trouvée.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($posts as $post)
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal{{ $post->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $post->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel{{ $post->id }}">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer la publication "{{ $post->name }}" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('bulletin-boards.posts.destroy', [$bulletinBoard, $post]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

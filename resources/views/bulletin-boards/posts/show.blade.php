@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.index') }}">Tableaux d'affichage</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.show', $bulletinBoard['id']) }}">{{ $bulletinBoard->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bulletin-boards.posts.index', $bulletinBoard['id']) }}">Publications</a></li>
                    <li class="breadcrumb-item active">{{ $post->name }}</li>
                </ol>
            </nav>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-{{ $post->status == 'published' ? 'success' : ($post->status == 'draft' ? 'warning' : 'secondary') }} me-2">
                            {{ ucfirst($post->status) }}
                        </span>
                        <span class="text-muted">Publication</span>
                    </div>

                    @if($post->canBeEditedBy(Auth::user()))
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-1"></i> Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('bulletin-boards.posts.edit', [$bulletinBoard['id'], $post->id]) }}">
                                        <i class="fas fa-edit fa-fw me-1"></i> Modifier
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                        <i class="fas fa-toggle-on fa-fw me-1"></i> Changer le statut
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('bulletin-boards.posts.destroy', [$bulletinBoard['id'], $post->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')">
                                            <i class="fas fa-trash fa-fw me-1"></i> Supprimer
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <h1 class="card-title mb-3">{{ $post->name }}</h1>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <i class="fas fa-calendar-alt fa-fw text-muted me-2"></i>
                                <strong>Date de début:</strong>
                                {{ $post->start_date->format('d/m/Y') }}
                            </div>

                            @if($post->end_date)
                                <div class="mb-3">
                                    <i class="fas fa-calendar-check fa-fw text-muted me-2"></i>
                                    <strong>Date de fin:</strong>
                                    {{ $post->end_date->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <i class="fas fa-user fa-fw text-muted me-2"></i>
                                <strong>Créé par:</strong>
                                {{ $post->creator->name }}
                            </div>

                            <div class="mb-3">
                                <i class="fas fa-clock fa-fw text-muted me-2"></i>
                                <strong>Créé le:</strong>
                                {{ $post->created_at->format('d/m/Y à H:i') }}
                            </div>

                            <div class="mb-3">
                                <i class="fas fa-edit fa-fw text-muted me-2"></i>
                                <strong>Dernière modification:</strong>
                                {{ $post->updated_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Contenu</h5>
                        </div>
                        <div class="card-body">
                            <div class="post-content">
                                {!! nl2br(e($post->description)) !!}
                            </div>
                        </div>
                    </div>

                    @if($post->attachments->isNotEmpty())
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Pièces jointes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fichier</th>
                                                <th>Type</th>
                                                <th>Taille</th>
                                                <th>Ajouté par</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($post->attachments as $attachment)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-2">
                                                                <i class="fas {{ $attachment->getIconClass() }} fa-lg text-muted"></i>
                                                            </div>
                                                            <div>
                                                                {{ $attachment->file_name }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ Str::upper(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) }}</td>
                                                    <td>{{ $attachment->getHumanReadableSize() }}</td>
                                                    <td>{{ $attachment->creator->name }}</td>
                                                    <td>
                                                        <a href="{{ Storage::url($attachment->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="fas fa-download me-1"></i> Télécharger
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('bulletin-boards.posts.index', $bulletinBoard['id']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour aux publications
                        </a>

                        @if($post->canBeEditedBy(Auth::user()))
                            <a href="{{ route('bulletin-boards.posts.edit', [$bulletinBoard['id'], $post->id]) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de changement de statut -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">Changer le statut de la publication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('bulletin-boards.posts.change-status', [$bulletinBoard['id'], $post->id]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Nouveau statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" {{ $post->status == 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="published" {{ $post->status == 'published' ? 'selected' : '' }}>Publié</option>
                            <option value="cancelled" {{ $post->status == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Changer le statut</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

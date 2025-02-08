<!-- resources/views/bulletin-boards/show.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Colonne principale -->
            <div class="col-md-8">
                <!-- En-tête de la publication -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="mb-1">{{ $bulletinBoard->name }}</h2>
                                <div class="text-muted">
                                    Publié par {{ $bulletinBoard->user->name }} • {{ $bulletinBoard->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                @can('update', $bulletinBoard)
                                    <a href="{{ route('bulletin-boards.edit', $bulletinBoard) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </a>
                                @endcan
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="window.print()">
                                                <i class="bi bi-printer"></i> Imprimer
                                            </a>
                                        </li>
                                        @can('delete', $bulletinBoard)
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <form action="{{ route('bulletin-boards.destroy', $bulletinBoard) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')">
                                                        <i class="bi bi-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card-text mb-4">
                            {!! nl2br(e($bulletinBoard->description)) !!}
                        </div>

                        @if($bulletinBoard->type === 'event')
                            <div class="bg-light p-3 rounded mb-4">
                                <h5 class="mb-3">Détails de l'événement</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="bi bi-calendar3"></i>
                                            <strong>Début:</strong> {{ $bulletinBoard->start_date->format('d/m/Y H:i') }}
                                        </p>
                                        @if($bulletinBoard->end_date)
                                            <p class="mb-2">
                                                <i class="bi bi-calendar3"></i>
                                                <strong>Fin:</strong> {{ $bulletinBoard->end_date->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                    @if($bulletinBoard->location)
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <i class="bi bi-geo-alt"></i>
                                                <strong>Lieu:</strong> {{ $bulletinBoard->location }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($bulletinBoard->attachments->count() > 0)
                            <div class="border-top pt-3">
                                <h5 class="mb-3">Pièces jointes</h5>
                                <div class="list-group">
                                    @foreach($bulletinBoard->attachments as $attachment)
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-paperclip me-2"></i>
                                                <div>
                                                    <div>{{ $attachment->name }}</div>
                                                    <small class="text-muted">{{ number_format($attachment->size / 1024, 2) }} KB</small>
                                                </div>
                                            </div>
                                            <div class="btn-group">
                                                <a href="{{ route('bulletin-boards.attachments.download', $attachment) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @can('delete', $attachment)
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="removeAttachment({{ $attachment->id }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Section commentaires -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Commentaires</h5>
                    </div>
                    <div class="card-body">
                        @if(setting('bulletin_board.allow_comments', true))
                            <form action="{{ route('bulletin-boards.comments.store', $bulletinBoard) }}" method="POST" class="mb-4">
                                @csrf
                                <div class="mb-3">
                            <textarea name="content"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Ajouter un commentaire..."></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Commenter</button>
                                </div>
                            </form>
                        @endif

                        <div class="comments-list">
                            @forelse($bulletinBoard->comments as $comment)
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar">
                                            <img src="{{ $comment->user->avatar_url }}"
                                                 alt="Avatar"
                                                 class="rounded-circle"
                                                 width="40">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-0">{{ $comment->user->name }}</h6>
                                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                            @can('delete', $comment)
                                                <form action="{{ route('bulletin-boards.comments.destroy', $comment) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-link text-danger btn-sm p-0"
                                                            onclick="return confirm('Supprimer ce commentaire ?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                        <p class="mb-0 mt-2">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-muted">Aucun commentaire pour le moment</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div class="col-md-4">
                <!-- Informations sur les organisations -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Organisations concernées</h5>
                    </div>
                    <div class="card-body">
                        @if($bulletinBoard->organisations->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($bulletinBoard->organisations as $organisation)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $organisation->name }}
                                        <span class="badge bg-secondary rounded-pill">
                                {{ $organisation->users_count ?? 0 }} membres
                            </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Aucune organisation associée</p>
                        @endif
                    </div>
                </div>

                <!-- Administrateurs -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Administrateurs</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @foreach($bulletinBoard->administrators as $admin)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $admin->avatar_url }}"
                                             alt="Avatar"
                                             class="rounded-circle me-2"
                                             width="32">
                                        {{ $admin->name }}
                                    </div>
                                    <span class="badge bg-primary">{{ $admin->pivot->role }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function removeAttachment(attachmentId) {
                if (confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?')) {
                    fetch(`/bulletin-boards/attachments/${attachmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            }
                        });
                }
            }
        </script>
    @endpush
@endsection

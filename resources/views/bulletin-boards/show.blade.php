<!-- resources/views/bulletin-boards/show.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="h4 mb-0">{{ $bulletinBoard->name }}</h2>
                            <div class="btn-group">
                                @can('update', $bulletinBoard)
                                    <a href="{{ route('bulletin-boards.edit', $bulletinBoard) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </a>
                                @endcan
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
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
                        <div class="mb-4">
                            {!! nl2br(e($bulletinBoard->description)) !!}
                        </div>

                        @if($bulletinBoard->events->count() > 0)
                            @php $event = $bulletinBoard->events->first() @endphp
                            <div class="bg-light p-3 rounded mb-4">
                                <h5>Détails de l'événement</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="bi bi-calendar3"></i>
                                            <strong>Début:</strong> {{ $event->start_date->format('d/m/Y H:i') }}
                                        </p>
                                        @if($event->end_date)
                                            <p class="mb-2">
                                                <i class="bi bi-calendar3"></i>
                                                <strong>Fin:</strong> {{ $event->end_date->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                    @if($event->location)
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <i class="bi bi-geo-alt"></i>
                                                <strong>Lieu:</strong> {{ $event->location }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-3">
                                <span class="badge bg-{{ $event->status == 'published' ? 'success' : 'warning' }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                                </div>
                            </div>
                        @endif

                        @if($bulletinBoard->posts->count() > 0)
                            @php $post = $bulletinBoard->posts->first() @endphp
                            <div class="mb-4">
                            <span class="badge bg-{{ $post->status == 'published' ? 'success' : 'warning' }}">
                                {{ ucfirst($post->status) }}
                            </span>
                            </div>
                        @endif

                        @if($bulletinBoard->attachments->count() > 0)
                            <div class="mt-4">
                                <h5>Pièces jointes</h5>
                                <div class="list-group">
                                    @foreach($bulletinBoard->attachments as $attachment)
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-paperclip me-2"></i>
                                                {{ $attachment->name }}
                                            </div>
                                            <div class="btn-group">
                                                <a href="{{ route('bulletin-boards.attachments.download', $attachment) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

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
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Aucune organisation associée</p>
                        @endif
                    </div>
                </div>

                <!-- Informations sur le créateur -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Informations</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Créé par:</strong> {{ $bulletinBoard->user->name }}
                        </p>
                        <p class="mb-2">
                            <strong>Date de création:</strong> {{ $bulletinBoard->created_at->format('d/m/Y H:i') }}
                        </p>
                        <p class="mb-0">
                            <strong>Dernière modification:</strong> {{ $bulletinBoard->updated_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

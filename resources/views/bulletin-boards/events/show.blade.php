<!-- resources/views/bulletin-boards/events/show.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Colonne principale -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h4 mb-0">{{ $event->name }}</h2>
                                <div class="text-muted small">
                                    Publié par {{ $event->user->name }} • {{ $event->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="d-flex gap-2">
{{--                                @if($canRegister)--}}
{{--                                    @if(!$isRegistered)--}}
{{--                                        <form action="{{ route('bulletin-boards.events.register', $event) }}" method="POST">--}}
{{--                                            @csrf--}}
{{--                                            <button type="submit" class="btn btn-primary">--}}
{{--                                                <i class="bi bi-calendar-check"></i> S'inscrire--}}
{{--                                            </button>--}}
{{--                                        </form>--}}
{{--                                    @else--}}
{{--                                        <form action="{{ route('bulletin-boards.events.unregister', $event) }}" method="POST">--}}
{{--                                            @csrf--}}
{{--                                            <button type="submit" class="btn btn-outline-danger">--}}
{{--                                                <i class="bi bi-calendar-x"></i> Se désinscrire--}}
{{--                                            </button>--}}
{{--                                        </form>--}}
{{--                                    @endif--}}
{{--                                @endif--}}

                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('update', $event->bulletinBoard)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('bulletin-boards.events.edit', $event) }}">
                                                    <i class="bi bi-pencil"></i> Modifier
                                                </a>
                                            </li>
                                        @endcan
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="window.print()">
                                                <i class="bi bi-printer"></i> Imprimer
                                            </a>
                                        </li>
                                        @can('delete', $event->bulletinBoard)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('bulletin-boards.events.destroy', $event) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                                        <i class="bi bi-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Informations de l'événement -->
                        <div class="bg-light p-3 rounded mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <i class="bi bi-calendar3"></i>
                                        <strong>Début:</strong> {{ $event->start_date }}
                                    </p>
                                    @if($event->end_date)
                                        <p class="mb-2">
                                            <i class="bi bi-calendar3"></i>
                                            <strong>Fin:</strong> {{ $event->end_date }}
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
                            <div class="mt-2">
                            <span class="badge bg-{{ $event->status == 'published' ? 'success' : 'warning' }}">
                                {{ ucfirst($event->status) }}
                            </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            {!! nl2br(e($event->description)) !!}
                        </div>

                        <!-- Pièces jointes -->
                        @if($event->bulletinBoard->attachments->count() > 0)
                            <div class="mt-4">
                                <h5>Documents joints</h5>
                                <div class="list-group">
                                    @foreach($event->bulletinBoard->attachments as $attachment)
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-paperclip me-2"></i>
                                                {{ $attachment->name }}
                                            </div>
                                            <a href="{{ route('bulletin-boards.attachments.download', $attachment) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div class="col-md-4">
                <!-- Participants -->

                <!-- Organisations -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Organisations concernées</h5>
                    </div>
                    <div class="card-body">
                        @if($event->bulletinBoard->organisations->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($event->bulletinBoard->organisations as $organisation)
                                    <div class="list-group-item">
                                        {{ $organisation->name }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Aucune organisation associée</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

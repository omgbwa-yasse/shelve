@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Événements</h2>
                    <a href="{{ route('public.events.create') }}" class="btn btn-primary">Nouvel événement</a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        @foreach($events as $event)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $event->name }}</h5>
                                        <p class="card-text">{{ Str::limit($event->description, 100) }}</p>
                                        <div class="mb-2">
                                            <strong>Date de début:</strong> {{ $event->start_date->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="mb-2">
                                            <strong>Date de fin:</strong> {{ $event->end_date->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="mb-2">
                                            <strong>Lieu:</strong> {{ $event->location ?? 'En ligne' }}
                                        </div>
                                        @if($event->is_online)
                                            <div class="mb-2">
                                                <strong>Lien:</strong> <a href="{{ $event->online_link }}" target="_blank">{{ $event->online_link }}</a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('public.events.show', $event) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.events.edit', $event) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.events.destroy', $event) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

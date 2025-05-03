@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ $publicEvent->name }}</h2>
                    <div>
                        <a href="{{ route('public.events.edit', $publicEvent) }}" class="btn btn-warning btn-sm">Modifier</a>
                        <form action="{{ route('public.events.destroy', $publicEvent) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">Supprimer</button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h4>Description</h4>
                        <p>{{ $publicEvent->description }}</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Dates</h4>
                            <p><strong>Début:</strong> {{ $publicEvent->start_date->format('d/m/Y H:i') }}</p>
                            <p><strong>Fin:</strong> {{ $publicEvent->end_date->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Lieu</h4>
                            @if($publicEvent->is_online)
                                <p><strong>Type:</strong> Événement en ligne</p>
                                <p><strong>Lien:</strong> <a href="{{ $publicEvent->online_link }}" target="_blank">{{ $publicEvent->online_link }}</a></p>
                            @else
                                <p><strong>Type:</strong> Événement en présentiel</p>
                                <p><strong>Lieu:</strong> {{ $publicEvent->location }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4>Inscriptions</h4>
                        <p><strong>Nombre d'inscrits:</strong> {{ $publicEvent->registrations->count() }}</p>

                        @if($publicEvent->registrations->count() > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Date d'inscription</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($publicEvent->registrations as $registration)
                                            <tr>
                                                <td>{{ $registration->user->name }}</td>
                                                <td>{{ $registration->user->email }}</td>
                                                <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Aucune inscription pour le moment.</p>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('public.events.index') }}" class="btn btn-secondary">Retour à la liste</a>
                        <a href="{{ route('public.event-registrations.create', ['event' => $publicEvent->id]) }}" class="btn btn-primary">S'inscrire</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

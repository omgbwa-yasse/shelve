@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Inscriptions aux événements</h2>
                    <a href="{{ route('public.event-registrations.create') }}" class="btn btn-primary">Nouvelle inscription</a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Événement</th>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Statut</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrations as $registration)
                                    <tr>
                                        <td>{{ $registration->event->title ?? 'N/A' }}</td>
                                        <td>{{ $registration->user->name ?? $registration->name ?? 'Inconnu' }}</td>
                                        <td>{{ $registration->email }}</td>
                                        <td>{{ $registration->phone ?? 'N/A' }}</td>
                                        <td>
                                            @switch($registration->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge bg-success">Confirmée</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Annulée</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $registration->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('public.event-registrations.show', $registration) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('public.event-registrations.edit', $registration) }}" class="btn btn-warning btn-sm">Modifier</a>
                                            <form action="{{ route('public.event-registrations.destroy', $registration) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?')">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $registrations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

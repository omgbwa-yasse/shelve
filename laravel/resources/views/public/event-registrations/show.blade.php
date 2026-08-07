@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Détails de l'inscription</h2>
                    <div>
                        <a href="{{ route('public.event-registrations.edit', $registration) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('public.event-registrations.index') }}" class="btn btn-secondary">Retour</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informations de l'inscription</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nom :</th>
                                    <td>{{ $registration->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email :</th>
                                    <td>{{ $registration->email }}</td>
                                </tr>
                                <tr>
                                    <th>Téléphone :</th>
                                    <td>{{ $registration->phone ?? 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <th>Statut :</th>
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
                                </tr>
                                <tr>
                                    <th>Date d'inscription :</th>
                                    <td>{{ $registration->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière modification :</th>
                                    <td>{{ $registration->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>

                            @if($registration->user)
                                <h5 class="mt-4">Utilisateur associé</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <p><strong>Nom :</strong> {{ $registration->user->name }}</p>
                                        <p><strong>Email :</strong> {{ $registration->user->email }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5>Événement</h5>
                            @if($registration->event)
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $registration->event->title }}</h6>
                                        <p class="card-text">{{ Str::limit($registration->event->description, 200) }}</p>

                                        @if($registration->event->start_date)
                                            <p><strong>Date de début :</strong> {{ $registration->event->start_date->format('d/m/Y H:i') }}</p>
                                        @endif

                                        @if($registration->event->end_date)
                                            <p><strong>Date de fin :</strong> {{ $registration->event->end_date->format('d/m/Y H:i') }}</p>
                                        @endif

                                        @if($registration->event->location)
                                            <p><strong>Lieu :</strong> {{ $registration->event->location }}</p>
                                        @endif

                                        <a href="{{ route('public.events.show', $registration->event) }}" class="btn btn-sm btn-outline-primary">Voir l'événement</a>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Aucun événement associé</p>
                            @endif
                        </div>
                    </div>

                    @if($registration->notes)
                        <div class="mt-4">
                            <h5>Notes</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $registration->notes }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h5>Actions</h5>
                        @if($registration->status == 'pending')
                            <form action="{{ route('public.event-registrations.update', $registration) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-success">Confirmer l'inscription</button>
                            </form>
                        @endif

                        @if($registration->status != 'cancelled')
                            <form action="{{ route('public.event-registrations.update', $registration) }}" method="POST" class="d-inline ms-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette inscription ?')">Annuler l'inscription</button>
                            </form>
                        @endif

                        <form action="{{ route('public.event-registrations.destroy', $registration) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?')">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

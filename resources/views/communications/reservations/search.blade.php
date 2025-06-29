@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Recherche avancée des réservations</h1>
    <form action="{{ route('communications.reservations.search.index') }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ request('code') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label for="name" class="form-label">Objet</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ request('name') }}">
            </div>
            <div class="col-md-3 mb-3">
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-select">
                    <option value="">-- Tous --</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status['value'] }}" {{ request('status') == $status['value'] ? 'selected' : '' }}>{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="user_id" class="form-label">Utilisateur</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">-- Tous --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="user_organisation_id" class="form-label">Organisation</label>
                <select name="user_organisation_id" id="user_organisation_id" class="form-select">
                    <option value="">-- Toutes --</option>
                    @foreach ($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ request('user_organisation_id') == $organisation->id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="return_date" class="form-label">Date retour</label>
                <input type="date" class="form-control" id="return_date" name="return_date" value="{{ request('return_date') }}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    @if(isset($reservations) && $reservations->count())
        <h2>Résultats</h2>
        <div class="row">
            @foreach ($reservations as $reservation)
                <div class="col-12 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('communications.reservations.show', $reservation->id) }}">
                                    <strong>{{ $reservation->code ?? 'N/A' }} : {{ $reservation->name ?? 'N/A' }}</strong>
                                </a>
                            </h5>
                            <p class="card-text">
                                <strong>Statut :</strong>
                                @if($reservation->status)
                                    <span class="badge bg-{{ $reservation->status->color() }}">{{ $reservation->status->label() }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                                <br>
                                <strong>Utilisateur :</strong> {{ $reservation->user->name ?? 'N/A' }}<br>
                                <strong>Organisation :</strong> {{ $reservation->userOrganisation->name ?? 'N/A' }}<br>
                                <strong>Date retour :</strong> {{ $reservation->return_date ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(isset($reservations))
        <div class="alert alert-warning">Aucune réservation trouvée pour ces critères.</div>
    @endif
</div>
@endsection

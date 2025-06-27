@extends('layouts.app')

@section('content')
    <div class="container">

        <h1>Fiche de réservation</h1>
            <div class="d-flex justify-content-between align-items-center bg-light p-3 mb-3">
                <div class="d-flex align-items-center">
                    <a href="#" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-cart me-1"></i>
                        Chariot
                    </a>
                    <a href="#" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-download me-1"></i>
                        Exporter
                    </a>
                    <a href="#" class="btn btn-light btn-sm me-2">
                        <i class="bi bi-printer me-1"></i>
                        Imprimer
                    </a>
                </div>
                <div class="d-flex align-items-center">
                    <a href="#" class="btn btn-light btn-sm">
                        <i class="bi bi-check-square me-1"></i>
                        Tout chocher
                    </a>
                </div>
            </div>



        <div class="col-13 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">
                               <strong>{{ $reservation->code ?? 'N/A' }} : {{ $reservation->name ?? 'N/A' }}</strong>
                            </h5>
                            <p class="card-text">
                                <strong>Contenu :</strong> {{ $reservation->content ?? 'N/A' }}<br>
                            </p>
                        </div>
                        <div class="card-text d-flex flex-wrap">
                            <div class="mr-3">
                                <strong>Demandeur :</strong>
                                <span>{{ $reservation->user->name ?? 'N/A' }} ({{ $reservation->userOrganisation->name ?? 'N/A' }})</span>
                            </div>
                        </div>

                        <div class="card-text d-flex flex-wrap">
                            <div class="mr-3">
                                <strong>Opérateur :</strong>
                                <span>{{ $reservation->operator->name ?? 'N/A' }} ({{ $reservation->operatorOrganisation->name ?? 'N/A' }})</span>
                            </div>
                        </div>

                        <div class="card-text d-flex flex-wrap">
                            <div class="mr-3">
                                <strong>Date de retour prévu :</strong> {{ $reservation->return_date ?? 'N/A' }}
                            </div>
                            <div>
                                <strong>Statut :</strong> {{ $reservation->status->name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ route('reservations.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('reservations.edit', $reservation->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('reservations.destroy', $reservation->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this reservation?')">Delete</button>
        </form>
        <a href="{{ route('reservations.records.create', $reservation->id) }}" class="btn btn-warning ">Ajouter des documents</a>
        <form action="{{ route('reservations-approved') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="id" value="{{ $reservation->id }}">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check me-1"></i> Approuver la réservation
            </button>
        </form>
    </div>

    <ul class="list-group list-group-none">
        @foreach($reservation->records as $record)
                <li class="list-group-item">{{  $record->name }}
                    <form action="{{ route('reservations.records.destroy', [$reservation , $record]) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
                    </form>
                </li>
        @endforeach

    </ul>


@endsection

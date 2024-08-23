@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Reservations</h1>
        <a href="{{ route('reservations.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Create New Reservation
        </a>

        <div class="row">
            @foreach ($reservations as $reservation)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title">
                                        <i class="bi bi-upc"></i> Code: {{ $reservation->code ?? 'N/A' }}
                                    </h5>
                                    <p class="card-text">
                                        <i class="bi bi-person"></i> <strong>Operator:</strong> {{ $reservation->operator->name ?? 'N/A' }}<br>
                                        <i class="bi bi-building"></i> <strong>Operator Organisation:</strong> {{ $reservation->operatorOrganisation->name ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="card-text">
                                        <i class="bi bi-people"></i> <strong>User:</strong> {{ $reservation->user->name ?? 'N/A' }}<br>
                                        <i class="bi bi-building"></i> <strong>User Organisation:</strong> {{ $reservation->userOrganisation->name ?? 'N/A' }}<br>
                                        <i class="bi bi-flag"></i> <strong>Status:</strong> {{ $reservation->status->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('reservations.show', $reservation->id) }}" class="btn btn-info mt-3">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

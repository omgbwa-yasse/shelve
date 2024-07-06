@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Reservation #{{ $reservation->code }}</h1>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Operator</h5>
                <p class="card-text">{{ $reservation->operator->name }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">User</h5>
                <p class="card-text">{{ $reservation->user->name }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Status</h5>
                <p class="card-text">{{ $reservation->status->name }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">User Organisation</h5>
                <p class="card-text">{{ $reservation->userOrganisation->name }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Operator Organisation</h5>
                <p class="card-text">{{ $reservation->operatorOrganisation->name }}</p>
            </div>
        </div>
        <a href="{{ route('reservations.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('reservations.edit', $reservation->id) }}" class="btn btn-warning btn-sm">Edit</a>
        <form action="{{ route('reservations.destroy', $reservation->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this reservation?')">Delete</button>
        </form>
        <a href="{{ route('reservations.records.create', $reservation->id) }}" class="btn btn-warning btn-sm">Ajouter des documents</a>
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

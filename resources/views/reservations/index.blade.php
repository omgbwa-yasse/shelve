@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Reservations</h1>
        <a href="{{ route('reservations.create') }}" class="btn btn-primary mb-3">Create New Reservation</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Operator</th>
                    <th>Operator Organisation</th>
                    <th>User</th>
                    <th>User Organisation</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->code }}</td>
                        <td>{{ $reservation->operator->name }}</td>
                        <td>{{ $reservation->operatorOrganisation->name }}</td>
                        <td>{{ $reservation->user->name }}</td>
                        <td>{{ $reservation->userOrganisation->name }}</td>
                        <td>{{ $reservation->status->name }}</td>
                        <td>
                            <a href="{{ route('reservations.show', $reservation->id) }}" class="btn btn-info btn-sm">View</a>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

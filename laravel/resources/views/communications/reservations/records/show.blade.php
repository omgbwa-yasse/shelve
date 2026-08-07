@extends('layouts.app')

@section('content')
    <h1>Communication Record Details</h1>
    <table class="table">
        <tr>
            <th>ID</th>
            <td>{{ $reservationRecord->id }}</td>
        </tr>
        <tr>
            <th>Communication</th>
            <td>{{ $reservationRecord->communication->code ?? '' }}</td>
        </tr>
        <tr>
            <th>Record</th>
            <td>{{ $reservationRecord->record->name ?? ''}}</td>
        </tr>
        <tr>
            <th>Is Original</th>
            <td>{{ $reservationRecord->is_original ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <th>Reservation Date</th>
            <td>{{ $reservationRecord->reservation_date }}</td>
        </tr>
        <tr>
            <th>Déjà communication</th>
            <td>{{ $reservationRecord->communication ? 'Yes' : 'No' }}</td>
        </tr>
    </table>
    <a href="{{ route('communications.reservations.index') }}" class="btn btn-secondary">Back</a>
    <a href="{{ route('communications.reservations.records.edit', [$reservation->id , $reservationRecord->id]) }}" class="btn btn-warning">Edit</a>
    <form action="{{ route('communications.reservations.records.destroy', [$reservation->id , $reservationRecord]) }}" method="POST" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
    </form>



@endsection

@extends('layouts.app')

@section('content')

    <h1>  N°  {{ $reservation->code }} par {{ $reservation->user->name }} / {{ $reservation->user->created_at }}</h1>
    <h3>Liste des documents reservés</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Record</th>
                <th>Original</th>
                <th>Date</th>
                <th>Communication</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservationRecords as $record)
                <tr>
                    <td>{{ $record->reservation->code }}</td>
                    <td>{{ $record->record->name }}</td>
                    <td>{{ $record->is_original ? 'Yes' : 'No' }}</td>
                    <td>{{ $record->reservation_date }}</td>
                    <td>{{ $record->communication ? 'Yes' : 'No' }}</td>
                    <td>  <a href="{{ route('communications.reservations.records.show',[$reservation->id , $record->id]) }}" class="btn btn-info">Show</a> </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

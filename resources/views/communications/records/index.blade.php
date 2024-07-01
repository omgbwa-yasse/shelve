@extends('layouts.app')

@section('content')
    <h1>Communication Records</h1>
    <a href="{{ route('transactions.records.create', $communication) }}" class="btn btn-primary">Create New Record</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Communication</th>
                <th>Record</th>
                <th>Is Original</th>
                <th>Return Date</th>
                <th>Return Effective</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($communicationRecords as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->communication->name }}</td>
                    <td>{{ $record->record->name }}</td>
                    <td>{{ $record->is_original ? 'Yes' : 'No' }}</td>
                    <td>{{ $record->return_date }}</td>
                    <td>{{ $record->return_effective }}</td>
                    <td>
                        <a href="{{ route('transactions.records.show',[$communication , $record->id]) }}" class="btn btn-info">Show</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

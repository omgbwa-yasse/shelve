@extends('layouts.app')

@section('content')
    <h1>Communication Record Details</h1>
    <table class="table">
        <tr>
            <th>ID</th>
            <td>{{ $communicationRecord->id }}</td>
        </tr>
        <tr>
            <th>Communication</th>
            <td>{{ $communicationRecord->communication->code ?? '' }}</td>
        </tr>
        <tr>
            <th>Record</th>
            <td>{{ $communicationRecord->record->name ?? ''}}</td>
        </tr>
        <tr>
            <th>Is Original</th>
            <td>{{ $communicationRecord->is_original ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <th>Return Date</th>
            <td>{{ $communicationRecord->return_date }}</td>
        </tr>
        <tr>
            <th>Return Effective</th>
            <td>{{ $communicationRecord->return_effective }}</td>
        </tr>
    </table>
    <a href="{{ route('transactions.index', $communication) }}" class="btn btn-secondary">Back</a>
    <a href="{{ route('transactions.records.edit', [$communication , $communicationRecord]) }}" class="btn btn-warning">Edit</a>
    <form action="{{ route('transactions.records.destroy', [$communication , $communicationRecord]) }}" method="POST" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
    </form>
@endsection

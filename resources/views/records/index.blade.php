@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Records</h1>
    <a href="{{ route('records.create') }}" class="btn btn-primary mb-3">Create Record</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
            <tr>
                <td>{{ $record->id }}</td>
                <td>{{ $record->code }}</td>
                <td>{{ $record->name }}</td>
                <td>
                    <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-info">Voir la fiche</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

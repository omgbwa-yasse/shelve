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
                    <a href="{{ route('records.show', $record) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('records.destroy', $record) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

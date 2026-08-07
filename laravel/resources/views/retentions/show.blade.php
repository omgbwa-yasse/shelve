@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Retention Details</h1>
        <table class="table">
            <tr>
                <th>ID</th>
                <td>{{ $retention->id }}</td>
            </tr>
            <tr>
                <th>Code</th>
                <td>{{ $retention->code }}</td>
            </tr>
            <tr>
                <th>Nom</th>
                <td>{{ $retention->name }}</td>
            </tr>
            <tr>
                <th>Duration</th>
                <td>{{ $retention->duration }}</td>
            </tr>
            <tr>
                <th>Sort</th>
                <td>{{ $retention->sort->name }}({{ $retention->sort->code }})</td>
            </tr>

        </table>
        <a href="{{ route('retentions.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('retentions.edit', $retention->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('retentions.destroy', $retention->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this retention?')">Delete</button>
        </form>
    </div>
@endsection

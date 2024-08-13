@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Les actions</h1>
    <a href="{{ route('mail-action.create') }}" class="btn btn-primary mb-3">Ajouter une action</a>
    <table class="table">
        <thead>
            <tr>
                <th>Intitulé </th>
                <th>Duration (en heures)</th>
                <th>A retourner</th>
                <th>Description </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mailActions as $mailAction)
            <tr>
                <td>{{ $mailAction->name }}</td>
                <td>{{ $mailAction->duration }}</td>
                <td>{{ $mailAction->to_return ? 'Yes' : 'No' }}</td>
                <td>{{ $mailAction->description }}</td>
                <td>
                    <a href="{{ route('mail-action.edit', $mailAction) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('mail-action.destroy', $mailAction) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this mail action?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Etages/niveaux</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Niveau / étage</th>
                    <th>Description</th>
                    <th>Bâtiments </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($floors as $floor)
                    <tr>
                        <td>{{ $floor->name }}</td>
                        <td>{{ $floor->description ?? '' }}</td>
                        <td>{{ $floor->building->name }}</td>
                        <td>
                            <a href="{{ route('floors.show', [$floor->building->id, $floor->id] ) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('floors.edit', [$floor->building->id, $floor->id]) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('floors.destroy', [$floor->building->id, $floor->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this floor?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

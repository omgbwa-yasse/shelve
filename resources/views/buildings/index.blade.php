@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Buildings</h1>
        <a href="{{ route('buildings.create') }}" class="btn btn-primary mb-3">Ajouter un bâtiment</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($buildings as $building)
                    <tr>
                        <td>{{ $building->id }}</td>
                        <td>{{ $building->name }}</td>
                        <td>{{ $building->description }}
                            @if($building->floors->count() > 1)
                            ( Batiment à {{ $building->floors->count() }} niveaux)
                            @endif

                        </td>
                        <td>
                            <a href="{{ route('buildings.show', $building->id) }}" class="btn btn-info btn-sm">Paramètres</a>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

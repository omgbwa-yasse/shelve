@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des termes</h1>
        <a href="{{ route('terms.create') }}" class="btn btn-primary mb-3">Ajouter un terme</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Langue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($terms as $term)
                    <tr>
                        <td>{{ $term->id }}</td>
                        <td>{{ $term->name }}</td>
                        <td>{{ $term->description }}</td>
                        <td>{{ $term->language->name }}</td>
                        <td>
                            <a href="{{ route('terms.show', $term->id) }}" class="btn btn-info">Paramètres</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

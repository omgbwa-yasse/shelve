@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Détails du terme</h1>
        <table class="table">
            <tr>
                <th>ID</th>
                <td>{{ $term->id }}</td>
            </tr>
            <tr>
                <th>Nom</th>
                <td>{{ $term->name }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $term->description }}</td>
            </tr>

            <tr>
                <th> Parent </th>
                <td>{{ $term->parent->name ?? 'Debut de la branche' }}</td>
            </tr>
            <tr>
                <th> Type </th>
                <td>{{ $term->type->name?? '' }}</td>
            </tr>
            <tr>
                <th> Domaine </th>
                <td>{{ $term->category->name?? '' }}</td>
            </tr>
            <tr>
                <th>Langue</th>
                <td>{{ $term->language->name }}</td>
            </tr>
        </table>
        <a href="{{ route('terms.index') }}" class="btn btn-secondary">Retour</a>
        <a href="{{ route('terms.edit', $term->id) }}" class="btn btn-primary">Modifier</a>
        <form action="{{ route('terms.destroy', $term->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce terme ?')">Supprimer</button>
        </form>
        <a href="{{ route('term-equivalents.create', $term) }}" class="btn btn-primary">Ajouter une traduction </a>
        <a href="{{ route('term-relations.create', $term) }}" class="btn btn-primary">Ajouter une une relation </a>
    </div>
@endsection

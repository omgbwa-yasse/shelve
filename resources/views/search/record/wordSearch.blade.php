@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Selectionner un term</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Type </th>
                <th>Parent </th>
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
                    <td>{{ $term->type->code }} - {{ $term->type->name }}</td>
                    <td>{{ $term->parent->name ?? 'Debut de la branche' }}</td>
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

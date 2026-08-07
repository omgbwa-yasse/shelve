@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Liste des types de contenant</h1>
    <a href="{{ route('container-property.create') }}" class="btn btn-primary mb-3">Ajouter un nouveau format</a>
    <table class="table">
        <thead>
            <tr>
                <th>Intitulé</th>
                <th>Largeur(cm)</th>
                <th>Hauteur(cm) </th>
                <th>Profondeur(cm) </th>
                <th>Volumétrie </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($containerProperties as $containerProperty)
                <tr>
                    <td>{{ $containerProperty->name }}</td>
                    <td>{{ $containerProperty->width }}</td>
                    <td>{{ $containerProperty->length }}</td>
                    <td>{{ $containerProperty->depth }}</td>
                    <td>{{ ($containerProperty->depth/100 * $containerProperty->length/100 * $containerProperty->width/100)*12 }} ml</td>
                    <td>
                        <a href="{{ route('container-property.show', $containerProperty->id) }}" class="btn btn-info btn-sm">Paramètre</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

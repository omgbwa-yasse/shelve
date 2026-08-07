@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col">
            <h1>Liste des Statuts d'Enregistrement</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('record-statuses.create') }}" class="btn btn-primary">Ajouter un statut</a>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recordStatuses as $recordStatus)
                    <tr class="clickable-row" data-href="{{ route('record-statuses.show', $recordStatus->id) }}">
                        <th scope="row">{{ $recordStatus->id }}</th>
                        <td>{{ $recordStatus->name }}</td>
                        <td>{{ $recordStatus->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

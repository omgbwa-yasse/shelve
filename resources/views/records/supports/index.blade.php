@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col">
            <h1>Liste des Supports</h1>
        </div>
        <div class="col text-end">
            <a href="{{ route('record-supports.create') }}" class="btn btn-primary">Ajouter un support</a>
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
                    @foreach($recordSupports as $recordSupport)
                    <tr class="clickable-row" data-href="{{ route('record-supports.show', $recordSupport->id) }}">
                        <th scope="row">{{ $recordSupport->id }}</th>
                        <td>{{ $recordSupport->name }}</td>
                        <td>{{ $recordSupport->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

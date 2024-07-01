@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Communication Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $communication->code }}</h5>
                <p class="card-text">Operator: {{ $communication->operator->name }}</p>
                <p class="card-text">Operator Organisation: {{ $communication->operatorOrganisation->name }}</p>
                <p class="card-text">User: {{ $communication->user->name }}</p>
                <p class="card-text">User Organisation: {{ $communication->userOrganisation->name }}</p>
                <p class="card-text">Return Date: {{ $communication->return_date }}</p>
                <p class="card-text">Return Effective: {{ $communication->return_effective }}</p>
                <p class="card-text">Status: {{ $communication->status->name }}</p>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Back</a>
                <a href="{{ route('transactions.edit', $communication->id) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('transactions.destroy', $communication->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this communication?')">Delete</button>
                </form>
                <hr>
                <a href="{{ route('transactions.records.create', $communication->id) }}" class="btn btn-warning">Ajouter des documents</a>
            </div>
        </div>

        @foreach($communication->records as $record)
            <ul class="list-group list-group-numbered">
                <li class="list-group-item">
                    {{ $record->record->name }} / {{ $record->is_original }} : {{ $record->return_date }} ; date effective : {{ $record->return_effective }}
                    <a href="{{ route('transactions.records.show', [$communication, $record]) }}" class="btn btn-secondary">Voir</a>
                </li>
            </ul>




        @endforeach



    </div>
@endsection

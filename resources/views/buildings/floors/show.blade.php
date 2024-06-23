@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Niveau détails</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $floor->name }}</h5>
                <p class="card-text">{{ $floor->description }}</p>
                <p class="card-text"><strong> Bâtiment :</strong> {{ $floor->building->name }}</p>
                <a href="{{ route('buildings.show', $building) }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection

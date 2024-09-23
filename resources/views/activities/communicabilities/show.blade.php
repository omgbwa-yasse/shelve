@extends('layouts.app')

@section('content')
    <h1>Détails du délai de communicabilité</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $communicability->name }}</h5>
            <p class="card-text">Activité : {{ $activity->name }}</p>
            <a href="{{ route('activities.communicabilities.index', $activity->id) }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
@endsection

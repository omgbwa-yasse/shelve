@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Détails de l'action</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $mailAction->name }}</h5>
            <p class="card-text">
                <strong>Durée :</strong> {{ $mailAction->duration }}<br>
                <strong>A retourner:</strong> {{ $mailAction->to_return ? 'Yes' : 'No' }}<br>
                <strong>Description:</strong> {{ $mailAction->description }}
            </p>
            <a href="{{ route('mail-action.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection

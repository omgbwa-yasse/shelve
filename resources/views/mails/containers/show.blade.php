@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Contenant pour archivage : détailles </h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $mailContainer->name }}</h5>
                <p class="card-text">Code: {{ $mailContainer->code }}</p>
                <p class="card-text">Code: {{ $mailContainer->creator->name }}</p>
                <p class="card-text">Type: {{ $mailContainer->containerProperty->name }}</p>
                <p class="card-text">Propriétaire : {{ $mailContainer->creatorOrganisation->name }}</p>
                <a href="{{ route('mail-container.index') }}" class="btn btn-secondary">Back</a>
                <a href="{{ route('mail-container.edit', $mailContainer->id) }}" class="btn btn-warning btn-secondary">Edit</a>
                <form action="{{ route('mail-container.destroy', $mailContainer->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-secondary" onclick="return confirm('Are you sure you want to delete this mail container?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection

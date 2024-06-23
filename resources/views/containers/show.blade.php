@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Contenant d'archives details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $container->code }}</h5>
                <p class="card-text"><strong>Shelf:</strong> {{ $container->shelf->code }}</p>
                <p class="card-text"><strong>Status:</strong> {{ $container->status->name }}</p>
                <p class="card-text"><strong>Property:</strong> {{ $container->property->name }}</p>
                <a href="{{ route('containers.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection

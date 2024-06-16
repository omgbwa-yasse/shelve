@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Author Details</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $author->name }}</h5>
            <p class="card-text">Type: {{ $author->authorType->name }}</p>
            @if ($author->parallel_name)
                <p class="card-text">Parallel Name: {{ $author->parallel_name }}</p>
            @endif
            @if ($author->other_name)
                <p class="card-text">Other Name: {{ $author->other_name }}</p>
            @endif
            @if ($author->lifespan)
                <p class="card-text">Lifespan: {{ $author->lifespan }}</p>
            @endif
            @if ($author->locations)
                <p class="card-text">Locations: {{ $author->locations }}</p>
            @endif
            @if ($author->parent)
                <p class="card-text">Parent Author: {{ $author->parent->name }}</p>
            @endif
        </div>
    </div>

    <a href="{{ route('mail-author.index') }}" class="btn btn-secondary mt-3">Back to Authors</a>
</div>
@endsection

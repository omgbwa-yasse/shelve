@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Liste des personnes physiques et morales</h2>
    <a href="{{ route('record-author.create') }}" class="btn btn-primary mb-3">Ajouter un auteur</a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

        <ul class="list-group list-group-none">
            @forelse ($authors as $author)
            <li class="list-group-item d-flex align-items-center">
                {{ $author->name }} ({{ $author->authorType->name }})
                <a href="{{ route('records.sort') }}?categ=author&id={{ $author->id}}" style="margin-left: auto;">
                    <button type="button" class="btn btn-success">Voir les archives</button>
                </a>
            </li>
            @empty
            <p class="text-muted">No authors found.</p>
            @endforelse
        </ul>
</div>

<script>
    const authors = @json($authors);
</script>

@endsection

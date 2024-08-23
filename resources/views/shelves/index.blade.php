@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Shelves</h1>
        <a href="{{ route('shelves.create') }}" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Create Shelf
        </a>

        <div id="shelfList">
            @foreach ($shelves as $shelf)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h5 class="card-title mb-2">
                                    <b>{{ $shelf->code ?? 'N/A' }}</b> - ID: {{ $shelf->id ?? 'N/A' }}
                                </h5>
                                <p class="card-text mb-1">
                                    <i class="bi bi-file-earmark-text"></i> <strong>Observation:</strong> {{ $shelf->observation ?? 'N/A' }}<br>
                                    <i class="bi bi-box"></i> <strong>Face(s):</strong> {{ $shelf->face ?? 'N/A' }}<br>
                                    <i class="bi bi-columns-gap"></i> <strong>Travée:</strong> {{ $shelf->ear ?? 'N/A' }}<br>
                                    <i class="bi bi-grid-3x3-gap"></i> <strong>Tablettes:</strong> {{ $shelf->shelf ?? 'N/A' }}<br>
                                    <i class="bi bi-rulers"></i> <strong>Longueur tablette:</strong> {{ $shelf->shelf_length ?? 'N/A' }} Cm<br>
                                    <i class="bi bi-building"></i> <strong>Salle d'archives:</strong> {{ $shelf->room->name ?? 'N/A' }}<br>
                                    <i class="bi bi-arrow-left-right"></i> <strong>Volumétrie:</strong> {{ $shelf->face && $shelf->ear && $shelf->shelf && $shelf->shelf_length ? ($shelf->face * $shelf->ear * $shelf->shelf * $shelf->shelf_length) / 100 : 'N/A' }} ml
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end text-center">
                                <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('shelves.show', $shelf->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('shelves.edit', $shelf->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('shelves.destroy', $shelf->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this shelf?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

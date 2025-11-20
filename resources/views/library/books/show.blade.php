@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-book"></i> {{ $book->title }}</h1>
        <div>
            <a href="{{ route('library.books.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
            </a>
            <a href="{{ route('library.books.edit', $book->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> {{ __('Modifier') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informations générales') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Titre') }}</div>
                        <div class="col-md-9">{{ $book->title }}</div>
                    </div>
                    @if($book->subtitle)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Sous-titre') }}</div>
                        <div class="col-md-9">{{ $book->subtitle }}</div>
                    </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('ISBN') }}</div>
                        <div class="col-md-9">{{ $book->formatted_isbn ?? $book->isbn ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Éditeur(s)') }}</div>
                        <div class="col-md-9">
                            @if($book->publishers->count() > 0)
                                {{ $book->publishers->pluck('name')->join(', ') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Collection') }}</div>
                        <div class="col-md-9">{{ $book->series->title ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Année') }}</div>
                        <div class="col-md-9">{{ $book->publication_year ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Édition') }}</div>
                        <div class="col-md-9">{{ $book->edition ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Langue') }}</div>
                        <div class="col-md-9">{{ $book->language->name ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Format') }}</div>
                        <div class="col-md-9">{{ $book->format->name ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Reliure') }}</div>
                        <div class="col-md-9">{{ $book->binding->name ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Pages') }}</div>
                        <div class="col-md-9">{{ $book->pages ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Dewey') }}</div>
                        <div class="col-md-9">{{ $book->dewey ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">{{ __('Dimensions') }}</div>
                        <div class="col-md-9">{{ $book->dimensions ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Description') }}</h5>
                </div>
                <div class="card-body">
                    {{ $book->description ?? __('Aucune description disponible.') }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Statistiques') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Exemplaires totaux') }}
                            <span class="badge bg-primary rounded-pill">{{ $book->total_copies }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Disponibles') }}
                            <span class="badge bg-success rounded-pill">{{ $book->available_copies }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('Prêts cumulés') }}
                            <span class="badge bg-info rounded-pill">{{ $book->loan_count }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Exemplaires') }}</h5>
                    <!-- TODO: Add link to create copy -->
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($book->copies as $copy)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $copy->barcode }}</h6>
                                    <small class="text-{{ $copy->is_available ? 'success' : 'warning' }}">
                                        {{ $copy->status_label }}
                                    </small>
                                </div>
                                <p class="mb-1 small">{{ $copy->full_location }}</p>
                            </div>
                        @empty
                            <div class="list-group-item text-muted">{{ __('Aucun exemplaire.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-book"></i> {{ __('Catalogue des ouvrages') }}</h1>
        <div>
            <a href="{{ route('library.books.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> {{ __('Nouvel ouvrage') }}
            </a>
            <a href="{{ route('library.books.export.form') }}" class="btn btn-secondary">
                <i class="bi bi-download"></i> {{ __('Exporter') }}
            </a>
        </div>
    </div>

    <!-- Filtres de recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('library.books.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Recherche') }}</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="{{ __('Titre, auteur, ISBN...') }}"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Catégorie') }}</label>
                    <select name="category_id" class="form-select">
                        <option value="">{{ __('Toutes les catégories') }}</option>
                        <!-- Categories à remplir dynamiquement -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Statut') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('Tous') }}</option>
                        <option value="available">{{ __('Disponible') }}</option>
                        <option value="borrowed">{{ __('Emprunté') }}</option>
                        <option value="reserved">{{ __('Réservé') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> {{ __('Rechercher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des ouvrages -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Cote') }}</th>
                            <th>{{ __('Titre') }}</th>
                            <th>{{ __('Auteur(s)') }}</th>
                            <th>{{ __('ISBN') }}</th>
                            <th>{{ __('Catégorie') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @forelse($books as $book)
                        <tr>
                            <td>{{ $book->code }}</td>
                            <td>{{ $book->title }}</td>
                            <td>{{ $book->authors }}</td>
                            <td>{{ $book->isbn }}</td>
                            <td>{{ $book->category->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $book->status_color }}">
                                    {{ $book->status_label }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('library.books.show', $book) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('library.books.edit', $book) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @empty --}}
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                {{ __('Aucun ouvrage trouvé') }}
                            </td>
                        </tr>
                        {{-- @endforelse --}}
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            {{-- <div class="mt-3">
                {{ $books->links() }}
            </div> --}}
        </div>
    </div>
</div>
@endsection

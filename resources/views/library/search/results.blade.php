3@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-search"></i> {{ __('Résultats de recherche') }}</h1>
        <div>
            <a href="{{ route('library.search.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Nouvelle recherche') }}
            </a>
            <a href="{{ route('library.search.advanced') }}" class="btn btn-outline-secondary">
                <i class="bi bi-funnel"></i> {{ __('Recherche avancée') }}
            </a>
        </div>
    </div>

    @if(isset($query))
        <div class="alert alert-info">
            {{ __('Résultats pour :') }} <strong>{{ $query }}</strong>
        </div>
    @endif

    @if(empty($results['books']) && empty($results['authors']) && empty($results['periodicals']))
        <div class="alert alert-warning">
            {{ __('Aucun résultat trouvé.') }}
        </div>
    @endif

    @if(!empty($results['books']) && count($results['books']) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-book"></i> {{ __('Livres') }} ({{ count($results['books']) }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Titre') }}</th>
                                <th>{{ __('Auteur(s)') }}</th>
                                <th>{{ __('Éditeur(s)') }}</th>
                                <th>{{ __('Année') }}</th>
                                <th>{{ __('ISBN') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['books'] as $book)
                            <tr>
                                <td>
                                    <a href="{{ route('library.books.show', $book->id) }}" class="fw-bold text-decoration-none">
                                        {{ $book->title }}
                                    </a>
                                    @if($book->subtitle)
                                        <br><small class="text-muted">{{ $book->subtitle }}</small>
                                    @endif
                                </td>
                                <td>{{ $book->authors_string }}</td>
                                <td>{{ $book->publishers->pluck('name')->join(', ') }}</td>
                                <td>{{ $book->publication_year }}</td>
                                <td>{{ $book->formatted_isbn }}</td>
                                <td>
                                    <a href="{{ route('library.books.show', $book->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($results['books'] instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-3">
                        {{ $results['books']->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(!empty($results['authors']) && count($results['authors']) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-person"></i> {{ __('Auteurs') }} ({{ count($results['authors']) }})</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($results['authors'] as $author)
                        <a href="{{ route('library.authors.show', $author->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            {{ $author->full_name }}
                            <span class="badge bg-primary rounded-pill">{{ $author->books_count }} {{ __('livres') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(!empty($results['periodicals']) && count($results['periodicals']) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-journal-text"></i> {{ __('Périodiques') }} ({{ count($results['periodicals']) }})</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($results['periodicals'] as $periodical)
                        <a href="#" class="list-group-item list-group-item-action">
                            {{ $periodical->title }}
                            <small class="text-muted">({{ $periodical->issn }})</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

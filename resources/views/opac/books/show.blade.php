@extends('opac.layouts.app')

@section('title', $book->title . ' - OPAC')

@section('content')
<div class="container my-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('opac.index') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('opac.books.index') }}">{{ __('Books') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $book->title }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-3">
            <div class="opac-card mb-4 text-center p-4">
                <i class="fas fa-book fa-8x text-muted mb-3"></i>
                @if($book->is_available)
                    <div class="alert alert-success mb-0 py-2">
                        <i class="fas fa-check-circle me-1"></i> {{ __('Available') }}
                    </div>
                @else
                    <div class="alert alert-secondary mb-0 py-2">
                        <i class="fas fa-times-circle me-1"></i> {{ __('Unavailable') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-9">
            <div class="opac-card mb-4">
                <div class="card-body">
                    <h1 class="h2 mb-2">{{ $book->title }}</h1>
                    @if($book->subtitle)
                        <h2 class="h4 text-muted mb-3">{{ $book->subtitle }}</h2>
                    @endif

                    <p class="lead mb-4">
                        {{ __('By') }} <strong>{{ $book->authors_string }}</strong>
                    </p>

                    <div class="mb-4">
                        <h5 class="border-bottom pb-2">{{ __('Description') }}</h5>
                        <p>{{ $book->description }}</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Details') }}</h5>
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="w-25">{{ __('ISBN') }}</th>
                                        <td>{{ $book->formatted_isbn }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Publisher') }}</th>
                                        <td>{{ $book->publishers->pluck('name')->join(', ') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Year') }}</th>
                                        <td>{{ $book->publication_year }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Edition') }}</th>
                                        <td>{{ $book->edition }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Pages') }}</th>
                                        <td>{{ $book->pages }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Classification') }}</h5>
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="w-25">{{ __('Dewey') }}</th>
                                        <td>{{ $book->dewey }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Language') }}</th>
                                        <td>{{ $book->language->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Format') }}</th>
                                        <td>{{ $book->format->name ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($book->subjects->count() > 0)
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">{{ __('Subjects') }}</h5>
                            <div>
                                @foreach($book->subjects as $subject)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $subject->term }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
    <h1>Term Translation Details</h1>

    <p><strong>{{ __('Term 1') }}:</strong> {{ $term->name }}</p>
    <p><strong>{{ __('Term 2') }}:</strong> {{ $termTranslation->term2->name }}</p>

    <a href="{{ route('term-translations.index', $term) }}" class="btn btn-secondary">{{ __('Back') }}</a>
@endsection

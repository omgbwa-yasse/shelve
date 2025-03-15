@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Term Equivalent Details') }}</h1>
    <div class="mt-3">
        <p><strong>{{ __('Term') }}:</strong> {{ $termEquivalent->term->name }}</p>
        <p><strong>{{ __('Child Term') }}:</strong> {{ $termEquivalent->child->name }}</p>
        <p><strong>{{ __('Equivalent Type') }}:</strong> {{ $termEquivalent->equivalentType->name }}</p>
    </div>
    <a href="{{ route('terms.term-relations.index', $termEquivalent->term) }}" class="btn btn-secondary">{{ __('Back') }}</a>
</div>
@endsection

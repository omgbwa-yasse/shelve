@extends('layouts.app')

@section('content')
    <h1>Term Equivalent Details</h1>

    <p><strong>Term 1:</strong> {{ $term->name }}</p>
    <p><strong>Term 2:</strong> {{ $termEquivalent->term2->name }}</p>

    <a href="{{ route('term-equivalents.index', $term) }}" class="btn btn-secondary">Back</a>
@endsection

@extends('layouts.app')

@section('content')
    <h1>Term Equivalent Details</h1>
    <p><strong>Term:</strong> {{ $termEquivalent->term->name }}</p>
    <p><strong>Child Term:</strong> {{ $termEquivalent->child->name }}</p>
    <p><strong>Equivalent Type:</strong> {{ $termEquivalent->relationType->name }}</p>
    <a href="{{ route('terms.term-relations.index', $termEquivalent->term) }}" class="btn btn-secondary">Back</a>
@endsection

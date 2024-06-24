@extends('layouts.app')

@section('content')
    <h1>Term Relation Details</h1>
    <p><strong>Term:</strong> {{ $termRelation->term->name }}</p>
    <p><strong>Child Term:</strong> {{ $termRelation->child->name }}</p>
    <p><strong>Relation Type:</strong> {{ $termRelation->relationType->name }}</p>
    <a href="{{ route('terms.term-relations.index', $termRelation->term) }}" class="btn btn-secondary">Back</a>
@endsection

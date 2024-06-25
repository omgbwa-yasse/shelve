@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Related Terms for "{{ $term->name }}"</h1>
    <a href="{{ route('term-related.create', $term) }}" class="btn btn-primary mb-3">Add Related Term</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($relatedTerms as $relatedTerm)
            <tr>
                <td>{{ $relatedTerm->relatedTerm->id }}</td>
                <td>{{ $relatedTerm->relatedTerm->name }}</td>
                <td>
                    <a href="{{ route('term-related.show', [$term, $relatedTerm]) }}" class="btn btn-sm btn-info">View</a>
                    <form action="{{ route('term-related.destroy', [$term, $relatedTerm]) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this related term?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

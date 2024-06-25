@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Related Term Details</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>ID</th>
                <td>{{ $relatedTerm->relatedTerm->id }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $relatedTerm->relatedTerm->name }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('term-related.index', $term) }}" class="btn btn-secondary">Back</a>
</div>
@endsection

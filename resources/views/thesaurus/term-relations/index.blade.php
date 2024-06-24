@extends('layouts.app')

@section('content')
    <h1>Term Relations for {{ $term->name }}</h1>
    <a href="{{ route('terms.term-relations.create', $term) }}" class="btn btn-primary">Create Term Relation</a>
    <table class="table">
        <thead>
            <tr>
                <th>Child Term</th>
                <th>Relation Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($termRelations as $termRelation)
                <tr>
                    <td>{{ $termRelation->child->name }}</td>
                    <td>{{ $termRelation->relationType->name }}</td>
                    <td>
                        <a href="{{ route('terms.term-relations.edit', [$term, $termRelation]) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('terms.term-relations.destroy', [$term, $termRelation]) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this term relation?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

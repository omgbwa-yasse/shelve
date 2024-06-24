@extends('layouts.app')

@section('content')
    <h1>Term Equivalents for {{ $term->name }}</h1>

    <a href="{{ route('term-equivalents.create', $term) }}" class="btn btn-primary">Add Term Equivalent</a>

    <table class="table">
        <thead>
            <tr>
                <th>Term 1</th>
                <th>Term 2</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($termEquivalents as $termEquivalent)
                <tr>
                    <td>{{ $term->name }}</td>
                    <td>{{ $termEquivalent->term2->name ?? ''}}</td>
                    <td>
                        <a href="{{ route('term-equivalents.edit', [$term, $termEquivalent]) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('term-equivalents.destroy', [$term, $termEquivalent]) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this term equivalent?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection



@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Language Details</h1>
        <table class="table">
            <tr>
                <th>Code</th>
                <td>{{ $language->code }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $language->name }}</td>
            </tr>
        </table>
        <a href="{{ route('languages.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('languages.edit', $language->id) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('languages.destroy', $language->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this language?')">Delete</button>
        </form>
    </div>
@endsection

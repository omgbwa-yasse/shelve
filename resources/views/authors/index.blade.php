@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <h2>Authors</h2>
    <a href="{{ route('mail-author.create') }}" class="btn btn-primary mb-3">Add New Author</a>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            {{ $message }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($authors as $author)
                <tr>
                    <td>{{ $author->id }}</td>
                    <td>{{ $author->authorType->name }}</td>
                    <td>{{ $author->name }}</td>
                    <td>

                        <a class="btn btn-info" href="{{ route('mail-author.show', $author) }}">Show</a>
                        <a class="btn btn-primary" href="{{ route('mail-author.edit', $author) }}">Edit</a>
                        <form action="{{ route('mail-author.destroy', $author) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    const authors = @json($authors);
</script>

@endsection

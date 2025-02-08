<!-- resources/views/bulletin-boards/posts/index.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des Posts</h1>
        <a href="{{ route('bulletin-boards.posts.create') }}" class="btn btn-primary mb-3">Créer un Post</a>
        <table class="table">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($posts as $post)
                <tr>
                    <td>{{ $post->name }}</td>
                    <td>{{ $post->description }}</td>
                    <td>{{ $post->status }}</td>
                    <td>
                        <a href="{{ route('bulletin-boards.posts.show', $post) }}" class="btn btn-info">Voir</a>
                        <a href="{{ route('bulletin-boards.posts.edit', $post) }}" class="btn btn-warning">Éditer</a>
                        <form action="{{ route('bulletin-boards.posts.destroy', $post) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $posts->links() }}
    </div>
@endsection

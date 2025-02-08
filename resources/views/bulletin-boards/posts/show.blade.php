<!-- resources/views/bulletin-boards/posts/show.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $post->name }}</h1>
        <p>{{ $post->description }}</p>
        <p><strong>Statut:</strong> {{ $post->status }}</p>
        <p><strong>Créé par:</strong> {{ $post->user->name }}</p>
        <h2>Organisations</h2>
        <ul>
            @foreach($post->bulletinBoard->organisations as $organisation)
                <li>{{ $organisation->name }}</li>
            @endforeach
        </ul>
        <h2>Pièces Jointes</h2>
        <ul>
            @foreach($post->bulletinBoard->attachments as $attachment)
                <li><a href="{{ route('bulletin-boards.attachments.download', $attachment) }}">{{ $attachment->name }}</a></li>
            @endforeach
        </ul>
        <a href="{{ route('bulletin-boards.posts.edit', $post) }}" class="btn btn-warning">Éditer</a>
        <form action="{{ route('bulletin-boards.posts.destroy', $post) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
    </div>
@endsection

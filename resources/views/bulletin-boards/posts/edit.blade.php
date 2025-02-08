<!-- resources/views/bulletin-boards/posts/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Éditer le Post</h1>
        <form action="{{ route('bulletin-boards.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" value="{{ $post->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" required>{{ $post->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="start_date">Date de Début</label>
                <input type="date" name="start_date" class="form-control" value="{{ $post->start_date }}">
            </div>
            <div class="form-group">
                <label for="end_date">Date de Fin</label>
                <input type="date" name="end_date" class="form-control" value="{{ $post->end_date }}">
            </div>
            <div class="form-group">
                <label for="status">Statut</label>
                <select name="status" class="form-control" required>
                    <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Publié</option>
                    <option value="cancelled" {{ $post->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div class="form-group">
                <label for="organisations">Organisations</label>
                <select name="organisations[]" class="form-control" multiple>
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ $post->bulletinBoard->organisations->contains($organisation) ? 'selected' : '' }}>
                            {{ $organisation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="attachments">Pièces Jointes</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
            </div>
            <button type="submit" class="btn btn-success">Mettre à jour</button>
        </form>
    </div>
@endsection

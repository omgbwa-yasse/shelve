<!-- resources/views/bulletin-boards/posts/create.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Créer un Post</h1>
        <form action="{{ route('bulletin-boards.posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Nom</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="start_date">Date de Début</label>
                <input type="date" name="start_date" class="form-control">
            </div>
            <div class="form-group">
                <label for="end_date">Date de Fin</label>
                <input type="date" name="end_date" class="form-control">
            </div>
            <div class="form-group">
                <label for="status">Statut</label>
                <select name="status" class="form-control" required>
                    <option value="draft">Brouillon</option>
                    <option value="published">Publié</option>
                    <option value="cancelled">Annulé</option>
                </select>
            </div>
            <div class="form-group">
                <label for="organisations">Organisations</label>
                <select name="organisations[]" class="form-control" multiple>
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="attachments">Pièces Jointes</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
            </div>
            <button type="submit" class="btn btn-success">Créer</button>
        </form>
    </div>
@endsection

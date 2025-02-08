<!-- resources/views/bulletin-boards/admin/settings.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Paramètres</h1>
        <form action="{{ route('bulletin-boards.admin.updateSettings') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="allow_comments">Autoriser les Commentaires</label>
                <select name="allow_comments" class="form-control">
                    <option value="1" {{ $settings['allow_comments'] ? 'selected' : '' }}>Oui</option>
                    <option value="0" {{ !$settings['allow_comments'] ? 'selected' : '' }}>Non</option>
                </select>
            </div>
            <div class="form-group">
                <label for="moderation_required">Modération Requise</label>
                <select name="moderation_required" class="form-control">
                    <option value="1" {{ $settings['moderation_required'] ? 'selected' : '' }}>Oui</option>
                    <option value="0" {{ !$settings['moderation_required'] ? 'selected' : '' }}>Non</option>
                </select>
            </div>
            <div class="form-group">
                <label for="max_file_size">Taille Maximale des Fichiers (MB)</label>
                <input type="number" name="max_file_size" class="form-control" value="{{ $settings['max_file_size'] }}">
            </div>
            <div class="form-group">
                <label for="allowed_file_types">Types de Fichiers Autorisés</label>
                <select name="allowed_file_types[]" class="form-control" multiple>
                    @foreach(['pdf', 'doc', 'docx', 'jpg', 'png'] as $type)
                        <option value="{{ $type }}" {{ in_array($type, $settings['allowed_file_types']) ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">Mettre à jour</button>
        </form>
    </div>
@endsection

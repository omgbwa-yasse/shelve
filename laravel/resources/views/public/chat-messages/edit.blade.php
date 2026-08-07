@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier le message</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('public.chat-messages.update', $message) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="chat_id" class="form-label">Discussion</label>
                            <select class="form-control @error('chat_id') is-invalid @enderror" id="chat_id" name="chat_id" required>
                                <option value="">Sélectionner une discussion</option>
                                @foreach($chats as $chat)
                                    <option value="{{ $chat->id }}" {{ (old('chat_id', $message->chat_id) == $chat->id) ? 'selected' : '' }}>
                                        {{ $chat->title ?? 'Discussion #' . $chat->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('chat_id')
                                <div class="invalid-feedback">
                                    {{ $errors->get('chat_id')[0] }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type de message</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required onchange="toggleMessageFields()">
                                <option value="text" {{ old('type', $message->type) == 'text' ? 'selected' : '' }}>Texte</option>
                                <option value="file" {{ old('type', $message->type) == 'file' ? 'selected' : '' }}>Fichier</option>
                                <option value="image" {{ old('type', $message->type) == 'image' ? 'selected' : '' }}>Image</option>
                                <option value="system" {{ old('type', $message->type) == 'system' ? 'selected' : '' }}>Message système</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">
                                    {{ $errors->get('type')[0] }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3" id="content-field">
                            <label for="content" class="form-label">Contenu du message</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="5" required>{{ old('content', $message->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">
                                    {{ $errors->get('content')[0] }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3" id="file-field" style="display: none;">
                            <label for="file" class="form-label">Nouveau fichier (optionnel)</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            @if($message->file_path)
                                <small class="form-text text-muted">Fichier actuel : {{ basename($message->file_path) }}</small>
                            @endif
                            @error('file')
                                <div class="invalid-feedback">
                                    {{ $errors->get('file')[0] }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Répondre à un message (optionnel)</label>
                            <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">Aucun (nouveau message)</option>
                                @foreach($recentMessages as $recentMessage)
                                    <option value="{{ $recentMessage->id }}" {{ (old('parent_id', $message->parent_id) == $recentMessage->id) ? 'selected' : '' }}>
                                        {{ Str::limit($recentMessage->content, 50) }} - {{ $recentMessage->user->name ?? 'Inconnu' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">
                                    {{ $errors->get('parent_id')[0] }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('public.chat-messages.index') }}" class="btn btn-secondary">Retour</a>
                            <button type="submit" class="btn btn-primary">Modifier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMessageFields() {
    const type = document.getElementById('type').value;
    const contentField = document.getElementById('content-field');
    const fileField = document.getElementById('file-field');
    const contentTextarea = document.getElementById('content');

    if (type === 'file' || type === 'image') {
        fileField.style.display = 'block';
        contentTextarea.required = false;
        contentField.querySelector('label').textContent = 'Description (optionnelle)';
    } else {
        fileField.style.display = 'none';
        contentTextarea.required = true;
        contentField.querySelector('label').textContent = 'Contenu du message';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', toggleMessageFields);
</script>
@endsection

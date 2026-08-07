@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Ajouter un participant</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('public.chat-participants.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="chat_id" class="form-label">Discussion</label>
                            <select class="form-control @error('chat_id') is-invalid @enderror" id="chat_id" name="chat_id" required>
                                <option value="">Sélectionner une discussion</option>
                                @foreach($chats as $chat)
                                    <option value="{{ $chat->id }}" {{ old('chat_id') == $chat->id ? 'selected' : '' }}>
                                        {{ $chat->title ?? 'Discussion #' . $chat->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('chat_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Utilisateur</label>
                            <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                <option value="">Sélectionner un utilisateur</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="member" {{ old('role') == 'member' ? 'selected' : '' }}>Membre</option>
                                <option value="moderator" {{ old('role') == 'moderator' ? 'selected' : '' }}>Modérateur</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Participant actif
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('public.chat-participants.index') }}" class="btn btn-secondary">Retour</a>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

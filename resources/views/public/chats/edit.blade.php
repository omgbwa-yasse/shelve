@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier le chat</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.chats.update', $publicChat) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="title">Titre</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $publicChat->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_group" name="is_group"
                                       value="1" {{ old('is_group', $publicChat->is_group) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_group">Chat de groupe</label>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', $publicChat->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Actif</label>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="user_ids">Participants</label>
                            <select class="form-control @error('user_ids') is-invalid @enderror"
                                    id="user_ids" name="user_ids[]" multiple required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ in_array($user->id, old('user_ids', $participants)) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_ids')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="{{ route('public.chats.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

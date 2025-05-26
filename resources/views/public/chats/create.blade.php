@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Nouvelle conversation</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('public.chats.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Titre</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="participants" class="form-label">Participants</label>
                            <select class="form-select @error('participants') is-invalid @enderror"
                                    id="participants" name="participants[]" multiple required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, old('participants', [])) ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('participants')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Maintenez la touche Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs participants
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="initial_message" class="form-label">Message initial</label>
                            <textarea class="form-control @error('initial_message') is-invalid @enderror"
                                      id="initial_message" name="initial_message" rows="3" required>{{ old('initial_message') }}</textarea>
                            @error('initial_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('public.chats.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Démarrer la conversation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#participants').select2({
            placeholder: 'Sélectionnez les participants',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
@endsection

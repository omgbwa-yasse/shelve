@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Créer un nouvel événement</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.events.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="name">Nom de l'événement</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <x-textarea-editor
                                name="description"
                                id="description"
                                :value="old('description')"
                                :rows="8"
                                placeholder="Décrivez votre événement ici..."
                                :required="false"
                            />
                        </div>

                        <div class="form-group mb-3">
                            <label for="start_date">Date de début</label>
                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror"
                                   id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="end_date">Date de fin</label>
                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                            @error('end_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_online" name="is_online"
                                       value="1" {{ old('is_online') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_online">Événement en ligne</label>
                            </div>
                        </div>

                        <div class="form-group mb-3" id="location_group">
                            <label for="location">Lieu</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                   id="location" name="location" value="{{ old('location') }}">
                            @error('location')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3" id="online_link_group" style="display: none;">
                            <label for="online_link">Lien de l'événement en ligne</label>
                            <input type="url" class="form-control @error('online_link') is-invalid @enderror"
                                   id="online_link" name="online_link" value="{{ old('online_link') }}">
                            @error('online_link')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Créer l'événement</button>
                            <a href="{{ route('public.events.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isOnlineCheckbox = document.getElementById('is_online');
        const locationGroup = document.getElementById('location_group');
        const onlineLinkGroup = document.getElementById('online_link_group');

        function toggleLocationFields() {
            if (isOnlineCheckbox.checked) {
                locationGroup.style.display = 'none';
                onlineLinkGroup.style.display = 'block';
            } else {
                locationGroup.style.display = 'block';
                onlineLinkGroup.style.display = 'none';
            }
        }

        isOnlineCheckbox.addEventListener('change', toggleLocationFields);
        toggleLocationFields(); // Initial state
    });
</script>
@endpush
@endsection

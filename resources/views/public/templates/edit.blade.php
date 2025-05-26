@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier le modèle</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('public.templates.update', $template) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $template->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="email" {{ old('type', $template->type) === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="document" {{ old('type', $template->type) === 'document' ? 'selected' : '' }}>Document</option>
                                <option value="notification" {{ old('type', $template->type) === 'notification' ? 'selected' : '' }}>Notification</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $template->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Contenu</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10" required>{{ old('content', $template->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Vous pouvez utiliser les variables suivantes :<br>
                                {user_name} - Nom de l'utilisateur<br>
                                {date} - Date actuelle<br>
                                {time} - Heure actuelle
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('public.templates.show', $template) }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    tinymce.init({
        selector: '#content',
        height: 400,
        plugins: 'link lists table code',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link table | code',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }'
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Modifier le Prompt</h5>
                    <div>
                        <a href="{{ route('settings.prompts.show', $prompt) }}" class="btn btn-info btn-sm me-2">
                            <i class="fas fa-eye"></i> Voir détails
                        </a>
                        <a href="{{ route('settings.prompts.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('settings.prompts.update', $prompt) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="title">Titre</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $prompt->title) }}">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optionnel. Doit être unique pour votre organisation.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="content">Contenu du prompt</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10" required>{{ old('content', $prompt->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_system" name="is_system" value="1" {{ old('is_system', $prompt->is_system) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_system">Prompt système</label>
                            <small class="form-text text-muted d-block">Cochez si ce prompt doit être accessible à tous les utilisateurs de l'organisation.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="organisation">Organisation</label>
                            <input type="text" class="form-control" id="organisation" value="{{ $prompt->organisation ? $prompt->organisation->name : ($currentOrganisation ? $currentOrganisation->name : 'Global') }}" disabled>
                            <small class="form-text text-muted">Le prompt est associé à cette organisation.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="created_by">Créé par</label>
                            <input type="text" class="form-control" id="created_by" value="{{ $prompt->user ? $prompt->user->name : 'Système' }}" disabled>
                        </div>

                        <div class="form-group mb-3">
                            <label for="created_at">Date de création</label>
                            <input type="text" class="form-control" id="created_at" value="{{ $prompt->created_at->format('d/m/Y H:i') }}" disabled>
                        </div>

                        <div class="form-group text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Si vous souhaitez ajouter un éditeur enrichi comme CodeMirror ou un autre
        // C'est ici que vous l'initialiseriez
    });
</script>
@endpush

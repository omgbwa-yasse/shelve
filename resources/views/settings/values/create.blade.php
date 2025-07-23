@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Créer une nouvelle valeur</h5>
                    <a href="{{ route('settings.values.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.values.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="setting_id" class="form-label">Paramètre *</label>
                                    <select class="form-select @error('setting_id') is-invalid @enderror" id="setting_id" name="setting_id" required>
                                        <option value="">Sélectionner un paramètre</option>
                                        @foreach($settings as $setting)
                                            <option value="{{ $setting->id }}" {{ old('setting_id') == $setting->id ? 'selected' : '' }}>
                                                {{ $setting->category->name ?? 'Sans catégorie' }} - {{ $setting->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('setting_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Utilisateur (optionnel)</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                        <option value="">Sélectionner un utilisateur</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="organisation_id" class="form-label">Organisation (optionnel)</label>
                                    <select class="form-select @error('organisation_id') is-invalid @enderror" id="organisation_id" name="organisation_id">
                                        <option value="">Sélectionner une organisation</option>
                                        @foreach($organisations as $organisation)
                                            <option value="{{ $organisation->id }}" {{ old('organisation_id') == $organisation->id ? 'selected' : '' }}>
                                                {{ $organisation->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('organisation_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="value" class="form-label">Valeur *</label>
                                    <textarea class="form-control @error('value') is-invalid @enderror" id="value" name="value" rows="3" required placeholder="Entrez la valeur...">{{ old('value') }}</textarea>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        La valeur sera encodée automatiquement selon le type du paramètre.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer la valeur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour afficher les détails du paramètre sélectionné
document.getElementById('setting_id').addEventListener('change', function() {
    const settingId = this.value;
    if (settingId) {
        // Ici on pourrait faire un appel AJAX pour récupérer les détails du paramètre
        // et ajuster l'interface de saisie en conséquence
    }
});
</script>
@endsection

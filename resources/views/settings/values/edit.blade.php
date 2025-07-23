@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Modifier la valeur</h5>
                    <a href="{{ route('settings.values.show', $settingValue) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.values.update', $settingValue) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="setting_id" class="form-label">Paramètre *</label>
                                    <select class="form-select @error('setting_id') is-invalid @enderror" id="setting_id" name="setting_id" required>
                                        <option value="">Sélectionner un paramètre</option>
                                        @foreach($settings as $setting)
                                            <option value="{{ $setting->id }}"
                                                {{ (old('setting_id', $settingValue->setting_id) == $setting->id) ? 'selected' : '' }}>
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
                                            <option value="{{ $user->id }}"
                                                {{ (old('user_id', $settingValue->user_id) == $user->id) ? 'selected' : '' }}>
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
                                            <option value="{{ $organisation->id }}"
                                                {{ (old('organisation_id', $settingValue->organisation_id) == $organisation->id) ? 'selected' : '' }}>
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
                                    <textarea class="form-control @error('value') is-invalid @enderror" id="value" name="value" rows="3" required placeholder="Entrez la valeur...">{{ old('value', json_decode($settingValue->value)) }}</textarea>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        La valeur sera encodée automatiquement selon le type du paramètre.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Affichage des informations du paramètre actuel -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Paramètre actuel : {{ $settingValue->setting->label }}</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Type :</strong> <span class="badge bg-info">{{ $settingValue->setting->type }}</span>
                                            </div>
                                            <div class="col-md-8">
                                                <strong>Description :</strong> {{ $settingValue->setting->description ?? 'Aucune description' }}
                                            </div>
                                        </div>
                                        @if($settingValue->setting->default_value)
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <strong>Valeur par défaut :</strong>
                                                <code>{{ $settingValue->setting->default_value }}</code>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Annuler</button>
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

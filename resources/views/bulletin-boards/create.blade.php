@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Créer un nouveau tableau d'affichage</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('bulletin-boards.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du tableau</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="organisations" class="form-label">Organisations</label>
                            <select class="form-control select2-multiple @error('organisations') is-invalid @enderror"
                                    id="organisations"
                                    name="organisations[]"
                                    multiple>
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Si aucune organisation n'est sélectionnée, l'organisation actuelle sera utilisée par défaut.</small>
                            @error('organisations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('bulletin-boards.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Créer le tableau
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialisation de Select2 pour la sélection multiple d'organisations
        $('#organisations').select2({
            placeholder: 'Sélectionnez les organisations à associer',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Aucune organisation trouvée";
                },
                searching: function() {
                    return "Recherche en cours...";
                }
            }
        });

        // Préselectionner les valeurs si elles existent dans old()
        @if(old('organisations'))
            const oldValues = @json(old('organisations'));
            $('#organisations').val(oldValues).trigger('change');
        @endif
    });
</script>
@endpush
@endsection

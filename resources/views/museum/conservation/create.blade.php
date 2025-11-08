@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-plus-square"></i> {{ __('Nouveau rapport de conservation') }}</h1>
        <a href="{{ route('museum.conservation.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('museum.conservation.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="artifact_id" class="form-label">{{ __('Artefact') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('artifact_id') is-invalid @enderror" id="artifact_id" name="artifact_id" required>
                        <option value="">{{ __('Sélectionner un artefact...') }}</option>
                    </select>
                    @error('artifact_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="report_date" class="form-label">{{ __('Date du rapport') }} <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('report_date') is-invalid @enderror"
                           id="report_date" name="report_date" value="{{ old('report_date', now()->format('Y-m-d')) }}" required>
                    @error('report_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="condition" class="form-label">{{ __('État de conservation') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('condition') is-invalid @enderror" id="condition" name="condition" required>
                        <option value="">{{ __('Sélectionner...') }}</option>
                        <option value="excellent">{{ __('Excellent') }}</option>
                        <option value="good">{{ __('Bon') }}</option>
                        <option value="fair">{{ __('Moyen') }}</option>
                        <option value="poor">{{ __('Mauvais') }}</option>
                        <option value="critical">{{ __('Critique') }}</option>
                    </select>
                    @error('condition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="5">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('museum.conservation.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ __('Créer le rapport') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

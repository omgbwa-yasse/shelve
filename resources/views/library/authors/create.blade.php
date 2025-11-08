@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-person-plus-fill"></i> {{ __('Nouvel auteur') }}</h1>
        <a href="{{ route('library.authors.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour à la liste') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('library.authors.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">{{ __('Prénom') }}</label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                               id="first_name" name="first_name" value="{{ old('first_name') }}">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                               id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="birth_date" class="form-label">{{ __('Date de naissance') }}</label>
                        <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                               id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                        @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="death_date" class="form-label">{{ __('Date de décès') }}</label>
                        <input type="date" class="form-control @error('death_date') is-invalid @enderror"
                               id="death_date" name="death_date" value="{{ old('death_date') }}">
                        @error('death_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="nationality" class="form-label">{{ __('Nationalité') }}</label>
                        <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                               id="nationality" name="nationality" value="{{ old('nationality') }}">
                        @error('nationality')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <label for="biography" class="form-label">{{ __('Biographie') }}</label>
                        <textarea class="form-control @error('biography') is-invalid @enderror"
                                  id="biography" name="biography" rows="5">{{ old('biography') }}</textarea>
                        @error('biography')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('library.authors.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ __('Créer l\'auteur') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

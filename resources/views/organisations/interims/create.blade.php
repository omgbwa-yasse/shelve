@extends('layouts.app')

@section('content')
<div class="container shadow-sm p-4 bg-white rounded">
    <h1 class="mb-4 text-primary">Désigner un intérimaire</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organisation-interims.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="organisation_id" class="form-label">Service / Direction</label>
                <select name="organisation_id" id="organisation_id" class="form-select" required>
                    <option value="">Choisir une entité</option>
                    @foreach($organisations as $org)
                        <option value="{{ $org->id }}" {{ old('organisation_id') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }} ({{ $org->code }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="titular_user_id" class="form-label">Responsable titulaire</label>
                <select name="titular_user_id" id="titular_user_id" class="form-select" required>
                    <option value="">Choisir le titulaire</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('titular_user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} {{ $user->surname }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="interim_user_id" class="form-label">Intérimaire</label>
                <select name="interim_user_id" id="interim_user_id" class="form-select" required>
                    <option value="">Choisir l'intérimaire</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('interim_user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} {{ $user->surname }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="start_date" class="form-label">Début de l'intérim</label>
                <input type="date" name="start_date" id="start_date" class="form-control"
                       value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="end_date" class="form-label">Fin de l'intérim <span class="text-muted">(facultatif)</span></label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                <small class="form-text text-muted">Laisser vide pour un intérim sans date de fin définie.</small>
            </div>
        </div>

        <div class="mb-3">
            <label for="reason" class="form-label">Motif <span class="text-muted">(facultatif)</span></label>
            <textarea name="reason" id="reason" class="form-control" rows="3"
                      placeholder="Congé, mission, formation...">{{ old('reason') }}</textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('organisation-interims.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle"></i> Enregistrer l'intérim
            </button>
        </div>
    </form>
</div>
@endsection

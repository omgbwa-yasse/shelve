@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1><i class="bi bi-trash"></i> Nouvelle liste de déclassement</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('declassement-lists.store') }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" maxlength="20" required value="{{ old('code') }}">
                </div>
                <div class="col-md-9">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" maxlength="200" required value="{{ old('name') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="generate_from_query" value="1" id="generateFromQuery">
                        <label class="form-check-label" for="generateFromQuery">
                            Générer la liste depuis la requête d'élimination (sort = Élimination, délai de rétention écoulé)
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <label class="form-label">Filtrer par activité (optionnel)</label>
                    <select name="activity_id" class="form-select">
                        <option value="">Toutes les activités</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Les dossiers correspondants seront ajoutés automatiquement à la création de la liste.
                        Vous pourrez également <a href="{{ route('declassement-lists.eligible-records') }}" target="_blank">consulter les dossiers éligibles</a> avant de créer la liste.
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Créer la liste
            </button>
            <a href="{{ route('declassement-lists.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
@endsection

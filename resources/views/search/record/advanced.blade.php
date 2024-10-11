@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h1 class="mb-4">Recherche Avancée</h1>

    <strong>Cycle de vie aussi - authors -  terms (thésaurus) - Création -  Activité -   Boites - communicabilité - Délai de conservation </strong>

    <form method="GET" action="/search">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="name" class="form-label">Nom</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nom de l'enregistrement">
            </div>
            <div class="col-md-4">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" placeholder="Code de l'enregistrement">
            </div>
            <div class="col-md-4">
                <label for="date_exact" class="form-label">Date exacte</label>
                <input type="date" class="form-control" id="date_exact" name="date_exact">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="date_start" class="form-label">Date de début</label>
                <input type="date" class="form-control" id="date_start" name="date_start">
            </div>
            <div class="col-md-4">
                <label for="date_end" class="form-label">Date de fin</label>
                <input type="date" class="form-control" id="date_end" name="date_end">
            </div>
            <div class="col-md-4">
                <label for="status_id" class="form-label">Statut</label>
                <select class="form-select" id="status_id" name="status_id">
                    <option value="">Choisir un statut</option>
                    <!-- Les options devraient être générées dynamiquement depuis la base de données -->
                    <option value="1">Actif</option>
                    <option value="2">Inactif</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="support_id" class="form-label">Support</label>
                <select class="form-select" id="support_id" name="support_id">
                    <option value="">Choisir un support</option>
                    <!-- Les options devraient être générées dynamiquement -->
                </select>
            </div>
            <div class="col-md-4">
                <label for="level_id" class="form-label">Niveau</label>
                <select class="form-select" id="level_id" name="level_id">
                    <option value="">Choisir un niveau</option>
                    <!-- Les options devraient être générées dynamiquement -->
                </select>
            </div>
            <div class="col-md-4">
                <label for="organisation_id" class="form-label">Organisation</label>
                <select class="form-select" id="organisation_id" name="organisation_id">
                    <option value="">Choisir une organisation</option>
                    <!-- Les options devraient être générées dynamiquement -->
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="biographical_history" class="form-label">Histoire Biographique</label>
                <input type="text" class="form-control" id="biographical_history" name="biographical_history" placeholder="Histoire Biographique">
            </div>
            <div class="col-md-6">
                <label for="archival_history" class="form-label">Histoire Archivistique</label>
                <input type="text" class="form-control" id="archival_history" name="archival_history" placeholder="Histoire Archivistique">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="content" class="form-label">Contenu</label>
                <input type="text" class="form-control" id="content" name="content" placeholder="Description du contenu">
            </div>
            <div class="col-md-6">
                <label for="acquisition_source" class="form-label">Source d'Acquisition</label>
                <input type="text" class="form-control" id="acquisition_source" name="acquisition_source" placeholder="Source d'Acquisition">
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            <button type="reset" class="btn btn-secondary">Réinitialiser</button>
        </div>
    </form>
</div>
@endsection

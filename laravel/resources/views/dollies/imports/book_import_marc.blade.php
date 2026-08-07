@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-upload"></i> Importer des livres (format MARC)</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('dollies.action', ['categ' => 'book', 'action' => 'import-marc-process', 'id' => $dolly->id]) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="alert alert-info">
                            <h5>Format MARC21 attendu :</h5>
                            <p>Fichier texte avec les champs MARC suivants :</p>
                            <ul>
                                <li><strong>LDR :</strong> Leader (24 caractères)</li>
                                <li><strong>001 :</strong> Numéro de contrôle</li>
                                <li><strong>020 $$a :</strong> ISBN</li>
                                <li><strong>100 $$a :</strong> Auteur principal</li>
                                <li><strong>245 $$a :</strong> Titre</li>
                                <li><strong>250 $$a :</strong> Édition</li>
                                <li><strong>260 $$a $$b $$c :</strong> Publication (lieu, éditeur, année)</li>
                                <li><strong>300 $$a :</strong> Description physique</li>
                            </ul>
                            <strong>Exemple :</strong><br>
                            <code>
                                LDR 00000nam  2200000   4500<br>
                                001 123456789<br>
                                020   $$a2253096811<br>
                                100 1 $$aHugo, Victor<br>
                                245 10$$aLes Misérables<br>
                                250   $$aPremière édition<br>
                                260   $$aParis$$bLibrairie Générale Française$$c1985<br>
                                300   $$a1488 p.
                            </code>
                        </div>

                        <div class="mb-3">
                            <label for="marc_file" class="form-label">Fichier MARC (.mrc, .txt)</label>
                            <input type="file" class="form-control @error('marc_file') is-invalid @enderror"
                                   id="marc_file" name="marc_file" accept=".mrc,.txt" required>
                            @error('marc_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="format" class="form-label">Format MARC</label>
                            <select class="form-select" id="format" name="format">
                                <option value="text" selected>MARC texte lisible</option>
                                <option value="binary">MARC binaire (.mrc)</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dollies.show', $dolly->id) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Importer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

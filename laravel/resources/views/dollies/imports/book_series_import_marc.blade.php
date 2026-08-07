@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-upload"></i> Importer des séries d'éditeur (format MARC)</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('dollies.action', ['categ' => 'book_series', 'action' => 'import-marc-process', 'id' => $dolly->id]) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="alert alert-info">
                            <h5>Format MARC21 pour séries attendu :</h5>
                            <p>Fichier texte avec les champs MARC pour publications en série :</p>
                            <ul>
                                <li><strong>LDR :</strong> Leader (type 'nas' pour séries)</li>
                                <li><strong>001 :</strong> Numéro de contrôle</li>
                                <li><strong>022 $$a :</strong> ISSN</li>
                                <li><strong>245 $$a :</strong> Titre de la série</li>
                                <li><strong>260 $$a $$b $$c :</strong> Publication (lieu, éditeur, dates)</li>
                                <li><strong>490 $$a :</strong> Mention de collection</li>
                                <li><strong>500 $$a :</strong> Notes générales</li>
                            </ul>
                            <strong>Exemple :</strong><br>
                            <code>
                                LDR 00000nas  2200000   4500<br>
                                001 987654321<br>
                                022   $$a0768-0465<br>
                                245 00$$aBibliothèque de la Pléiade<br>
                                260   $$aParis$$bGallimard$$c1931-<br>
                                490 0 $$aCollection<br>
                                500   $$aEnviron 700 volumes publiés
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

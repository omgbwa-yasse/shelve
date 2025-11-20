@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-upload"></i> Importer des livres (format ISBD)</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('dollies.action', ['categ' => 'book', 'action' => 'import-isbd-process', 'id' => $dolly->id]) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="alert alert-info">
                            <h5>Format ISBD attendu :</h5>
                            <p>Chaque livre doit être séparé par une ligne vide. Format des zones :</p>
                            <ul>
                                <li><strong>Zone 1 :</strong> Titre : sous-titre / Responsabilité</li>
                                <li><strong>Zone 2 :</strong> Édition</li>
                                <li><strong>Zone 4 :</strong> Publication (Lieu : Éditeur, Année)</li>
                                <li><strong>Zone 5 :</strong> Description physique (pages)</li>
                                <li><strong>Zone 8 :</strong> ISBN</li>
                            </ul>
                            <strong>Exemple :</strong><br>
                            <code>
                                Les Misérables / Victor Hugo. - <br>
                                Première édition. - <br>
                                Paris : Librairie Générale Française, 1985. - <br>
                                1488 p. - <br>
                                ISBN 2-253-09681-1
                            </code>
                        </div>

                        <div class="mb-3">
                            <label for="isbd_file" class="form-label">Fichier ISBD (.txt)</label>
                            <input type="file" class="form-control @error('isbd_file') is-invalid @enderror"
                                   id="isbd_file" name="isbd_file" accept=".txt" required>
                            @error('isbd_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="encoding" class="form-label">Encodage du fichier</label>
                            <select class="form-select" id="encoding" name="encoding">
                                <option value="UTF-8" selected>UTF-8</option>
                                <option value="ISO-8859-1">ISO-8859-1 (Latin-1)</option>
                                <option value="Windows-1252">Windows-1252</option>
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

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-upload"></i> Importer des séries d'éditeur (format ISBD)</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('dollies.action', ['categ' => 'book_series', 'action' => 'import-isbd-process', 'id' => $dolly->id]) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="alert alert-info">
                            <h5>Format ISBD pour séries attendu :</h5>
                            <p>Chaque série doit être séparée par une ligne vide. Format :</p>
                            <ul>
                                <li><strong>Zone 1 :</strong> Titre de la série</li>
                                <li><strong>Zone 4 :</strong> Publication (Lieu : Éditeur, Date début)</li>
                                <li><strong>Zone 6 :</strong> Collection (nombre de volumes)</li>
                                <li><strong>Zone 8 :</strong> ISSN</li>
                            </ul>
                            <strong>Exemple :</strong><br>
                            <code>
                                Bibliothèque de la Pléiade. - <br>
                                Paris : Gallimard, 1931-. - <br>
                                (Collection ; 700 vol.). - <br>
                                ISSN 0768-0465
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

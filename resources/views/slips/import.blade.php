@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Gestion des imports</h1>
        <script>
            document.getElementById('format').addEventListener('change', function() {
                var form = document.getElementById('importForm');
                var format = this.value;
                form.action = "{{ route('slips.import', ['format' => '']) }}".replace(/\/$/, '') + '/' + format;
            });
        </script>
        <div class="card mb-4">
            <div class="card-header">Nouvel import</div>
            <div class="card-body">
                <form action="{{ route('slips.import', ['format' => '']) }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="mb-3">
                        <label for="format" class="form-label">Format d'import</label>
                        <select name="format" id="format" class="form-select" required>
                            <option value="">Choisissez un format</option>
                            <option value="excel">Excel</option>
                            <option value="ead">EAD (Encoded Archival Description)</option>
                            <option value="seda">SEDA (Standard d'échange de données pour l'archivage)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier à importer</label>
                        <input type="file" name="file" id="file" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="update_existing" id="update_existing">
                            <label class="form-check-label" for="update_existing">
                                Mettre à jour les enregistrements existants
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>

    </div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Import/Export de thésaurus **</h3>
                    <div class="card-tools">
                        <a href="{{ route('thesaurus.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Section Import -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Import</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('thesaurus.import-file') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group">
                                            <label for="file">Fichier à importer</label>
                                            <input type="file" name="file" id="file" class="form-control-file @error('file') is-invalid @enderror"
                                                   accept=".xml,.rdf,.csv,.json" required>
                                            <small class="form-text text-muted">Taille maximale autorisée: {{ ini_get('upload_max_filesize') }}</small>
                                            @error('file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="format">Format du fichier</label>
                                            <select name="format" id="format" class="form-control @error('format') is-invalid @enderror" required>
                                                <option value="">Sélectionnez un format</option>
                                                <option value="skos-rdf">SKOS/RDF (XML)</option>
                                                <option value="csv">CSV</option>
                                                <option value="json">JSON</option>
                                            </select>
                                            @error('format')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="scheme_id">Schéma de destination (optionnel)</label>
                                            <select name="scheme_id" id="scheme_id" class="form-control @error('scheme_id') is-invalid @enderror">
                                                <option value="">Créer un nouveau schéma</option>
                                                @foreach($schemes as $scheme)
                                                    <option value="{{ $scheme->id }}">{{ $scheme->formatted_title }}</option>
                                                @endforeach
                                            </select>
                                            @error('scheme_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="language">Langue par défaut</label>
                                            <select name="language" id="language" class="form-control @error('language') is-invalid @enderror">
                                                <option value="fr-fr">Français</option>
                                                <option value="en-us">Anglais</option>
                                                <option value="de-de">Allemand</option>
                                                <option value="es-es">Espagnol</option>
                                                <option value="it-it">Italien</option>
                                            </select>
                                            @error('language')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="merge_mode">Mode de fusion</label>
                                            <select name="merge_mode" id="merge_mode" class="form-control @error('merge_mode') is-invalid @enderror" required>
                                                <option value="append">Ajouter aux données existantes</option>
                                                <option value="merge">Fusionner avec les données existantes</option>
                                                <option value="replace">Remplacer les données existantes</option>
                                            </select>
                                            @error('merge_mode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-upload"></i> Importer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Section Export -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Export</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('thesaurus.export-scheme') }}">
                                        @csrf
                                        <div class="form-group">
                                            <label for="export_scheme_id">Schéma à exporter</label>
                                            <select name="scheme_id" id="export_scheme_id" class="form-control @error('scheme_id') is-invalid @enderror" required>
                                                <option value="">Sélectionnez un schéma</option>
                                                @foreach($schemes as $scheme)
                                                    <option value="{{ $scheme->id }}">{{ $scheme->formatted_title }}</option>
                                                @endforeach
                                            </select>
                                            @error('scheme_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="export_format">Format d'export</label>
                                            <select name="format" id="export_format" class="form-control @error('format') is-invalid @enderror" required>
                                                <option value="skos-rdf">SKOS/RDF (XML)</option>
                                                <option value="csv">CSV</option>
                                                <option value="json">JSON</option>
                                            </select>
                                            @error('format')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="export_language">Langue</label>
                                            <select name="language" id="export_language" class="form-control @error('language') is-invalid @enderror">
                                                <option value="">Toutes les langues</option>
                                                <option value="fr-fr">Français</option>
                                                <option value="en-us">Anglais</option>
                                                <option value="de-de">Allemand</option>
                                                <option value="es-es">Espagnol</option>
                                                <option value="it-it">Italien</option>
                                            </select>
                                            @error('language')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="include_relations" id="include_relations" class="form-check-input" checked>
                                            <label class="form-check-label" for="include_relations">
                                                Inclure les relations entre concepts
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-download"></i> Exporter
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historique des imports -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Historique des imports récents</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Fichier</th>
                                                    <th>Type</th>
                                                    <th>Statut</th>
                                                    <th>Traités</th>
                                                    <th>Créés</th>
                                                    <th>Erreurs</th>
                                                    <th>Message</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentImports as $import)
                                                    <tr>
                                                        <td>{{ Carbon\Carbon::parse($import->created_at)->format('d/m/Y H:i') }}</td>
                                                        <td>{{ $import->filename }}</td>
                                                        <td>
                                                            <span class="badge badge-info">{{ strtoupper($import->type) }}</span>
                                                        </td>
                                                        <td>
                                                            @switch($import->status)
                                                                @case('completed')
                                                                    <span class="badge badge-success">Terminé</span>
                                                                    @break
                                                                @case('failed')
                                                                    <span class="badge badge-danger">Échoué</span>
                                                                    @break
                                                                @case('processing')
                                                                    <span class="badge badge-warning">En cours</span>
                                                                    @break
                                                                @default
                                                                    <span class="badge badge-secondary">{{ $import->status }}</span>
                                                            @endswitch
                                                        </td>
                                                        <td>{{ $import->processed_items }}</td>
                                                        <td>{{ $import->created_items }}</td>
                                                        <td>{{ $import->error_items }}</td>
                                                        <td>{{ Str::limit($import->message, 50) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted">Aucun import trouvé</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

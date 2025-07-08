@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Module Thésaurus - Tableau de bord</h3>
                </div>
                <div class="card-body">
                    <!-- Statistiques générales -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['total_schemes'] }}</h5>
                                    <p class="card-text">Schémas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['total_concepts'] }}</h5>
                                    <p class="card-text">Concepts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['total_labels'] }}</h5>
                                    <p class="card-text">Labels</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['total_relations'] }}</h5>
                                    <p class="card-text">Relations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-white bg-secondary">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['records_with_concepts'] }}</h5>
                                    <p class="card-text">Records liés</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4>Actions rapides</h4>
                            <div class="btn-group" role="group">
                                <a href="{{ route('tool.thesaurus.import-export') }}" class="btn btn-primary">
                                    <i class="fas fa-exchange-alt"></i> Import/Export
                                </a>
                                <a href="{{ route('tool.thesaurus.record-concept-relations') }}" class="btn btn-success">
                                    <i class="fas fa-link"></i> Relations Records-Concepts
                                </a>
                                <a href="{{ route('tool.thesaurus.statistics') }}" class="btn btn-info">
                                    <i class="fas fa-chart-bar"></i> Statistiques détaillées
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des schémas -->
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Schémas de thésaurus</h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Identifiant</th>
                                            <th>Titre</th>
                                            <th>Langue</th>
                                            <th>Concepts</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($schemes as $scheme)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-primary">{{ $scheme->identifier }}</span>
                                                </td>
                                                <td>{{ $scheme->title }}</td>
                                                <td>{{ $scheme->language }}</td>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $scheme->concepts->count() }}</span>
                                                </td>
                                                <td>{{ Str::limit($scheme->description, 100) }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                                onclick="exportScheme({{ $scheme->id }})">
                                                            <i class="fas fa-download"></i> Export
                                                        </button>
                                                        <a href="{{ route('tool.thesaurus.record-concept-relations', ['scheme_id' => $scheme->id]) }}" 
                                                           class="btn btn-outline-success btn-sm">
                                                            <i class="fas fa-link"></i> Relations
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Aucun schéma trouvé</td>
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

<!-- Modal pour l'export de schéma -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('tool.thesaurus.export-scheme') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Exporter un schéma</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="scheme_id" id="export_scheme_id">
                    
                    <div class="form-group">
                        <label for="format">Format d'export</label>
                        <select name="format" id="format" class="form-control" required>
                            <option value="skos">SKOS RDF/XML</option>
                            <option value="rdf">RDF/XML</option>
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="language">Langue</label>
                        <select name="language" id="language" class="form-control">
                            <option value="fr-fr">Français</option>
                            <option value="en-us">Anglais</option>
                            <option value="">Toutes les langues</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="include_relations" id="include_relations" class="form-check-input" checked>
                        <label class="form-check-label" for="include_relations">
                            Inclure les relations entre concepts
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Exporter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function exportScheme(schemeId) {
    document.getElementById('export_scheme_id').value = schemeId;
    $('#exportModal').modal('show');
}
</script>
@endsection

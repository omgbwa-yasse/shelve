@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Historique des versions</h1>
            <p class="text-muted mb-0">{{ $currentDocument->name }}</p>
        </div>
        <div>
            <a href="{{ route('documents.show', $currentDocument) }}" class="btn btn-secondary">
                <i class="bi bi-eye"></i> Voir le document
            </a>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Code:</strong> <code>{{ $currentDocument->code }}</code>
                        </div>
                        <div class="col-md-3">
                            <strong>Type:</strong> {{ $currentDocument->type->name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Dossier:</strong>
                            <a href="{{ route('folders.show', $currentDocument->folder) }}">
                                {{ $currentDocument->folder->name }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <strong>Version actuelle:</strong>
                            <span class="badge bg-primary">v{{ $currentDocument->version_number }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Toutes les versions ({{ $allVersions->count() }})
            </h5>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadVersionModal">
                <i class="bi bi-upload"></i> Ajouter une nouvelle version
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%">Version</th>
                            <th style="width: 20%">Date</th>
                            <th style="width: 15%">Créé par</th>
                            <th style="width: 10%">Statut</th>
                            <th style="width: 10%">Signature</th>
                            <th style="width: 25%">Notes</th>
                            <th style="width: 10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allVersions as $version)
                            <tr class="{{ $version->is_current_version ? 'table-primary' : '' }}">
                                <td>
                                    <span class="badge {{ $version->is_current_version ? 'bg-success' : 'bg-secondary' }}">
                                        v{{ $version->version_number }}
                                    </span>
                                    @if($version->is_current_version)
                                        <small class="text-muted">(actuelle)</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $version->created_at->format('d/m/Y H:i') }}
                                    <br>
                                    <small class="text-muted">{{ $version->created_at->diffForHumans() }}</small>
                                </td>
                                <td>{{ $version->creator->name ?? 'Inconnu' }}</td>
                                <td>
                                    @switch($version->status)
                                        @case('draft')
                                            <span class="badge bg-secondary">Brouillon</span>
                                            @break
                                        @case('active')
                                            <span class="badge bg-success">Actif</span>
                                            @break
                                        @case('archived')
                                            <span class="badge bg-warning">Archivé</span>
                                            @break
                                        @case('obsolete')
                                            <span class="badge bg-danger">Obsolète</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @switch($version->signature_status)
                                        @case('unsigned')
                                            <i class="bi bi-pencil text-secondary"></i> Non signé
                                            @break
                                        @case('pending')
                                            <i class="bi bi-clock text-warning"></i> En attente
                                            @break
                                        @case('signed')
                                            <i class="bi bi-shield-check text-success"></i> Signé
                                            @break
                                        @case('rejected')
                                            <i class="bi bi-x-circle text-danger"></i> Rejeté
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($version->version_notes)
                                        {{ Str::limit($version->version_notes, 50) }}
                                    @else
                                        <span class="text-muted">Aucune note</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('documents.show', $version) }}" class="btn btn-outline-primary" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('documents.versions.download', [$currentDocument, $version]) }}" class="btn btn-outline-success" title="Télécharger">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        @if(!$version->is_current_version && Gate::allows('update', $version))
                                            <button type="button" class="btn btn-outline-warning" title="Restaurer cette version"
                                                    onclick="if(confirm('Voulez-vous restaurer cette version comme version actuelle ?')) { alert('Fonctionnalité à implémenter'); }">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Aucune version trouvée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Comparaison de versions -->
    <div class="card mt-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-arrows-expand"></i> Comparaison de versions
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <label for="version1" class="form-label">Version 1</label>
                    <select id="version1" class="form-select">
                        <option value="">-- Sélectionner une version --</option>
                        @foreach($allVersions as $version)
                            <option value="{{ $version->id }}">
                                v{{ $version->version_number }} - {{ $version->created_at->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-center d-flex align-items-end justify-content-center">
                    <button type="button" class="btn btn-primary" onclick="compareVersions()">
                        <i class="bi bi-arrow-left-right"></i> Comparer
                    </button>
                </div>
                <div class="col-md-5">
                    <label for="version2" class="form-label">Version 2</label>
                    <select id="version2" class="form-select">
                        <option value="">-- Sélectionner une version --</option>
                        @foreach($allVersions as $version)
                            <option value="{{ $version->id }}" {{ $version->is_current_version ? 'selected' : '' }}>
                                v{{ $version->version_number }} - {{ $version->created_at->format('d/m/Y H:i') }}
                                {{ $version->is_current_version ? '(actuelle)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div id="comparisonResult" class="mt-3" style="display: none;">
                <div class="alert alert-info">
                    La comparaison de versions sera affichée ici.
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des versions -->
    <div class="card mt-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-graph-up"></i> Statistiques
            </h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h3 class="text-primary">{{ $allVersions->count() }}</h3>
                    <p class="text-muted mb-0">Versions totales</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-success">{{ $allVersions->where('signature_status', 'signed')->count() }}</h3>
                    <p class="text-muted mb-0">Versions signées</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-warning">{{ $allVersions->where('is_archived', true)->count() }}</h3>
                    <p class="text-muted mb-0">Versions archivées</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-info">{{ number_format($allVersions->sum(fn($v) => $v->attachment?->size ?? 0) / 1024 / 1024, 2) }} MB</h3>
                    <p class="text-muted mb-0">Taille totale</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une nouvelle version -->
<div class="modal fade" id="uploadVersionModal" tabindex="-1" aria-labelledby="uploadVersionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.upload', $currentDocument) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadVersionModalLabel">Ajouter une nouvelle version</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Version actuelle: <strong>v{{ $currentDocument->version_number }}</strong><br>
                        Nouvelle version: <strong>v{{ $currentDocument->version_number + 1 }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror"
                               id="file" name="file" required>
                        <small class="form-text text-muted">
                            Formats acceptés: PDF, Word, Excel, Images. Taille max: 10 MB
                        </small>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="version_notes" class="form-label">Notes de version</label>
                        <textarea class="form-control @error('version_notes') is-invalid @enderror"
                                  id="version_notes" name="version_notes" rows="3"
                                  placeholder="Décrivez les modifications apportées dans cette version...">{{ old('version_notes') }}</textarea>
                        @error('version_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Téléverser
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function compareVersions() {
    const version1 = document.getElementById('version1').value;
    const version2 = document.getElementById('version2').value;

    if (!version1 || !version2) {
        alert('Veuillez sélectionner deux versions à comparer');
        return;
    }

    if (version1 === version2) {
        alert('Veuillez sélectionner deux versions différentes');
        return;
    }

    // TODO: Implémenter la comparaison via AJAX
    const resultDiv = document.getElementById('comparisonResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = `
        <div class="alert alert-warning">
            <strong>Fonctionnalité à implémenter:</strong>
            Comparaison entre les versions ${version1} et ${version2}
        </div>
    `;
}
</script>
@endpush
@endsection

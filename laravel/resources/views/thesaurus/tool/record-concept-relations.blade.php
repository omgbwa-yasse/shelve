@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Relations Records - Concepts du thésaurus</h3>
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

                    <!-- Formulaire d'association automatique -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Association automatique</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('thesaurus.auto-associate-concepts') }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="scheme_id">Schéma de référence</label>
                                                    <select name="scheme_id" id="scheme_id" class="form-control">
                                                        <option value="">Tous les schémas</option>
                                                        @foreach($schemes as $scheme)
                                                            <option value="{{ $scheme->id }}">{{ $scheme->formatted_title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="min_weight">Poids minimum</label>
                                                    <input type="number" name="min_weight" id="min_weight" class="form-control" 
                                                           value="0.5" min="0.1" max="1.0" step="0.1">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="max_concepts">Max concepts</label>
                                                    <input type="number" name="max_concepts" id="max_concepts" class="form-control" 
                                                           value="5" min="1" max="10">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="overwrite" id="overwrite" class="form-check-input">
                                                        <label class="form-check-label" for="overwrite">
                                                            Écraser existant
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-primary form-control">
                                                        <i class="fas fa-magic"></i> Associer automatiquement
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('thesaurus.record-concept-relations') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filter_scheme_id">Filtrer par schéma</label>
                                            <select name="scheme_id" id="filter_scheme_id" class="form-control">
                                                <option value="">Tous les schémas</option>
                                                @foreach($schemes as $scheme)
                                                    <option value="{{ $scheme->id }}" 
                                                            {{ request('scheme_id') == $scheme->id ? 'selected' : '' }}>
                                                        {{ $scheme->formatted_title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="min_weight">Poids minimum</label>
                                            <input type="number" name="min_weight" id="min_weight" class="form-control" 
                                                   value="{{ request('min_weight', 0.0) }}" min="0.0" max="1.0" step="0.1">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="search">Rechercher</label>
                                            <input type="text" name="search" id="search" class="form-control" 
                                                   value="{{ request('search') }}" placeholder="Nom du record ou concept...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-info form-control">
                                                <i class="fas fa-search"></i> Filtrer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Liste des relations -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Record</th>
                                            <th>Concepts associés</th>
                                            <th>Nombre</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($records as $record)
                                            <tr>
                                                <td>
                                                    <strong>{{ $record->name }}</strong><br>
                                                    <small class="text-muted">{{ $record->code }}</small>
                                                </td>
                                                <td>
                                                    @foreach($record->thesaurusConcepts as $concept)
                                                        @php
                                                            $prefLabel = $concept->labels()->where('label_type', 'prefLabel')->first();
                                                            $weight = $concept->pivot->weight;
                                                            $context = $concept->pivot->context;
                                                        @endphp
                                                        <div class="mb-1">
                                                            <span class="badge badge-{{ $weight >= 0.7 ? 'success' : 'secondary' }}">
                                                                {{ $prefLabel ? $prefLabel->literal_form : 'Sans label' }}
                                                            </span>
                                                            <small class="text-muted">
                                                                ({{ number_format($weight, 2) }} - {{ $context }})
                                                            </small>
                                                            @if($concept->scheme)
                                                                <small class="text-info">[{{ $concept->scheme->identifier }}]</small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $record->thesaurusConcepts->count() }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('records.show', $record->id) }}" 
                                                           class="btn btn-outline-info btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-warning btn-sm" 
                                                                onclick="reassociateConcepts({{ $record->id }})">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                onclick="removeConcepts({{ $record->id }})">
                                                            <i class="fas fa-unlink"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Aucun record trouvé</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $records->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function reassociateConcepts(recordId) {
    if (confirm('Voulez-vous recalculer les associations de concepts pour ce record ?')) {
        // Créer un formulaire pour la réassociation
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("thesaurus.auto-associate-concepts") }}';
        
        let csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        let recordIds = document.createElement('input');
        recordIds.type = 'hidden';
        recordIds.name = 'record_ids[]';
        recordIds.value = recordId;
        
        let overwrite = document.createElement('input');
        overwrite.type = 'hidden';
        overwrite.name = 'overwrite';
        overwrite.value = '1';
        
        form.appendChild(csrfToken);
        form.appendChild(recordIds);
        form.appendChild(overwrite);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function removeConcepts(recordId) {
    if (confirm('Voulez-vous supprimer toutes les associations de concepts pour ce record ?')) {
        // Implémentation pour supprimer les associations
        alert('Fonction non encore implémentée');
    }
}
</script>
@endsection

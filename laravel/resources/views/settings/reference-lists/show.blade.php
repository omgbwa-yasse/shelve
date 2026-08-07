@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">{{ $referenceList->name }}</h1>
            <p class="text-muted">
                <code class="bg-light px-2 py-1 rounded">{{ $referenceList->code }}</code>
                @if($referenceList->description) — {{ $referenceList->description }} @endif
                @if($referenceList->linkedSchema)
                    <span class="badge bg-info">Schéma lié : {{ $referenceList->linkedSchema->name }}</span>
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('settings.reference-lists.edit', $referenceList) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <a href="{{ route('settings.reference-lists.index') }}" class="btn btn-outline-secondary">Retour</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('import_errors') && count(session('import_errors')))
        <div class="alert alert-warning">
            <strong>Lignes en erreur lors de l'import :</strong>
            <ul class="mb-0">
                @foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <!-- Import / Export (étape 7) -->
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-arrow-repeat"></i> Import / Export en masse</div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('settings.reference-lists.values.import', $referenceList) }}" enctype="multipart/form-data" class="d-flex gap-2">
                        @csrf
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <button class="btn btn-primary text-nowrap"><i class="bi bi-upload"></i> Importer</button>
                    </form>
                    <small class="text-muted">Gabarit : code | value | description | active (1re ligne = en-têtes)</small>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <a href="{{ route('settings.reference-lists.values.export', $referenceList) }}" class="btn btn-outline-success">
                        <i class="bi bi-download"></i> Exporter (.xlsx)
                    </a>
                    <form method="POST" action="{{ route('settings.reference-lists.purge-inactive', $referenceList) }}"
                          onsubmit="return confirm('Supprimer toutes les valeurs désactivées non utilisées ?');">
                        @csrf
                        <button class="btn btn-outline-danger text-nowrap"><i class="bi bi-trash"></i> Supprimer les désactivés non utilisés</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="valuesTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#active">Actives
                ({{ $referenceList->activeValues->count() }})</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#inactive">Inactives
                ({{ $referenceList->values->count() }})</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#trashed">Corbeille
                ({{ $trashedValues->count() }})</a>
        </li>
    </ul>

    <div class="tab-content">
        @php
            $tabs = [
                'active' => $referenceList->activeValues,
                'inactive' => $referenceList->values,
            ];
        @endphp
        @foreach($tabs as $tabKey => $tabValues)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tabKey }}">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px">Code</th>
                                    <th>Valeur</th>
                                    <th>Description</th>
                                    <th>Propriétés</th>
                                    <th style="width: 170px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tabValues as $value)
                                    <tr>
                                        <td><code class="bg-light px-2 py-1 rounded">{{ $value->code }}</code></td>
                                        <td>{{ $value->value }}</td>
                                        <td>{{ $value->description }}</td>
                                        <td>
                                            @if($value->extra_attributes)
                                                <span class="badge bg-light text-dark border" title="{{ json_encode($value->extra_attributes) }}">
                                                    <i class="bi bi-sliders"></i> {{ count($value->extra_attributes) }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('settings.reference-lists.values.update', [$referenceList, $value]) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                        title="Basculer actif/inactif">
                                                    <i class="bi bi-toggle-{{ $value->active ? 'on' : 'off' }}"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('settings.reference-lists.values.destroy', [$referenceList, $value]) }}" class="d-inline"
                                                  onsubmit="return confirm('Supprimer cette valeur ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">Aucune valeur {{ $tabKey === 'active' ? 'active' : 'inactive' }} dans ce domaine</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($tabKey === 'active')
                        <div class="card-footer bg-light">
                            <form method="POST" action="{{ route('settings.reference-lists.values.store', $referenceList) }}" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Valeur <span class="text-danger">*</span></label>
                                    <input type="text" name="value" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control">
                                </div>
                                <div class="col-md-2 d-grid">
                                    <button class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ajouter</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="tab-pane fade" id="trashed">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px">Code</th>
                                <th>Valeur</th>
                                <th>Description</th>
                                <th style="width: 170px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trashedValues as $value)
                                <tr>
                                    <td><code class="bg-light px-2 py-1 rounded">{{ $value->code }}</code></td>
                                    <td>{{ $value->value }}</td>
                                    <td>{{ $value->description }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('settings.reference-lists.values.restore', [$referenceList, $value]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i> Restaurer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <p class="text-muted mb-0">Corbeille vide</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

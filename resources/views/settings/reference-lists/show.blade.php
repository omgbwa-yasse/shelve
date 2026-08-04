@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">{{ $referenceList->name }}</h1>
            <p class="text-muted">
                <code class="bg-light px-2 py-1 rounded">{{ $referenceList->code }}</code>
                @if($referenceList->description) — {{ $referenceList->description }} @endif
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
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-list-check"></i> Valeurs du domaine
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 150px">Code</th>
                        <th>Valeur</th>
                        <th>Description</th>
                        <th style="width: 100px">Statut</th>
                        <th style="width: 150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referenceList->values as $value)
                        <tr>
                            <td><code class="bg-light px-2 py-1 rounded">{{ $value->code }}</code></td>
                            <td>{{ $value->value }}</td>
                            <td>{{ $value->description }}</td>
                            <td>
                                @if($value->active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('settings.reference-lists.values.update', [$referenceList, $value]) }}" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                            title="Basculer actif/inactif" onclick="this.closest('form').submit()">
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
                                <p class="text-muted mb-0">Aucune valeur dans ce domaine</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
    </div>
</div>
@endsection

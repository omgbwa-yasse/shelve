@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-diagram-3"></i> {{ __('Définitions de Workflow') }}</h1>
        <a href="{{ route('workflows.definitions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> {{ __('Nouvelle définition') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Total') }}</h5>
                    <p class="card-text display-4">{{ $definitions->total() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Actifs') }}</h5>
                    <p class="card-text display-4">{{ $definitions->where('status', 'active')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Brouillons') }}</h5>
                    <p class="card-text display-4">{{ $definitions->where('status', 'draft')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Archivés') }}</h5>
                    <p class="card-text display-4">{{ $definitions->where('status', 'archived')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des définitions -->
    <div class="card">
        <div class="card-body">
            @if($definitions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Nom') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Version') }}</th>
                                <th>{{ __('Statut') }}</th>
                                <th>{{ __('Créé par') }}</th>
                                <th>{{ __('Date création') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($definitions as $definition)
                                <tr>
                                    <td>
                                        <strong>{{ $definition->name }}</strong>
                                    </td>
                                    <td>{{ Str::limit($definition->description, 50) }}</td>
                                    <td>
                                        <span class="badge bg-info">v{{ $definition->version }}</span>
                                    </td>
                                    <td>
                                        @switch($definition->status)
                                            @case('active')
                                                <span class="badge bg-success">{{ __('Actif') }}</span>
                                                @break
                                            @case('draft')
                                                <span class="badge bg-warning">{{ __('Brouillon') }}</span>
                                                @break
                                            @case('archived')
                                                <span class="badge bg-secondary">{{ __('Archivé') }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $definition->creator->name ?? 'N/A' }}</td>
                                    <td>{{ $definition->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('workflows.definitions.show', $definition) }}" class="btn btn-info" title="{{ __('Voir') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('workflows.definitions.edit', $definition) }}" class="btn btn-warning" title="{{ __('Modifier') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('workflows.definitions.destroy', $definition) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette définition ?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="{{ __('Supprimer') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $definitions->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-diagram-3 text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">{{ __('Aucune définition de workflow trouvée.') }}</p>
                    <a href="{{ route('workflows.definitions.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> {{ __('Créer la première définition') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

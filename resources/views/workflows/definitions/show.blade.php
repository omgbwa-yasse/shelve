@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-diagram-3"></i> {{ $definition->name }}</h1>
        <div>
            <a href="{{ route('workflows.definitions.configuration.edit', $definition) }}" class="btn btn-primary">
                <i class="bi bi-diagram-2"></i> {{ __('Configurer BPMN') }}
            </a>
            <a href="{{ route('workflows.definitions.edit', $definition) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> {{ __('Modifier') }}
            </a>
            <a href="{{ route('workflows.definitions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informations générales -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informations générales') }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __('Nom') }}</dt>
                        <dd class="col-sm-9">{{ $definition->name }}</dd>

                        <dt class="col-sm-3">{{ __('Description') }}</dt>
                        <dd class="col-sm-9">{{ $definition->description ?: __('Aucune description') }}</dd>

                        <dt class="col-sm-3">{{ __('Statut') }}</dt>
                        <dd class="col-sm-9">
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
                        </dd>

                        <dt class="col-sm-3">{{ __('Version') }}</dt>
                        <dd class="col-sm-9"><span class="badge bg-info">v{{ $definition->version }}</span></dd>

                        <dt class="col-sm-3">{{ __('Créé par') }}</dt>
                        <dd class="col-sm-9">{{ $definition->creator->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">{{ __('Date création') }}</dt>
                        <dd class="col-sm-9">{{ $definition->created_at->format('d/m/Y H:i') }}</dd>

                        @if($definition->updated_at != $definition->created_at)
                            <dt class="col-sm-3">{{ __('Dernière modification') }}</dt>
                            <dd class="col-sm-9">{{ $definition->updated_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Configuration BPMN -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Configuration BPMN') }}</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>{{ $definition->bpmn_xml }}</code></pre>
                </div>
            </div>
        </div>

        <!-- Statistiques et actions -->
        <div class="col-md-4">
            <!-- Statistiques -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Statistiques') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{ __('Instances totales') }}</strong>
                        <div class="display-6">{{ $definition->instances->count() }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Instances en cours') }}</strong>
                        <div class="display-6">{{ $definition->instances->where('status', 'running')->count() }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Instances terminées') }}</strong>
                        <div class="display-6">{{ $definition->instances->where('status', 'completed')->count() }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Transitions') }}</strong>
                        <div class="display-6">{{ $definition->transitions->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Actions rapides') }}</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('workflows.instances.create') }}?definition={{ $definition->id }}" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-play-fill"></i> {{ __('Démarrer une instance') }}
                    </a>
                    <a href="{{ route('workflows.definitions.edit', $definition) }}" class="btn btn-warning w-100 mb-2">
                        <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                    </a>
                    <form action="{{ route('workflows.definitions.destroy', $definition) }}" method="POST" onsubmit="return confirm('{{ __('Êtes-vous sûr ?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> {{ __('Supprimer') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transitions -->
    @if($definition->transitions->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Transitions') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('De') }}</th>
                                <th>{{ __('Vers') }}</th>
                                <th>{{ __('Nom') }}</th>
                                <th>{{ __('Condition') }}</th>
                                <th>{{ __('Par défaut') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($definition->transitions as $transition)
                                <tr>
                                    <td><code>{{ $transition->from_task_key }}</code></td>
                                    <td><code>{{ $transition->to_task_key }}</code></td>
                                    <td>{{ $transition->name }}</td>
                                    <td>{{ $transition->condition ? Str::limit($transition->condition, 30) : '-' }}</td>
                                    <td>
                                        @if($transition->is_default)
                                            <span class="badge bg-primary">{{ __('Oui') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Non') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Instances récentes -->
    @if($definition->instances->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Instances récentes') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Nom') }}</th>
                                <th>{{ __('Statut') }}</th>
                                <th>{{ __('Démarré par') }}</th>
                                <th>{{ __('Date démarrage') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($definition->instances->take(10) as $instance)
                                <tr>
                                    <td>{{ $instance->name }}</td>
                                    <td>
                                        @switch($instance->status)
                                            @case('running')
                                                <span class="badge bg-primary">{{ __('En cours') }}</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">{{ __('Terminé') }}</span>
                                                @break
                                            @case('paused')
                                                <span class="badge bg-warning">{{ __('En pause') }}</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">{{ __('Annulé') }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $instance->starter->name ?? 'N/A' }}</td>
                                    <td>{{ $instance->started_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('workflows.instances.show', $instance) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

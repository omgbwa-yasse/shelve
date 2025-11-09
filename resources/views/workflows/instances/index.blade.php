@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-play-circle"></i> {{ __('Instances de Workflow') }}</h1>
        <a href="{{ route('workflows.instances.create') }}" class="btn btn-primary">
            <i class="bi bi-play-fill"></i> {{ __('Démarrer workflow') }}
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
                    <h5 class="card-title">{{ __('En cours') }}</h5>
                    <p class="card-text display-4">{{ $instances->where('status', 'running')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Terminés') }}</h5>
                    <p class="card-text display-4">{{ $instances->where('status', 'completed')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">{{ __('En pause') }}</h5>
                    <p class="card-text display-4">{{ $instances->where('status', 'paused')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Annulés') }}</h5>
                    <p class="card-text display-4">{{ $instances->where('status', 'cancelled')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des instances -->
    <div class="card">
        <div class="card-body">
            @if($instances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Nom') }}</th>
                                <th>{{ __('Définition') }}</th>
                                <th>{{ __('Statut') }}</th>
                                <th>{{ __('Démarré par') }}</th>
                                <th>{{ __('Date démarrage') }}</th>
                                <th>{{ __('Date fin') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instances as $instance)
                                <tr>
                                    <td><strong>{{ $instance->name }}</strong></td>
                                    <td>{{ $instance->definition->name ?? 'N/A' }}</td>
                                    <td>
                                        @switch($instance->status)
                                            @case('running')
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-arrow-repeat"></i> {{ __('En cours') }}
                                                </span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> {{ __('Terminé') }}
                                                </span>
                                                @break
                                            @case('paused')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-pause-circle"></i> {{ __('En pause') }}
                                                </span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> {{ __('Annulé') }}
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $instance->starter->name ?? 'N/A' }}</td>
                                    <td>{{ $instance->started_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $instance->completed_at ? $instance->completed_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('workflows.instances.show', $instance) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> {{ __('Voir') }}
                                        </a>
                                        @if($instance->status != 'completed' && $instance->status != 'cancelled')
                                            <form action="{{ route('workflows.instances.destroy', $instance) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Annuler cette instance ?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $instances->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-play-circle text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">{{ __('Aucune instance de workflow trouvée.') }}</p>
                    <a href="{{ route('workflows.instances.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-play-fill"></i> {{ __('Démarrer le premier workflow') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

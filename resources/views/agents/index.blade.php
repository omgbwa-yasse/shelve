@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Agents IA</h1>
        <a href="{{ route('agents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvel Agent
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prompt</th>
                            <th>Fréquence</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td>{{ $agent->name }}</td>
                                <td>{{ $agent->prompt->name }}</td>
                                <td>
                                    {{ $agent->frequence_value }}
                                    @switch($agent->frequence_type)
                                        @case('day')
                                            jour(s)
                                            @break
                                        @case('heure')
                                            heure(s)
                                            @break
                                        @case('min')
                                            minute(s)
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($agent->is_trained)
                                        <span class="badge bg-success">Entraîné</span>
                                    @else
                                        <span class="badge bg-warning">Non entraîné</span>
                                    @endif
                                    @if($agent->is_public)
                                        <span class="badge bg-info">Public</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('agents.show', $agent) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $agent)
                                            <a href="{{ route('agents.edit', $agent) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $agent)
                                            <form action="{{ route('agents.destroy', $agent) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet agent ?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun agent trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $agents->links() }}
        </div>
    </div>
</div>
@endsection

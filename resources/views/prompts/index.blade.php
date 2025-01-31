@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Prompts</h1>
        <a href="{{ route('prompts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouveau Prompt
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
                            <th>Statut</th>
                            <th>Créé par</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prompts as $prompt)
                            <tr>
                                <td>{{ $prompt->name }}</td>
                                <td>
                                    @if($prompt->is_draft)
                                        <span class="badge bg-warning">Brouillon</span>
                                    @endif
                                    @if($prompt->is_archived)
                                        <span class="badge bg-secondary">Archivé</span>
                                    @endif
                                    @if($prompt->is_public)
                                        <span class="badge bg-success">Public</span>
                                    @endif
                                    @if($prompt->is_system)
                                        <span class="badge bg-info">Système</span>
                                    @endif
                                </td>
                                <td>{{ $prompt->user->name }}</td>
                                <td>{{ $prompt->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('prompts.show', $prompt) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $prompt)
                                            <a href="{{ route('prompts.edit', $prompt) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $prompt)
                                            <form action="{{ route('prompts.destroy', $prompt) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun prompt trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $prompts->links() }}
        </div>
    </div>
</div>
@endsection

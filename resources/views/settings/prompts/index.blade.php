@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Gestion des Prompts</h5>
                    <div>
                        <a href="{{ route('settings.prompts.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouveau Prompt
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('settings.prompts.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="is_system" name="is_system" value="1" {{ request('is_system') ? 'checked' : '' }} onChange="this.form.submit()">
                                    <label class="form-check-label" for="is_system">Afficher uniquement les prompts système</label>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Organisation</th>
                                    <th>Créé par</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prompts as $prompt)
                                    <tr>
                                        <td>{{ $prompt->title ?? 'Sans titre' }}</td>
                                        <td>
                                            @if($prompt->is_system)
                                                <span class="badge bg-primary">Système</span>
                                            @else
                                                <span class="badge bg-secondary">Utilisateur</span>
                                            @endif
                                        </td>
                                        <td>{{ $prompt->organisation ? $prompt->organisation->name : 'Global' }}</td>
                                        <td>{{ $prompt->user ? $prompt->user->name : 'Système' }}</td>
                                        <td>{{ $prompt->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('settings.prompts.show', $prompt) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('settings.prompts.edit', $prompt) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal{{ $prompt->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Modal de confirmation de suppression -->
                                            <div class="modal fade" id="deleteModal{{ $prompt->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $prompt->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel{{ $prompt->id }}">Confirmer la suppression</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Êtes-vous sûr de vouloir supprimer ce prompt : "{{ $prompt->title ?? 'Sans titre' }}" ?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                            <form action="{{ route('settings.prompts.destroy', $prompt) }}" method="POST" style="display: inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun prompt trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $prompts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

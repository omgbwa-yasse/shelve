@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $prompt->name }}</h4>
                    @can('update', $prompt)
                        <div class="btn-group">
                            <a href="{{ route('prompts.edit', $prompt) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Modifier
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('prompts.toggle-public', $prompt) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            {{ $prompt->is_public ? 'Rendre privé' : 'Rendre public' }}
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('prompts.toggle-draft', $prompt) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            {{ $prompt->is_draft ? 'Publier' : 'Remettre en brouillon' }}
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('prompts.archive', $prompt) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">Archiver</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex gap-2 mb-3">
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
                        </div>

                        <h5>Instructions</h5>
                        <div class="card">
                            <div class="card-body bg-light">
                                {!! nl2br(e($prompt->instruction)) !!}
                            </div>
                        </div>
                    </div>

                    <div class="text-muted">
                        <p>
                            Créé par: {{ $prompt->user->name }}<br>
                            Date de création: {{ $prompt->created_at->format('d/m/Y H:i') }}<br>
                            Dernière modification: {{ $prompt->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('prompts.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

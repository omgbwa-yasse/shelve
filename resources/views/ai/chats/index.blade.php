@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-robot"></i> Mes Conversations IA</h1>
            <a href="{{ route('ai-chats.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouvelle Conversation
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-3">
                <!-- Filtres -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtres</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('ai-chats.index') }}">
                            <div class="mb-3">
                                <label for="model_filter" class="form-label">Modèle IA</label>
                                <select class="form-select form-select-sm" id="model_filter" name="model">
                                    <option value="">Tous les modèles</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ request('model') == $model->id ? 'selected' : '' }}>
                                            {{ $model->name }} ({{ $model->provider }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="date_filter" class="form-label">Période</label>
                                <select class="form-select form-select-sm" id="date_filter" name="period">
                                    <option value="">Toutes les périodes</option>
                                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-search"></i> Filtrer
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Statistiques</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Total conversations:</strong> {{ $stats['total_chats'] ?? 0 }}
                        </p>
                        <p class="mb-2">
                            <strong>Messages envoyés:</strong> {{ $stats['total_messages'] ?? 0 }}
                        </p>
                        <p class="mb-0">
                            <strong>Tokens utilisés:</strong> {{ number_format($stats['total_tokens'] ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div id="chatList">
                    @forelse ($chats as $chat)
                        <div class="card mb-3 shadow-sm hover-shadow">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-2">
                                            {{ $chat->title ?? 'Conversation sans titre' }}
                                            @if($chat->is_active)
                                                <span class="badge bg-success ms-2">Active</span>
                                            @else
                                                <span class="badge bg-secondary ms-2">Archivée</span>
                                            @endif
                                        </h5>
                                        <p class="card-text mb-1">
                                            <i class="bi bi-box"></i> <strong>Modèle:</strong> {{ $chat->aiModel->name ?? 'N/A' }} ({{ $chat->aiModel->provider ?? 'N/A' }})<br>
                                            <i class="bi bi-chat-dots"></i> <strong>Messages:</strong> {{ $chat->messages_count ?? 0 }}<br>
                                            <i class="bi bi-calendar"></i> <strong>Créée:</strong> {{ $chat->created_at->format('d/m/Y H:i') }}<br>
                                            <i class="bi bi-clock"></i> <strong>Dernière activité:</strong> {{ $chat->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-end text-center mt-3 mt-md-0">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ai-chats.show', $chat->id) }}" class="btn btn-sm btn-primary" title="Continuer la conversation">
                                                <i class="bi bi-chat-square-text"></i> Ouvrir
                                            </a>
                                            <a href="{{ route('ai-chats.edit', $chat->id) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($chat->is_active)
                                                <form action="{{ route('ai-chats.archive', $chat->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Archiver">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('ai-chats.destroy', $chat->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette conversation? Cette action est irréversible.')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-chat-square-dots" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="mt-3 mb-0 text-muted">Aucune conversation trouvée.</p>
                                <a href="{{ route('ai-chats.create') }}" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Commencer une nouvelle conversation
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $chats->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow {
            transition: box-shadow 0.3s ease-in-out;
        }
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endsection

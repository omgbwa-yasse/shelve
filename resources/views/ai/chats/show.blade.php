@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ $aiChat->title }}</h2>
                            <small class="text-muted">
                                Modèle: {{ $aiChat->aiModel->name }} |
                                Créé par: {{ $aiChat->user->name }} |
                                Statut:
                                <span class="badge bg-{{ $aiChat->is_active ? 'success' : 'danger' }}">
                                    {{ $aiChat->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </small>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('ai.chats.edit', $aiChat) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <form action="{{ route('ai.chats.destroy', $aiChat) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce chat ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Messages -->
                        <div class="chat-messages mb-4" style="max-height: 500px; overflow-y: auto;">
                            @forelse($aiChat->messages as $message)
                                <div class="message mb-3 {{ $message->is_from_user ? 'text-end' : '' }}">
                                    <div class="message-content d-inline-block p-3 rounded {{ $message->is_from_user ? 'bg-primary text-white' : 'bg-light' }}"
                                         style="max-width: 80%;">
                                        <div class="message-text">
                                            {!! nl2br(e($message->content)) !!}
                                        </div>
                                        <small class="message-time d-block mt-1 {{ $message->is_from_user ? 'text-white-50' : 'text-muted' }}">
                                            {{ $message->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <p>Aucun message dans cette conversation.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Formulaire de nouveau message -->
                        @if($aiChat->is_active)
                            <form action="{{ route('ai.chats.messages.store', $aiChat) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="input-group">
                                    <textarea name="content" class="form-control" rows="3"
                                              placeholder="Écrivez votre message..." required></textarea>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Envoyer
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                Cette conversation est inactive. Vous ne pouvez plus envoyer de messages.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Ressources associées -->
                @if($aiChat->resources->isNotEmpty())
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="mb-0">Ressources associées</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($aiChat->resources as $resource)
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $resource->title }}</h5>
                                                <p class="card-text">{{ Str::limit($resource->description, 100) }}</p>
                                                <a href="{{ route('resources.show', $resource) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    Voir la ressource
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .chat-messages {
            scroll-behavior: smooth;
        }
        .message-content {
            word-wrap: break-word;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Scroll to bottom of messages
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.querySelector('.chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    </script>
    @endpush
@endsection

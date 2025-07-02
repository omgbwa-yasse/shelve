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
                                Modèle: {{ $aiChat->aiModel ? $aiChat->aiModel->name : 'Non défini' }} |
                                Créé par: {{ $aiChat->user ? $aiChat->user->name : 'Utilisateur inconnu' }} |
                                Statut:
                                <span class="badge bg-{{ $aiChat->is_active ? 'success' : 'danger' }}">
                                    {{ $aiChat->is_active ? 'Actif' : 'Inactif' }}
                                </span> |
                                <span id="ollama-status" class="badge bg-secondary">
                                    Vérification d'Ollama...
                                </span>
                            </small>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('ai.chats.edit', ['chat' => $aiChat->id]) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <form action="{{ route('ai.chats.destroy', ['chat' => $aiChat->id]) }}" method="POST" class="d-inline">
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
                        <!-- Alerte pour les messages système -->
                        <div id="system-messages">
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>

                        <!-- Messages -->
                        <div class="chat-messages mb-4" id="chat-messages-container" style="max-height: 500px; overflow-y: auto;">
                            @forelse($aiChat->messages ?? [] as $message)
                                <div class="message mb-3 {{ $message->role === 'user' ? 'text-end' : '' }}" id="message-{{ $message->id }}">
                                    <div class="message-content d-inline-block p-3 rounded {{ $message->role === 'user' ? 'bg-primary text-white' : 'bg-light' }}"
                                         style="max-width: 80%;">
                                        <div class="message-text">
                                            {!! nl2br(e($message->content)) !!}
                                        </div>
                                        <small class="message-time d-block mt-1 {{ $message->role === 'user' ? 'text-white-50' : 'text-muted' }}">
                                            {{ $message->created_at ? $message->created_at->format('d/m/Y H:i') : 'Date inconnue' }}
                                            @if(isset($message->metadata['type']))
                                                <span class="badge bg-info">{{ $message->metadata['type'] }}</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-comments fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted">Aucun message dans cette conversation.</p>

                                    @if($aiChat->is_active)
                                        <div class="mt-4">
                                            <a href="{{ route('ai.chats.start', ['id' => $aiChat->id]) }}" class="btn btn-success btn-lg">
                                                <i class="fas fa-play-circle"></i> Commencer le chat
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endforelse
                        </div>

                        <!-- Indicateur de chargement -->
                        <div id="typing-indicator" class="message mb-3 d-none">
                            <div class="message-content d-inline-block p-3 rounded bg-light" style="max-width: 80%;">
                                <div class="typing-animation">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de nouveau message -->
                        @if($aiChat->is_active)
                            <form id="message-form" action="{{ route('ai.chats.messages.storeForChat', ['chat' => $aiChat->id]) }}" method="POST" class="mt-4">
                                @csrf
                                <input type="hidden" name="role" value="user">
                                <div class="input-group">
                                    <textarea name="content" id="message-content" class="form-control" rows="3"
                                              placeholder="Écrivez votre message..." required></textarea>
                                    <button type="submit" id="send-button" class="btn btn-primary">
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
                @if(isset($aiChat->resources) && $aiChat->resources->isNotEmpty())
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="mb-0">Ressources associées</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($aiChat->resources ?? [] as $resource)
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $resource->title ?? 'Titre non disponible' }}</h5>
                                                <p class="card-text">{{ isset($resource->description) ? Str::limit($resource->description, 100) : 'Description non disponible' }}</p>
                                                <a href="{{ route('ai.resources.show', ['resource' => $resource->id]) }}"
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
        /* Animation de l'indicateur de saisie */
        .typing-animation {
            display: flex;
            align-items: center;
            column-gap: 6px;
        }
        .typing-animation span {
            height: 10px;
            width: 10px;
            display: block;
            border-radius: 50%;
            background: #888;
            animation: typing 1s infinite ease-in-out;
        }
        .typing-animation span:nth-child(1) { animation-delay: 0s; }
        .typing-animation span:nth-child(2) { animation-delay: 0.3s; }
        .typing-animation span:nth-child(3) { animation-delay: 0.6s; }
        
        @keyframes typing {
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1); }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables
            const chatMessages = document.querySelector('#chat-messages-container');
            const messageForm = document.getElementById('message-form');
            const messageContent = document.getElementById('message-content');
            const sendButton = document.getElementById('send-button');
            const typingIndicator = document.getElementById('typing-indicator');
            const ollamaStatusBadge = document.getElementById('ollama-status');
            
            // Scroll to bottom of messages
            function scrollToBottom() {
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }
            
            // Check Ollama status
            function checkOllamaStatus() {
                fetch('{{ route("ai.chats.check-ollama-status") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'healthy') {
                            ollamaStatusBadge.className = 'badge bg-success';
                            ollamaStatusBadge.textContent = 'Ollama en ligne';
                        } else {
                            ollamaStatusBadge.className = 'badge bg-danger';
                            ollamaStatusBadge.textContent = 'Ollama hors ligne';
                        }
                    })
                    .catch(error => {
                        ollamaStatusBadge.className = 'badge bg-danger';
                        ollamaStatusBadge.textContent = 'Erreur de connexion';
                        console.error('Erreur de vérification du statut:', error);
                    });
            }
            
            // Handle form submission
            if (messageForm) {
                messageForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Disable form while processing
                    sendButton.disabled = true;
                    messageContent.disabled = true;
                    
                    // Show typing indicator
                    typingIndicator.classList.remove('d-none');
                    scrollToBottom();
                    
                    // Get form data
                    const formData = new FormData(messageForm);
                    
                    // Send message
                    fetch(messageForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Add user message to chat
                            const userMessageHtml = createMessageHtml(data.userMessage);
                            appendMessage(userMessageHtml);
                            
                            // Add AI response to chat
                            if (data.aiMessage) {
                                const aiMessageHtml = createMessageHtml(data.aiMessage);
                                appendMessage(aiMessageHtml);
                            }
                            
                            // Clear the form
                            messageContent.value = '';
                        } else {
                            showSystemMessage('danger', data.error || 'Une erreur est survenue.');
                        }
                    })
                    .catch(error => {
                        showSystemMessage('danger', 'Erreur de communication: ' + error.message);
                    })
                    .finally(() => {
                        // Enable form
                        sendButton.disabled = false;
                        messageContent.disabled = false;
                        typingIndicator.classList.add('d-none');
                        scrollToBottom();
                    });
                });
            }
            
            // Create message HTML
            function createMessageHtml(message) {
                const isUser = message.role === 'user';
                const messageTime = new Date(message.created_at).toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                let badgeHtml = '';
                if (message.metadata && message.metadata.type) {
                    badgeHtml = `<span class="badge bg-info">${message.metadata.type}</span>`;
                }
                
                return `
                    <div class="message mb-3 ${isUser ? 'text-end' : ''}" id="message-${message.id}">
                        <div class="message-content d-inline-block p-3 rounded ${isUser ? 'bg-primary text-white' : 'bg-light'}"
                             style="max-width: 80%;">
                            <div class="message-text">
                                ${message.content.replace(/\n/g, '<br>')}
                            </div>
                            <small class="message-time d-block mt-1 ${isUser ? 'text-white-50' : 'text-muted'}">
                                ${messageTime} ${badgeHtml}
                            </small>
                        </div>
                    </div>
                `;
            }
            
            // Append message to chat
            function appendMessage(messageHtml) {
                const messageContainer = document.createElement('div');
                messageContainer.innerHTML = messageHtml;
                chatMessages.appendChild(messageContainer.firstElementChild);
                scrollToBottom();
            }
            
            // Show system message
            function showSystemMessage(type, message) {
                const systemMessages = document.getElementById('system-messages');
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                systemMessages.appendChild(alertDiv);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    alertDiv.classList.remove('show');
                    setTimeout(() => alertDiv.remove(), 150);
                }, 5000);
            }
            
            // Initialize
            scrollToBottom();
            checkOllamaStatus();
            
            // Check Ollama status every 30 seconds
            setInterval(checkOllamaStatus, 30000);
        });
    </script>
    @endpush
@endsection

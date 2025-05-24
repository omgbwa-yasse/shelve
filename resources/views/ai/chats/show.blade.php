@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar avec infos et ressources -->
            <div class="col-md-3">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informations</h5>
                    </div>
                    <div class="card-body">
                        <h6>{{ $chat->title ?? 'Conversation sans titre' }}</h6>
                        <p class="small mb-2">
                            <strong>Modèle:</strong> {{ $chat->aiModel->name ?? 'N/A' }}<br>
                            <strong>Provider:</strong> {{ $chat->aiModel->provider ?? 'N/A' }}<br>
                            <strong>Créée:</strong> {{ $chat->created_at->format('d/m/Y H:i') }}
                        </p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('ai-chats.edit', $chat->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Modifier
                            </a>
                            <button class="btn btn-sm btn-outline-danger" onclick="clearChat()">
                                <i class="bi bi-trash"></i> Effacer l'historique
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ressources liées -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-paperclip"></i> Ressources liées</h5>
                    </div>
                    <div class="card-body">
                        <div id="resourcesList">
                            @forelse($chat->resources as $resource)
                                <div class="resource-item mb-2 p-2 bg-light rounded">
                                    <i class="bi bi-file-text"></i>
                                    <small>{{ ucfirst($resource->resource_type) }} #{{ $resource->resource_id }}</small>
                                    <button class="btn btn-sm btn-link text-danger p-0 float-end"
                                            onclick="removeResource({{ $resource->id }})">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">Aucune ressource attachée</p>
                            @endforelse
                        </div>
                        <button class="btn btn-sm btn-outline-secondary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                            <i class="bi bi-plus"></i> Ajouter une ressource
                        </button>
                    </div>
                </div>
            </div>

            <!-- Zone de chat principale -->
            <div class="col-md-9">
                <div class="card shadow h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="bi bi-chat-dots"></i> {{ $chat->title ?? 'Conversation' }}
                            </h4>
                            <div>
                                <span class="badge bg-info">{{ $chat->messages->count() }} messages</span>
                                <a href="{{ route('ai-chats.index') }}" class="btn btn-sm btn-outline-secondary ms-2">
                                    <i class="bi bi-arrow-left"></i> Retour
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0" style="height: calc(100vh - 300px); overflow-y: auto;">
                        <div id="messagesContainer" class="p-3">
                            @foreach($chat->messages as $message)
                                <div class="message-wrapper mb-3 {{ $message->role == 'user' ? 'text-end' : 'text-start' }}">
                                    <div class="message {{ $message->role == 'user' ? 'user-message' : 'assistant-message' }}">
                                        <div class="message-header">
                                            <strong>
                                                @if($message->role == 'user')
                                                    <i class="bi bi-person-circle"></i> Vous
                                                @else
                                                    <i class="bi bi-robot"></i> Assistant IA
                                                @endif
                                            </strong>
                                            <small class="text-muted ms-2">{{ $message->created_at->format('H:i') }}</small>
                                        </div>
                                        <div class="message-content mt-1">
                                            {!! nl2br(e($message->content)) !!}
                                        </div>
                                        @if($message->metadata)
                                            <div class="message-metadata mt-2">
                                                <small class="text-muted">
                                                    @if(isset($message->metadata['tokens']))
                                                        Tokens: {{ $message->metadata['tokens'] }}
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <div id="typingIndicator" class="typing-indicator d-none">
                                <div class="message assistant-message">
                                    <div class="typing-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white">
                        <form id="messageForm" onsubmit="sendMessage(event)">
                            <div class="input-group">
                                <textarea class="form-control" id="messageInput" rows="2"
                                          placeholder="Tapez votre message..."
                                          onkeypress="handleKeyPress(event)"></textarea>
                                <button class="btn btn-primary" type="submit" id="sendButton">
                                    <i class="bi bi-send"></i> Envoyer
                                </button>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Appuyez sur Shift+Entrée pour un saut de ligne
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour ajouter des ressources -->
    <div class="modal fade" id="addResourceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une ressource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addResourceForm">
                        <div class="mb-3">
                            <label class="form-label">Type de ressource</label>
                            <select class="form-select" id="resourceType">
                                <option value="records">Dossiers</option>
                                <option value="slip">Bordereaux</option>
                                <option value="communication">Communications</option>
                                <option value="mail">Courriers</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ID de la ressource</label>
                            <input type="number" class="form-control" id="resourceId" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="addResource()">Ajouter</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .message-wrapper {
            display: flex;
        }

        .user-message {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 18px 18px 4px 18px;
            max-width: 70%;
            margin-left: auto;
            display: inline-block;
        }

        .assistant-message {
            background-color: #f1f3f4;
            color: #202124;
            padding: 10px 15px;
            border-radius: 18px 18px 18px 4px;
            max-width: 70%;
            display: inline-block;
        }

        .message-header {
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .message-content {
            white-space: pre-wrap;
        }

        .typing-dots {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .typing-dots span {
            width: 8px;
            height: 8px;
            background-color: #999;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes typing {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        #messagesContainer {
            scroll-behavior: smooth;
        }
    </style>

    <script>
        const chatId = {{ $chat->id }};
        const messagesContainer = document.getElementById('messagesContainer');

        // Auto-scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage(event);
            }
        }

        async function sendMessage(event) {
            event.preventDefault();

            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            const typingIndicator = document.getElementById('typingIndicator');
            const message = messageInput.value.trim();

            if (!message) return;

            // Disable input
            messageInput.disabled = true;
            sendButton.disabled = true;

            // Add user message to UI
            addMessageToUI('user', message);
            messageInput.value = '';

            // Show typing indicator
            typingIndicator.classList.remove('d-none');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            try {
                const response = await fetch(`/ai-chats/${chatId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ content: message })
                });

                const data = await response.json();

                // Hide typing indicator
                typingIndicator.classList.add('d-none');

                // Add AI response to UI
                if (data.response) {
                    addMessageToUI('assistant', data.response);
                }

            } catch (error) {
                console.error('Error:', error);
                typingIndicator.classList.add('d-none');
                alert('Une erreur est survenue. Veuillez réessayer.');
            } finally {
                // Re-enable input
                messageInput.disabled = false;
                sendButton.disabled = false;
                messageInput.focus();
            }
        }

        function addMessageToUI(role, content) {
            const messageHtml = `
                <div class="message-wrapper mb-3 ${role === 'user' ? 'text-end' : 'text-start'}">
                    <div class="message ${role === 'user' ? 'user-message' : 'assistant-message'}">
                        <div class="message-header">
                            <strong>
                                ${role === 'user' ? '<i class="bi bi-person-circle"></i> Vous' : '<i class="bi bi-robot"></i> Assistant IA'}
                            </strong>
                            <small class="text-muted ms-2">${new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</small>
                        </div>
                        <div class="message-content mt-1">
                            ${content.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                </div>
            `;

            const typingIndicator = document.getElementById('typingIndicator');
            typingIndicator.insertAdjacentHTML('beforebegin', messageHtml);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function clearChat() {
            if (confirm('Êtes-vous sûr de vouloir effacer tout l\'historique de cette conversation?')) {
                // Implémenter la logique de suppression
            }
        }

        function addResource() {
            const type = document.getElementById('resourceType').value;
            const id = document.getElementById('resourceId').value;

            // Implémenter l'ajout de ressource via AJAX
            console.log('Adding resource:', type, id);

            // Fermer le modal
            bootstrap.Modal.getInstance(document.getElementById('addResourceModal')).hide();
        }

        function removeResource(resourceId) {
            if (confirm('Supprimer cette ressource?')) {
                // Implémenter la suppression via AJAX
                console.log('Removing resource:', resourceId);
            }
        }
    </script>
@endsection

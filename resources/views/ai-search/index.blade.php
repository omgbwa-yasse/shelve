@extends('layouts.app')

@section('title', __('AI Search Assistant'))

@section('content')
<div class="card-header bg-primary text-white">
    <h4 class="mb-0">
        <i class="bi bi-robot me-2"></i>
        {{ __('AI Search Assistant') }}
    </h4>
</div>
<div class="card-body">

                    <!-- Sélecteur de type de recherche -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="search-type-selector">
                                <label class="form-label fw-bold">{{ __('Search in:') }}</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="searchType" id="searchRecords" value="records" checked>
                                    <label class="btn btn-outline-primary" for="searchRecords">
                                        <i class="bi bi-folder me-1"></i>{{ __('Records') }}
                                    </label>

                                    <input type="radio" class="btn-check" name="searchType" id="searchMails" value="mails">
                                    <label class="btn btn-outline-primary" for="searchMails">
                                        <i class="bi bi-envelope me-1"></i>{{ __('Mails') }}
                                    </label>

                                    <input type="radio" class="btn-check" name="searchType" id="searchCommunications" value="communications">
                                    <label class="btn btn-outline-primary" for="searchCommunications">
                                        <i class="bi bi-chat-dots me-1"></i>{{ __('Communications') }}
                                    </label>

                                    <input type="radio" class="btn-check" name="searchType" id="searchSlips" value="slips">
                                    <label class="btn btn-outline-primary" for="searchSlips">
                                        <i class="bi bi-arrow-left-right me-1"></i>{{ __('Transfers') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <a href="{{ route('ai-search.documentation') }}" class="btn btn-outline-info me-2" target="_blank">
                                    <i class="bi bi-book me-1"></i>{{ __('Documentation') }}
                                </a>
                                <button class="btn btn-outline-secondary" id="clearChat">
                                    <i class="bi bi-trash me-1"></i>{{ __('Clear Chat') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de conversation -->
                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <div class="ai-message">
                                <div class="message-avatar">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-text">
                                        {{ __('Hello! I\'m your AI search assistant. I can help you find documents, mails, communications, and transfers. Ask me anything!') }}
                                    </div>
                                    <div class="message-time">{{ now()->format('H:i') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Zone de saisie -->
                        <div class="chat-input-container">
                            <form id="chatForm" class="d-flex gap-2">
                                <input type="text"
                                       id="messageInput"
                                       class="form-control form-control-lg"
                                       placeholder="{{ __('Ask me what you\'re looking for...') }}"
                                       autocomplete="off">
                                <button type="submit"
                                        class="btn btn-primary btn-lg"
                                        id="sendButton">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>
                        </div>
                    </div>

@endsection

@section('styles')
<style>
.chat-container {
    height: 600px;
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 20px;
}

.ai-message, .user-message {
    display: flex;
    align-items: flex-start;
    margin-bottom: 24px;
    animation: messageSlideIn 0.4s ease-out;
}

.user-message {
    justify-content: flex-end;
}

.message-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-right: 16px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    transition: transform 0.2s ease;
}

.message-avatar:hover {
    transform: scale(1.05);
}

.ai-message .message-avatar {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.user-message .message-avatar {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
    margin-left: 16px;
    margin-right: 0;
    order: 2;
}

.message-content {
    max-width: 75%;
    min-width: 120px;
    animation: contentFadeIn 0.5s ease-out 0.1s both;
}

.user-message .message-content {
    text-align: right;
}

.message-text {
    background-color: white;
    padding: 14px 18px;
    border-radius: 20px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    margin-bottom: 6px;
    position: relative;
    line-height: 1.5;
    word-wrap: break-word;
    border: 1px solid #f1f3f4;
    transition: box-shadow 0.2s ease;
}

.message-text:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}

.ai-message .message-text::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 16px;
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-right: 8px solid white;
}

.user-message .message-text {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-color: #0056b3;
}

.user-message .message-text::before {
    content: '';
    position: absolute;
    right: -8px;
    top: 16px;
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-left: 8px solid #007bff;
}

.message-time {
    font-size: 11px;
    color: #8e9297;
    padding: 0 10px;
    font-weight: 500;
}

.user-message .message-time {
    text-align: right;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes contentFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.result-cards {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.result-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.result-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    transform: translateY(-1px);
}

.result-card-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.result-header {
    display: flex;
    align-items: center;
    font-weight: 500;
    color: #333;
}

.result-title {
    font-size: 14px;
    line-height: 1.4;
}

.result-actions {
    text-align: right;
    font-size: 11px;
}

.result-card:hover .result-actions {
    color: #007bff !important;
}

/* Styles pour les anciens liens (compatibilité) */
.result-links {
    margin-top: 10px;
}

.result-link {
    display: inline-block;
    background-color: #28a745;
    color: white;
    padding: 6px 12px;
    border-radius: 15px;
    text-decoration: none;
    margin: 2px;
    font-size: 12px;
    transition: background-color 0.3s;
}

.result-link:hover {
    background-color: #218838;
    color: white;
    text-decoration: none;
}

.chat-input-container {
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
}

.search-type-selector .btn-group {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.typing-indicator {
    animation: messageSlideIn 0.3s ease-out;
}

.typing-dots {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-right: 8px;
}

.typing-dots .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: #007bff;
    animation: typingBounce 1.4s infinite both;
}

.typing-dots .dot:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots .dot:nth-child(3) {
    animation-delay: 0.4s;
}

.typing-text {
    color: #6c757d;
    font-style: italic;
    font-size: 14px;
}

@keyframes typingBounce {
    0%, 80%, 100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    40% {
        transform: scale(1.2);
        opacity: 1;
    }
}
</style>
@endsection

@section('scripts')
<script>
let currentSearchType = 'records';

// Gestion du changement de type de recherche
document.querySelectorAll('input[name="searchType"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.checked) {
            currentSearchType = this.value;
            addSystemMessage(`Recherche maintenant dans : ${getTypeName(currentSearchType)}`);
        }
    });
});

// Gestion du formulaire de chat
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendMessage();
});

// Clear chat
document.getElementById('clearChat').addEventListener('click', function() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.innerHTML = `
        <div class="ai-message">
            <div class="message-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-text">
                    {{ __('Hello! I\'m your AI search assistant. I can help you find documents, mails, communications, and transfers. Ask me anything!') }}
                </div>
                <div class="message-time">${new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</div>
            </div>
        </div>
    `;
});

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();

    if (!message) return;

    // Ajouter message utilisateur
    addUserMessage(message);
    messageInput.value = '';

    // Afficher indicateur de frappe
    showTypingIndicator();

    // Envoyer à l'IA
    fetch('/ai-search/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            message: message,
            search_type: currentSearchType
        })
    })
    .then(response => response.json())
    .then(data => {
        hideTypingIndicator();

        if (data.success) {
            addAIMessage(data.response, data.results || []);
        } else {
            addAIMessage(data.response || 'Une erreur est survenue.', []);
        }
    })
    .catch(error => {
        hideTypingIndicator();
        console.error('Error:', error);
        addAIMessage('Désolé, une erreur de connexion s\'est produite.', []);
    });
}

function addUserMessage(message) {
    const chatMessages = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});

    const messageHtml = `
        <div class="user-message">
            <div class="message-content">
                <div class="message-text">${escapeHtml(message)}</div>
                <div class="message-time">${time}</div>
            </div>
            <div class="message-avatar">
                <i class="bi bi-person"></i>
            </div>
        </div>
    `;

    chatMessages.insertAdjacentHTML('beforeend', messageHtml);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addAIMessage(message, results = []) {
    const chatMessages = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});

    let resultsHtml = '';
    if (results.length > 0) {
        resultsHtml = '<div class="result-cards mt-3">';
        results.forEach(result => {
            const typeIcon = getTypeIcon(result.type);
            const description = result.description ? `<small class="text-muted">${escapeHtml(result.description)}</small>` : '';

            resultsHtml += `
                <div class="result-card" onclick="openResult('${result.url}')">
                    <div class="result-card-content">
                        <div class="result-header">
                            <i class="${typeIcon} me-2"></i>
                            <span class="result-title">${escapeHtml(result.title)}</span>
                        </div>
                        ${description}
                        <div class="result-actions">
                            <small class="text-primary">Cliquer pour ouvrir</small>
                        </div>
                    </div>
                </div>
            `;
        });
        resultsHtml += '</div>';
    }

    const messageHtml = `
        <div class="ai-message">
            <div class="message-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-text">
                    ${escapeHtml(message)}
                    ${resultsHtml}
                </div>
                <div class="message-time">${time}</div>
            </div>
        </div>
    `;

    chatMessages.insertAdjacentHTML('beforeend', messageHtml);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addSystemMessage(message) {
    const chatMessages = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});

    const messageHtml = `
        <div class="ai-message">
            <div class="message-avatar">
                <i class="bi bi-gear"></i>
            </div>
            <div class="message-content">
                <div class="message-text" style="background-color: #e9ecef; color: #495057;">
                    ${escapeHtml(message)}
                </div>
                <div class="message-time">${time}</div>
            </div>
        </div>
    `;

    chatMessages.insertAdjacentHTML('beforeend', messageHtml);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function showTypingIndicator() {
    const chatMessages = document.getElementById('chatMessages');
    const indicator = `
        <div class="ai-message typing-indicator" id="typingIndicator">
            <div class="message-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-text">
                    <div class="typing-dots">
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                    <span class="typing-text">L'IA analyse votre demande...</span>
                </div>
            </div>
        </div>
    `;
    chatMessages.insertAdjacentHTML('beforeend', indicator);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
        indicator.remove();
    }
}

function getTypeName(type) {
    const types = {
        'records': 'Documents',
        'mails': 'Mails',
        'communications': 'Communications',
        'slips': 'Transferts'
    };
    return types[type] || type;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getTypeIcon(type) {
    const icons = {
        'records': 'bi bi-folder',
        'mails': 'bi bi-envelope',
        'communications': 'bi bi-chat-dots',
        'slips': 'bi bi-arrow-left-right'
    };
    return icons[type] || 'bi bi-file-earmark';
}

function openResult(url) {
    window.open(url, '_blank');
}

// Fonctions pour l'interaction avec la sidebar
window.sendMessageFromSidebar = function(query) {
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.value = query;
        sendMessage();

        // Ajouter à l'historique de la sidebar
        if (window.addToSearchHistorySidebar) {
            window.addToSearchHistorySidebar(query);
        }
    }
};

window.clearChatFromSidebar = function() {
    document.getElementById('clearChat').click();
};

window.exportChatFromSidebar = function() {
    const chatMessages = document.getElementById('chatMessages');
    const messages = chatMessages.innerHTML;
    const blob = new Blob([messages], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `ai-chat-export-${new Date().toISOString().slice(0,10)}.html`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};
</script>
@endsection
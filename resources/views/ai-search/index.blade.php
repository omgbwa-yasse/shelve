@extends('layouts.app')

@section('title', __('AI Search Assistant'))

<!-- Autoriser l'accès au microphone pour la reconnaissance vocale -->
<meta http-equiv="Permissions-Policy" content="microphone=(self)">

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
                                <div class="input-group">
                                    <input type="text"
                                           id="messageInput"
                                           class="form-control form-control-lg"
                                           placeholder="{{ __('Ask me what you\'re looking for...') }}"
                                           autocomplete="off">
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-lg"
                                            id="voiceButton"
                                            title="Reconnaissance vocale">
                                        <i class="bi bi-mic" id="voiceIcon"></i>
                                    </button>
                                </div>
                                <button type="submit"
                                        class="btn btn-primary btn-lg"
                                        id="sendButton">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>

                            <!-- Indicateur d'enregistrement vocal -->
                            <div id="voiceRecordingIndicator" class="voice-recording-indicator" style="display: none;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="recording-animation me-2">
                                        <div class="recording-dot"></div>
                                    </div>
                                    <span class="text-primary">🎤 En cours d'enregistrement... Parlez maintenant</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-3" id="stopRecordingButton">
                                        <i class="bi bi-stop-circle"></i> Arrêter
                                    </button>
                                </div>
                            </div>

                            <!-- Options vocales -->
                            <div class="voice-settings mt-2" style="display: none;" id="voiceSettings">
                                <small class="text-muted">
                                    <i class="bi bi-gear me-1"></i>
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input me-1" id="autoSendVoice" checked>
                                        Envoyer automatiquement après reconnaissance vocale
                                    </label>
                                    <span class="ms-3">
                                        <i class="bi bi-keyboard me-1"></i>
                                        Raccourci: <kbd>Ctrl+Shift+V</kbd>
                                    </span>
                                </small>
                            </div>
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

/* Voice Recording Styles */
.voice-recording-indicator {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 10px;
    margin-top: 10px;
    border: 2px solid #007bff;
    animation: pulseBlue 2s infinite;
}

.recording-animation {
    position: relative;
}

.recording-dot {
    width: 12px;
    height: 12px;
    background-color: #dc3545;
    border-radius: 50%;
    animation: recordingPulse 1s infinite alternate;
}

@keyframes recordingPulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0.6;
    }
}

@keyframes pulseBlue {
    0%, 100% {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    50% {
        border-color: #0056b3;
        background-color: #e3f2fd;
    }
}

#voiceButton {
    transition: all 0.3s ease;
}

#voiceButton:hover {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

#voiceButton.recording {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
    animation: recordingButtonPulse 1s infinite;
}

@keyframes recordingButtonPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.voice-error {
    background-color: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.voice-success {
    background-color: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.voice-settings {
    padding: 8px 12px;
    background-color: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.voice-settings .form-check-input {
    margin-top: 0.1em;
}

kbd {
    background-color: #f8f9fa;
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 1px 5px;
    font-size: 0.8em;
    color: #333;
}

/* Tooltip pour le bouton microphone */
#voiceButton[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: white;
    padding: 5px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
}

/* Animation pour le focus automatique sur l'input après reconnaissance */
#messageInput.voice-completed {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.input-group .btn {
    border-left: 1px solid #ced4da;
}
</style>
@endsection

@section('scripts')
<script>
// Fonction d'attente pour jQuery si nécessaire
function waitForJQuery(callback) {
    if (typeof window.jQuery !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForJQuery(callback);
        }, 50);
    }
}

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

// ==================== RECONNAISSANCE VOCALE ====================

class VoiceSpeechRecognition {
    constructor() {
        // Vérifier le support des API nécessaires
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            console.log("getUserMedia supported");
        } else {
            console.log("getUserMedia is not supported on your browser!");
            return;
        }

        // Vérifier le support de Speech Recognition
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.log('Speech Recognition not supported in this browser');
            return;
        }

        // Propriétés
        this.recognition = null;
        this.stream = null;
        this.isRecording = false;
        this.pendingRecording = false;
        this.permissionGranted = false;

        // Références DOM
        this.voiceButton = document.getElementById('voiceButton');
        this.voiceIcon = document.getElementById('voiceIcon');
        this.recordingIndicator = document.getElementById('voiceRecordingIndicator');
        this.stopButton = document.getElementById('stopRecordingButton');
        this.messageInput = document.getElementById('messageInput');
        this.autoSendCheckbox = document.getElementById('autoSendVoice');

        // Contraintes pour getUserMedia
        this.constraints = {
            audio: true,
            video: false
        };

        // Initialiser
        this.init();
    }

    init() {
        console.log('Initializing VoiceSpeechRecognition...');

        // Configurer Speech Recognition
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.interimResults = true;
        this.recognition.lang = 'fr-FR';

        // Event listeners pour Speech Recognition
        this.recognition.onstart = this.onRecognitionStart.bind(this);
        this.recognition.onresult = this.onRecognitionResult.bind(this);
        this.recognition.onerror = this.onRecognitionError.bind(this);
        this.recognition.onend = this.onRecognitionEnd.bind(this);

        // Event listeners pour les boutons
        if (this.voiceButton) {
            this.voiceButton.onclick = this.toggleRecording.bind(this);
        }
        if (this.stopButton) {
            this.stopButton.onclick = this.stopRecording.bind(this);
        }

        console.log('VoiceSpeechRecognition initialized successfully');
    }

    // Gérer le succès de getUserMedia
    handleStreamSuccess(stream) {
        console.log('Stream access granted');
        this.stream = stream;
        this.permissionGranted = true;

        // Arrêter le stream immédiatement (on n'a besoin que de la permission)
        this.stream.getAudioTracks().forEach(track => track.stop());
        this.stream = null;

        // Démarrer la reconnaissance vocale maintenant
        this.startSpeechRecognition();
    }

    // Gérer l'erreur de getUserMedia
    handleStreamError(error) {
        console.log("getUserMedia error: ", error);
        this.permissionGranted = false;
        this.showMessage('Accès au microphone refusé', 'error');
        this.showPermissionInstructions();
        this.pendingRecording = false;
    }

    // Démarrer la reconnaissance vocale
    startSpeechRecognition() {
        if (this.isRecording) return;

        try {
            console.log('Starting speech recognition...');
            this.messageInput.value = '';
            this.recognition.start();
        } catch (error) {
            console.error('Error starting speech recognition:', error);
            this.showMessage('Erreur lors du démarrage de la reconnaissance', 'error');
            this.pendingRecording = false;
        }
    }

    // Toggle recording (démarrer/arrêter)
    toggleRecording() {
        console.log('toggleRecording called');

        if (this.isRecording) {
            this.stopRecording();
            return;
        }

        this.pendingRecording = true;

        // Si permission déjà accordée, démarrer directement
        if (this.permissionGranted) {
            this.startSpeechRecognition();
        } else {
            // Demander la permission d'abord
            this.showMessage('Demande d\'autorisation microphone...', 'info');
            navigator.mediaDevices
                .getUserMedia(this.constraints)
                .then(this.handleStreamSuccess.bind(this))
                .catch(this.handleStreamError.bind(this));
        }
    }

    // Arrêter l'enregistrement
    stopRecording() {
        console.log('stopRecording called');
        this.pendingRecording = false;

        if (this.recognition && this.isRecording) {
            this.recognition.stop();
        }
    }

    // Events Speech Recognition
    onRecognitionStart() {
        console.log('Speech recognition started');
        this.isRecording = true;
        this.pendingRecording = false;
        this.updateUI(true);
        this.showMessage('Microphone activé. Parlez maintenant...', 'info');
    }

    onRecognitionResult(event) {
        let finalTranscript = '';
        let interimTranscript = '';

        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                finalTranscript += transcript;
            } else {
                interimTranscript += transcript;
            }
        }

        // Afficher le texte dans l'input
        if (finalTranscript) {
            this.messageInput.value = finalTranscript.trim();
            this.messageInput.classList.add('voice-completed');
            setTimeout(() => {
                this.messageInput.classList.remove('voice-completed');
            }, 2000);

            // Envoyer automatiquement si l'option est activée
            if (this.autoSendCheckbox && this.autoSendCheckbox.checked && finalTranscript.trim().length > 0) {
                this.showMessage('Message envoyé automatiquement', 'success');
                setTimeout(() => {
                    if (typeof sendMessage === 'function') {
                        sendMessage();
                    }
                }, 500);
            } else {
                this.showMessage('Reconnaissance terminée. Cliquez sur Envoyer.', 'success');
            }
        } else if (interimTranscript) {
            this.messageInput.value = interimTranscript.trim();
        }
    }

    onRecognitionError(event) {
        console.error('Speech recognition error:', event.error);
        this.isRecording = false;
        this.updateUI(false);

        let errorMessage = 'Erreur de reconnaissance vocale';
        switch(event.error) {
            case 'network':
                errorMessage = 'Erreur réseau. Vérifiez votre connexion internet.';
                break;
            case 'not-allowed':
                errorMessage = 'Accès au microphone refusé';
                this.permissionGranted = false;
                this.showPermissionInstructions();
                break;
            case 'no-speech':
                errorMessage = 'Aucune parole détectée. Essayez de parler plus fort.';
                break;
            case 'audio-capture':
                errorMessage = 'Microphone non détecté ou problème audio.';
                break;
        }

        this.showMessage(errorMessage, 'error');
        this.pendingRecording = false;
    }

    onRecognitionEnd() {
        console.log('Speech recognition ended');
        this.isRecording = false;
        this.updateUI(false);
    }

    // Mettre à jour l'interface utilisateur
    updateUI(recording) {
        if (!this.voiceButton || !this.voiceIcon || !this.recordingIndicator) return;

        if (recording) {
            this.voiceButton.classList.add('recording');
            this.voiceIcon.className = 'bi bi-mic-fill';
            this.voiceButton.title = 'Cliquez pour arrêter l\'enregistrement';
            this.recordingIndicator.style.display = 'block';
        } else {
            this.voiceButton.classList.remove('recording');
            this.voiceIcon.className = 'bi bi-mic';
            this.voiceButton.title = 'Reconnaissance vocale';
            this.recordingIndicator.style.display = 'none';
        }
    }

    // Afficher un message
    showMessage(message, type = 'info') {
        if (!this.recordingIndicator) return;

        const messageSpan = this.recordingIndicator.querySelector('span');
        if (!messageSpan) return;

        // Supprimer les classes précédentes
        this.recordingIndicator.classList.remove('voice-error', 'voice-success');

        // Ajouter la classe appropriée
        if (type === 'error') {
            this.recordingIndicator.classList.add('voice-error');
            messageSpan.innerHTML = '❌ ' + message;
        } else if (type === 'success') {
            this.recordingIndicator.classList.add('voice-success');
            messageSpan.innerHTML = '✅ ' + message;
        } else {
            messageSpan.innerHTML = '🎤 ' + message;
        }

        this.recordingIndicator.style.display = 'block';

        // Masquer automatiquement après quelques secondes pour les messages d'erreur/succès
        if (type === 'error' || type === 'success') {
            setTimeout(() => {
                if (!this.isRecording && this.recordingIndicator.style.display === 'block') {
                    this.recordingIndicator.style.display = 'none';
                }
            }, 3000);
        }
    }

    // Afficher les instructions de permission
    showPermissionInstructions() {
        if (!this.recordingIndicator) return;

        const messageSpan = this.recordingIndicator.querySelector('span');
        if (!messageSpan) return;

        this.recordingIndicator.classList.remove('voice-success');
        this.recordingIndicator.classList.add('voice-error');

        messageSpan.innerHTML = `
            🔒 <strong>Autorisation microphone requise</strong><br>
            <small>
                • Cliquez sur l'icône 🔒 ou 🎤 dans la barre d'adresse<br>
                • Sélectionnez "Autoriser" pour le microphone<br>
                • Puis cliquez à nouveau sur le bouton microphone
            </small>
        `;

        this.recordingIndicator.style.display = 'block';
    }

    // Réinitialiser les permissions
    resetPermissions() {
        console.log('Resetting permissions...');
        this.permissionGranted = false;
        this.pendingRecording = false;
        this.showMessage('Permissions réinitialisées. Vous pouvez réessayer.', 'info');
    }
}

// Instance globale
let voiceRecognition = null;
let isRecording = false; // Compatibilité avec l'ancien code

// Fonctions de compatibilité avec l'ancien code
function startRecording() {
    if (voiceRecognition) {
        voiceRecognition.toggleRecording();
    }
}

function stopRecording() {
    if (voiceRecognition) {
        voiceRecognition.stopRecording();
    }
}

function resetPermissions() {
    if (voiceRecognition) {
        voiceRecognition.resetPermissions();
    }
}

// Attendre que tout soit chargé
function initializeVoiceRecognition() {
    console.log('Initializing voice recognition...');

    // Vérifier que tous les éléments DOM existent
    const requiredElements = [
        'voiceButton',
        'voiceIcon',
        'voiceRecordingIndicator',
        'stopRecordingButton',
        'messageInput',
        'voiceSettings',
        'autoSendVoice'
    ];

    const missingElements = requiredElements.filter(id => !document.getElementById(id));
    if (missingElements.length > 0) {
        console.error('Missing DOM elements:', missingElements);
        setTimeout(initializeVoiceRecognition, 100); // Réessayer après 100ms
        return;
    }

    // Créer l'instance de reconnaissance vocale
    try {
        voiceRecognition = new VoiceSpeechRecognition();

        if (voiceRecognition.recognition) {
            console.log('VoiceSpeechRecognition initialized successfully');

            // Afficher les paramètres vocaux si supporté
            document.getElementById('voiceSettings').style.display = 'block';

            // Double-clic sur le bouton micro pour reset les permissions
            document.getElementById('voiceButton').addEventListener('dblclick', function(e) {
                e.preventDefault();
                resetPermissions();
            });

            // Raccourci clavier pour la reconnaissance vocale (Ctrl + Shift + V)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'V') {
                    e.preventDefault();
                    startRecording();
                }
            });

            // Sauvegarder les préférences dans localStorage
            document.getElementById('autoSendVoice').addEventListener('change', function() {
                localStorage.setItem('ai-search-auto-send-voice', this.checked);
            });

            // Charger les préférences depuis localStorage
            const savedAutoSend = localStorage.getItem('ai-search-auto-send-voice');
            if (savedAutoSend !== null) {
                document.getElementById('autoSendVoice').checked = savedAutoSend === 'true';
            }

            console.log('Voice recognition fully initialized!');
        } else {
            console.log('Speech recognition not supported');
            // Masquer le bouton si non supporté
            document.getElementById('voiceButton').style.display = 'none';
        }
    } catch (error) {
        console.error('Error initializing voice recognition:', error);
        document.getElementById('voiceButton').style.display = 'none';
    }
}

// Initialisation finale avec gestion de l'ordre de chargement
function initializeWhenReady() {
    console.log('Starting initialization sequence...');

    // Attendre que jQuery soit chargé si nécessaire (pour la compatibilité)
    waitForJQuery(function() {
        console.log('jQuery loaded, now initializing voice recognition...');

        // S'assurer que le DOM est prêt
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM ready, initializing voice recognition...');
                initializeVoiceRecognition();
            });
        } else {
            console.log('DOM already ready, initializing voice recognition...');
            initializeVoiceRecognition();
        }
    });
}

// Démarrer l'initialisation
initializeWhenReady();
</script>
@endsection

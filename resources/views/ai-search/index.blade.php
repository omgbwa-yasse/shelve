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
    <div class="ai-workspace">
        <div class="ai-chat-col">

                    <!-- Barre d'outils : mode agent + actions -->
                    <div class="ai-toolbar d-flex align-items-center justify-content-between mb-3">
                        <div class="form-check form-switch mb-0" title="{{ __('the AI searches in several steps across all your data (contacts, mails, records, transfers…) and shows its reasoning') }}">
                            <input class="form-check-input" type="checkbox" role="switch" id="agentMode" checked>
                            <label class="form-check-label fw-semibold" for="agentMode">
                                {{ __('Agent mode') }}
                            </label>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('ai-search.documentation') }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="{{ __('Documentation') }}">
                                <i class="bi bi-book"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" id="clearChat" title="{{ __('Clear Chat') }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Sélecteur de type de recherche (mode classique uniquement) -->
                    <div class="row mb-3" id="searchTypeRow" style="display: none;">
                        <div class="col-12">
                            <div class="search-type-selector">
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="searchType" id="searchRecords" value="records" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="searchRecords">
                                        <i class="bi bi-folder me-1"></i>{{ __('Records') }}
                                    </label>

                                    <input type="radio" class="btn-check" name="searchType" id="searchMails" value="mails">
                                    <label class="btn btn-outline-primary btn-sm" for="searchMails">
                                        <i class="bi bi-envelope me-1"></i>{{ __('Mails') }}
                                    </label>

                                    <input type="radio" class="btn-check" name="searchType" id="searchCommunications" value="communications">
                                    <label class="btn btn-outline-primary btn-sm" for="searchCommunications">
                                        <i class="bi bi-chat-dots me-1"></i>{{ __('Communications') }}
                                    </label>

                                    <input type="radio" class="btn-check" name="searchType" id="searchSlips" value="slips">
                                    <label class="btn btn-outline-primary btn-sm" for="searchSlips">
                                        <i class="bi bi-arrow-left-right me-1"></i>{{ __('Transfers') }}
                                    </label>
                                </div>
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

        </div><!-- /.ai-chat-col -->

        <!-- Panneau de détail incrusté (style preview Claude) -->
        <aside id="previewPanel" aria-hidden="true">
            <div class="preview-inner">
                <div class="preview-header">
                    <div class="preview-header-title">
                        <i id="previewIcon" class="bi bi-file-earmark me-2"></i>
                        <div>
                            <div class="preview-type" id="previewType"></div>
                            <div class="preview-title" id="previewTitle"></div>
                        </div>
                    </div>
                    <div class="preview-header-actions">
                        <a id="previewOpenLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary" style="display:none;">
                            <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('Open full page') }}
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="previewClose" title="{{ __('Close') }} (Esc)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="preview-body" id="previewBody">
                    <div class="text-muted p-3">{{ __('Loading...') }}</div>
                </div>
            </div>
        </aside>
    </div><!-- /.ai-workspace -->
</div>

@endsection

@push('styles')
<style>
/* ===== Espace de travail : chat + panneau incrusté ===== */
.ai-workspace {
    display: flex;
    align-items: stretch;
    gap: 0;
}

.ai-chat-col {
    flex: 1 1 auto;
    min-width: 0;
}

#previewPanel {
    flex: 0 0 0px;
    width: 0;
    overflow: hidden;
    transition: flex-basis 0.3s ease, width 0.3s ease;
    background: #fff;
}

#previewPanel.open {
    flex: 0 0 420px;
    width: 420px;
    border-left: 1px solid #dee2e6;
    margin-left: 16px;
}

.preview-inner {
    width: 420px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    padding: 14px 16px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.preview-header-title {
    display: flex;
    align-items: flex-start;
    min-width: 0;
    font-size: 15px;
}

.preview-header-title i {
    font-size: 20px;
    color: #007bff;
    margin-top: 2px;
}

.preview-type {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}

.preview-title {
    font-weight: 600;
    line-height: 1.3;
    word-break: break-word;
}

.preview-header-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.preview-body {
    overflow-y: auto;
    flex: 1;
    padding: 12px 16px;
    max-height: 640px;
}

.preview-field {
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f4;
}

.preview-field-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #8e9297;
    margin-bottom: 2px;
}

.preview-field-value {
    font-size: 14px;
    color: #212529;
    word-break: break-word;
}

.preview-field.highlight {
    background: #e7f1ff;
    border-radius: 8px;
    padding: 10px 12px;
    border-bottom: none;
    margin: 6px 0;
}

.preview-field.highlight .preview-field-label {
    color: #0d6efd;
}

@media (max-width: 991px) {
    #previewPanel.open {
        position: fixed;
        inset: 0;
        width: 100%;
        flex: none;
        z-index: 1055;
        margin-left: 0;
        border-left: none;
    }

    #previewPanel.open .preview-inner {
        width: 100%;
    }

    #previewPanel.open .preview-body {
        max-height: none;
    }
}

.chat-container {
    height: 600px;
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    background-color: #fafbfc;
    border: 1px solid #eef0f2;
    border-radius: 12px;
    margin-bottom: 16px;
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
    padding: 12px 16px;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    margin-bottom: 6px;
    position: relative;
    line-height: 1.55;
    word-wrap: break-word;
    border: 1px solid #eef0f2;
}

.user-message .message-text {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-color: #0056b3;
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

/* Étapes de recherche de l'agent */
.agent-steps {
    border-left: 3px solid #dee2e6;
    padding-left: 10px;
    margin-bottom: 10px;
}

.agent-step {
    font-size: 12px;
    color: #6c757d;
    padding: 2px 0;
    animation: messageSlideIn 0.25s ease-out;
}

.agent-step-count {
    color: #adb5bd;
}

/* Vignettes enrichies */
.result-type-badge {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 8px;
    border-radius: 10px;
    background: #e7f1ff;
    color: #0d6efd;
    margin-left: auto;
    flex-shrink: 0;
}

.result-meta {
    font-size: 12px;
    color: #6c757d;
}

.result-open-link {
    font-size: 12px;
    text-decoration: none;
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
@endpush

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

// Le sélecteur de type ne sert qu'au mode classique : l'agent choisit lui-même
const agentModeSwitch = document.getElementById('agentMode');
const searchTypeRow = document.getElementById('searchTypeRow');
function syncSearchTypeRow() {
    if (searchTypeRow && agentModeSwitch) {
        searchTypeRow.style.display = agentModeSwitch.checked ? 'none' : '';
    }
}
if (agentModeSwitch) {
    agentModeSwitch.addEventListener('change', syncSearchTypeRow);
    syncSearchTypeRow();
}

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

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();

    if (!message) return;

    // Ajouter message utilisateur
    addUserMessage(message);
    messageInput.value = '';

    const agentMode = document.getElementById('agentMode')?.checked ?? false;

    if (agentMode) {
        // Streaming SSE : les étapes s'affichent en direct, repli sur le POST
        // classique si le flux échoue (proxy, buffering...).
        sendAgentStream(message).catch(error => {
            console.warn('Stream failed, falling back to classic agent:', error);
            removeAgentLive();
            sendClassic('/ai-search/agent', message);
        });
    } else {
        showTypingIndicator(false);
        sendClassic('/ai-search/chat', message);
    }
}

function sendClassic(endpoint, message) {
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken()
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
            addAIMessage(data.response, data.results || [], data.steps || []);
        } else {
            console.error('AI search failed:', data.error || 'Unknown error');
            addAIMessage(data.error || 'Une erreur est survenue.', []);
        }
    })
    .catch(error => {
        hideTypingIndicator();
        console.error('Error:', error);
        addAIMessage('Désolé, une erreur de connexion s\'est produite.', []);
    });
}

async function sendAgentStream(message) {
    showAgentLive();

    const response = await fetch('/ai-search/agent/stream', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'text/event-stream',
            'X-CSRF-TOKEN': csrfToken()
        },
        body: JSON.stringify({ message: message })
    });

    if (!response.ok || !response.body) {
        throw new Error('HTTP ' + response.status);
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';
    let gotFinal = false;

    const handleEvent = (eventName, data) => {
        if (eventName === 'step') {
            addAgentLiveStep(data);
        } else if (eventName === 'final') {
            gotFinal = true;
            removeAgentLive();
            addAIMessage(data.response, data.results || [], data.steps || []);
        } else if (eventName === 'error') {
            gotFinal = true;
            removeAgentLive();
            addAIMessage(data.error || 'Une erreur est survenue.', []);
        }
    };

    while (true) {
        const { done, value } = await reader.read();
        if (done) break;
        buffer += decoder.decode(value, { stream: true });

        let sep;
        while ((sep = buffer.indexOf('\n\n')) !== -1) {
            const rawEvent = buffer.slice(0, sep);
            buffer = buffer.slice(sep + 2);

            let eventName = 'message';
            let dataLine = '';
            rawEvent.split('\n').forEach(line => {
                if (line.startsWith('event:')) eventName = line.slice(6).trim();
                if (line.startsWith('data:')) dataLine += line.slice(5).trim();
            });

            if (dataLine) {
                try {
                    handleEvent(eventName, JSON.parse(dataLine));
                } catch (e) {
                    console.warn('Bad SSE payload', e);
                }
            }
        }
    }

    if (!gotFinal) {
        throw new Error('Stream ended without final event');
    }
}

// ===== Bulle de progression de l'agent (étapes en direct) =====

function showAgentLive() {
    const chatMessages = document.getElementById('chatMessages');
    const html = `
        <div class="ai-message" id="agentLive">
            <div class="message-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-text">
                    <div class="agent-steps" id="agentLiveSteps"></div>
                    <div>
                        <div class="typing-dots">
                            <div class="dot"></div>
                            <div class="dot"></div>
                            <div class="dot"></div>
                        </div>
                        <span class="typing-text">L'agent cherche dans vos données...</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    chatMessages.insertAdjacentHTML('beforeend', html);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addAgentLiveStep(step) {
    const container = document.getElementById('agentLiveSteps');
    if (!container) return;

    const label = step.thought || step.tool || '';
    const countInfo = step.error ? '<span class="text-danger">erreur</span>' : `${step.count} résultat(s)`;
    container.insertAdjacentHTML('beforeend', `
        <div class="agent-step">
            <i class="bi bi-search me-1"></i>
            ${escapeHtml(label)}
            <span class="agent-step-count">— ${countInfo}</span>
        </div>
    `);

    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function removeAgentLive() {
    const live = document.getElementById('agentLive');
    if (live) live.remove();
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

function addAIMessage(message, results = [], steps = []) {
    const chatMessages = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});

    // Étapes de recherche de l'agent (raisonnement multi-étapes)
    let stepsHtml = '';
    if (steps.length > 0) {
        stepsHtml = '<div class="agent-steps mb-2">';
        steps.forEach(step => {
            const label = step.thought || step.tool;
            const countInfo = step.error
                ? `<span class="text-danger">erreur</span>`
                : `${step.count} résultat(s)`;
            stepsHtml += `
                <div class="agent-step">
                    <i class="bi bi-search me-1"></i>
                    ${escapeHtml(label)}
                    <span class="agent-step-count">— ${countInfo}</span>
                </div>
            `;
        });
        stepsHtml += '</div>';
    }

    let resultsHtml = '';
    if (results.length > 0) {
        resultsHtml = '<div class="result-cards mt-3">';
        results.forEach(result => {
            const typeIcon = getTypeIcon(result.type);
            const description = result.description ? `<small class="text-muted">${escapeHtml(result.description)}</small>` : '';
            const meta = result.meta ? `<div class="result-meta"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(result.meta)}</div>` : '';
            const hasPreview = PREVIEWABLE_TYPES.includes(result.type);
            const hasUrl = result.url && result.url !== 'null';
            const clickAttr = hasPreview
                ? `onclick="openPreview('${result.type}', ${parseInt(result.id, 10)})"`
                : (hasUrl ? `onclick="openResult('${result.url}')"` : '');
            const openLink = hasUrl
                ? `<a href="${result.url}" target="_blank" class="result-open-link" onclick="event.stopPropagation()"><i class="bi bi-box-arrow-up-right me-1"></i>Ouvrir la fiche</a>`
                : '';

            resultsHtml += `
                <div class="result-card" ${clickAttr} style="${clickAttr ? '' : 'cursor: default;'}">
                    <div class="result-card-content">
                        <div class="result-header">
                            <i class="${typeIcon} me-2"></i>
                            <span class="result-title">${escapeHtml(result.title)}</span>
                            <span class="result-type-badge">${escapeHtml(getTypeName(result.type))}</span>
                        </div>
                        ${meta}
                        ${description}
                        <div class="result-actions d-flex justify-content-between align-items-center">
                            ${openLink}
                            ${hasPreview ? '<small class="text-primary ms-auto"><i class="bi bi-layout-sidebar-inset-reverse me-1"></i>Aperçu</small>' : ''}
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
                    ${stepsHtml}
                    ${formatAgentMessage(message)}
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

function showTypingIndicator(agentMode = false) {
    const chatMessages = document.getElementById('chatMessages');
    const text = agentMode
        ? "L'agent cherche dans vos données (plusieurs étapes possibles)..."
        : "L'IA analyse votre demande...";
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
                    <span class="typing-text">${text}</span>
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
        'records': 'Archive',
        'mails': 'Courrier',
        'communications': 'Communication',
        'slips': 'Versement',
        'slip_records': 'Document versé',
        'contacts': 'Contact',
        'users': 'Utilisateur',
        'authors': 'Producteur',
        'containers': 'Conteneur',
        'digital_folders': 'Dossier numérique',
        'digital_documents': 'Document numérique',
        'dollies': 'Chariot',
        'tasks': 'Tâche',
        'organisations': 'Organisation',
        'external_organizations': 'Organisation externe',
        'reservations': 'Réservation',
        'accessions': 'Entrée',
        'public_requests': 'Demande du public',
        'workplaces': 'Espace de travail',
        'activities': 'Plan de classement',
        'thesaurus_concepts': 'Thésaurus',
        'keywords': 'Mot-clé',
        'laws': 'Loi'
    };
    return types[type] || type;
}

// Types disposant d'une fiche détaillée dans le panneau de preview
const PREVIEWABLE_TYPES = ['records', 'mails', 'communications', 'slips', 'contacts', 'authors'];

// Libellés français des champs du panneau de preview
const PREVIEW_LABELS = {
    code: 'Code',
    date: 'Date',
    from: 'Expéditeur',
    to: 'Destinataire',
    from_email: 'Email expéditeur',
    to_email: 'Email destinataire',
    email: 'Email',
    phone: 'Téléphone',
    address: 'Adresse',
    position: 'Fonction',
    organization: 'Organisation',
    authors: 'Producteur(s)',
    status: 'Statut',
    activity: 'Classement (activité)',
    location: 'Localisation physique',
    archived_in: 'Archivé dans',
    attachments: 'Pièces jointes',
    communicability: 'Communicabilité',
    retention: 'Rétention',
    priority: 'Priorité',
    parallel_name: 'Nom parallèle',
    other_name: 'Autre nom',
    lifespan: 'Dates d\'existence',
    locations: 'Lieux',
    operator: 'Opérateur',
    requester: 'Demandeur',
    return_date: 'Date de retour',
    description: 'Description'
};

const PREVIEW_HIGHLIGHT = ['location', 'communicability', 'retention', 'email', 'from_email', 'to_email'];

function openPreview(type, id) {
    const panel = document.getElementById('previewPanel');
    const body = document.getElementById('previewBody');
    const title = document.getElementById('previewTitle');
    const typeLabel = document.getElementById('previewType');
    const icon = document.getElementById('previewIcon');
    const openLink = document.getElementById('previewOpenLink');

    panel.classList.add('open');
    panel.setAttribute('aria-hidden', 'false');
    typeLabel.textContent = getTypeName(type);
    title.textContent = '...';
    icon.className = getTypeIcon(type) + ' me-2';
    openLink.style.display = 'none';
    body.innerHTML = '<div class="text-muted p-3"><div class="typing-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div> Chargement...</div>';

    fetch(`/ai-search/preview/${encodeURIComponent(type)}/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.item) {
            body.innerHTML = `<div class="alert alert-warning m-3">${escapeHtml(data.error || 'Élément introuvable ou hors de votre périmètre.')}</div>`;
            return;
        }

        renderPreview(data.item);
    })
    .catch(() => {
        body.innerHTML = '<div class="alert alert-danger m-3">Erreur de chargement de l\'aperçu.</div>';
    });
}

function renderPreview(item) {
    const body = document.getElementById('previewBody');
    const title = document.getElementById('previewTitle');
    const openLink = document.getElementById('previewOpenLink');

    title.textContent = item.title || ('Élément #' + item.id);

    if (item.url) {
        openLink.href = item.url;
        openLink.style.display = '';
    }

    const skip = ['type', 'id', 'title', 'url'];
    let html = '';

    // Champs mis en avant d'abord (localisation, communicabilité, emails...)
    const orderedKeys = Object.keys(item).sort((a, b) => {
        const ha = PREVIEW_HIGHLIGHT.includes(a) ? 0 : 1;
        const hb = PREVIEW_HIGHLIGHT.includes(b) ? 0 : 1;
        return ha - hb;
    });

    orderedKeys.forEach(key => {
        if (skip.includes(key)) return;
        let value = item[key];
        if (value === null || value === '' || value === undefined) return;
        if (Array.isArray(value)) {
            if (!value.length) return;
            value = value.join(', ');
        }
        if (typeof value === 'boolean') {
            value = value ? 'Oui' : 'Non';
        }

        const label = PREVIEW_LABELS[key] || key.replace(/_/g, ' ');
        const highlight = PREVIEW_HIGHLIGHT.includes(key) ? ' highlight' : '';
        html += `
            <div class="preview-field${highlight}">
                <div class="preview-field-label">${escapeHtml(label)}</div>
                <div class="preview-field-value">${escapeHtml(String(value))}</div>
            </div>
        `;
    });

    body.innerHTML = html || '<div class="text-muted p-3">Aucun détail disponible.</div>';
}

function closePreview() {
    const panel = document.getElementById('previewPanel');
    panel.classList.remove('open');
    panel.setAttribute('aria-hidden', 'true');
}

document.getElementById('previewClose').addEventListener('click', closePreview);
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePreview();
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Rendu léger du markdown des réponses de l'agent (gras + sauts de ligne),
// appliqué APRÈS échappement HTML donc sans risque d'injection.
function formatAgentMessage(text) {
    return escapeHtml(text)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n/g, '<br>');
}

function getTypeIcon(type) {
    const icons = {
        'records': 'bi bi-folder',
        'mails': 'bi bi-envelope',
        'communications': 'bi bi-chat-dots',
        'slips': 'bi bi-arrow-left-right',
        'contacts': 'bi bi-person-lines-fill',
        'users': 'bi bi-person-badge',
        'authors': 'bi bi-person-vcard',
        'containers': 'bi bi-box-seam',
        'digital_folders': 'bi bi-folder-symlink',
        'digital_documents': 'bi bi-file-earmark-text',
        'dollies': 'bi bi-cart3',
        'tasks': 'bi bi-check2-square',
        'organisations': 'bi bi-building',
        'external_organizations': 'bi bi-buildings'
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

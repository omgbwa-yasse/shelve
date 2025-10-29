{{-- Interface de chat principal --}}
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
                        title="{{ __('Voice recognition') }}">
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
        @include('ai-search.partials.voice-recording-indicator')

        <!-- Options vocales -->
        @include('ai-search.partials.voice-settings')
    </div>
</div>

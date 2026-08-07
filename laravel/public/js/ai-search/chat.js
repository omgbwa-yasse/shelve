/**
 * Gestionnaire principal pour l'interface de chat AI
 */
class AISearchChat {
    constructor() {
        this.currentSearchType = 'records';
        this.initialized = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.initialized = true;
    }

    bindEvents() {
        // Gestion du changement de type de recherche
        document.querySelectorAll('input[name="searchType"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (e.target.checked) {
                    this.currentSearchType = e.target.value;
                    this.addSystemMessage(`Recherche maintenant dans : ${this.getTypeName(this.currentSearchType)}`);
                }
            });
        });

        // Gestion du formulaire de chat
        const chatForm = document.getElementById('chatForm');
        if (chatForm) {
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage();
            });
        }

        // Clear chat
        const clearButton = document.getElementById('clearChat');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                this.clearChat();
            });
        }
    }

    sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();

        if (!message) return;

        // Ajouter message utilisateur
        this.addUserMessage(message);
        messageInput.value = '';

        // Afficher indicateur de frappe
        this.showTypingIndicator();

        // Envoyer à l'IA
        fetch('/ai-search/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                search_type: this.currentSearchType
            })
        })
        .then(response => response.json())
        .then(data => {
            this.hideTypingIndicator();

            if (data.success) {
                this.addAIMessage(data.response, data.results || []);
            } else {
                this.addAIMessage(data.response || 'Une erreur est survenue.', []);
            }
        })
        .catch(error => {
            this.hideTypingIndicator();
            console.error('Error:', error);
            this.addAIMessage('Désolé, une erreur de connexion s\'est produite.', []);
        });
    }

    addUserMessage(message) {
        const chatMessages = document.getElementById('chatMessages');
        const time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});

        const messageHtml = `
            <div class="user-message">
                <div class="message-content">
                    <div class="message-text">${this.escapeHtml(message)}</div>
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

    addAIMessage(message, results = []) {
        const chatMessages = document.getElementById('chatMessages');
        const time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});

        let resultsHtml = '';
        if (results.length > 0) {
            resultsHtml = '<div class="result-cards mt-3">';
            results.forEach(result => {
                const typeIcon = this.getTypeIcon(result.type);
                const description = result.description ? `<small class="text-muted">${this.escapeHtml(result.description)}</small>` : '';

                resultsHtml += `
                    <div class="result-card" onclick="openResult('${result.url}')">
                        <div class="result-card-content">
                            <div class="result-header">
                                <i class="${typeIcon} me-2"></i>
                                <span class="result-title">${this.escapeHtml(result.title)}</span>
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
                        ${this.escapeHtml(message)}
                        ${resultsHtml}
                    </div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;

        chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    addSystemMessage(message) {
        const chatMessages = document.getElementById('chatMessages');
        const time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});

        const messageHtml = `
            <div class="ai-message">
                <div class="message-avatar">
                    <i class="bi bi-gear"></i>
                </div>
                <div class="message-content">
                    <div class="message-text" style="background-color: #e9ecef; color: #495057;">
                        ${this.escapeHtml(message)}
                    </div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;

        chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    clearChat() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.innerHTML = `
            <div class="ai-message">
                <div class="message-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-text">
                        Bonjour ! Je suis votre assistant IA de recherche. Je peux vous aider à trouver des documents, mails, communications et transferts. Posez-moi vos questions !
                    </div>
                    <div class="message-time">${new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</div>
                </div>
            </div>
        `;
    }

    showTypingIndicator() {
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

    hideTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }

    getTypeName(type) {
        const types = {
            'records': 'Documents',
            'mails': 'Mails',
            'communications': 'Communications',
            'slips': 'Transferts'
        };
        return types[type] || type;
    }

    getTypeIcon(type) {
        const icons = {
            'records': 'bi bi-folder',
            'mails': 'bi bi-envelope',
            'communications': 'bi bi-chat-dots',
            'slips': 'bi bi-arrow-left-right'
        };
        return icons[type] || 'bi bi-file-earmark';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Fonction globale pour ouvrir les résultats
function openResult(url) {
    window.open(url, '_blank');
}

// Fonctions pour l'interaction avec la sidebar
window.sendMessageFromSidebar = function(query) {
    const messageInput = document.getElementById('messageInput');
    if (messageInput && window.aiSearchChat) {
        messageInput.value = query;
        window.aiSearchChat.sendMessage();

        // Ajouter à l'historique de la sidebar
        if (window.addToSearchHistorySidebar) {
            window.addToSearchHistorySidebar(query);
        }
    }
};

window.clearChatFromSidebar = function() {
    if (window.aiSearchChat) {
        window.aiSearchChat.clearChat();
    }
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

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    window.aiSearchChat = new AISearchChat();
});

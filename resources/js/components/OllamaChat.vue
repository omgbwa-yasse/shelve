
<template>
  <div class="ollama-chat">
    <!-- Header avec sélection de modèle -->
    <div class="chat-header bg-white shadow-sm p-4 border-b">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Chat Ollama</h2>
        <div class="flex items-center space-x-4">
          <select v-model="selectedModel" class="form-select">
            <option value="">Sélectionner un modèle</option>
            <option v-for="model in availableModels" :key="model.id" :value="model.name">
              {{ model.name }} ({{ model.parameter_size_formatted }})
            </option>
          </select>
          <div class="flex items-center">
            <span class="text-sm text-gray-500 mr-2">Status:</span>
            <div :class="`w-3 h-3 rounded-full ${healthStatus === 'healthy' ? 'bg-green-500' : 'bg-red-500'}`"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Zone de messages -->
    <div class="chat-messages flex-1 overflow-y-auto p-4 space-y-4" ref="messagesContainer">
      <div
        v-for="message in messages"
        :key="message.id"
        :class="`message ${message.role === 'user' ? 'user-message' : 'assistant-message'}`"
      >
        <div class="message-content">
          <div class="message-header">
            <span class="font-medium">{{ message.role === 'user' ? 'Vous' : selectedModel }}</span>
            <span class="text-xs text-gray-500">{{ formatTime(message.timestamp) }}</span>
          </div>
          <div class="message-text mt-2" v-html="formatMessage(message.content)"></div>
          <div v-if="message.metadata" class="message-metadata text-xs text-gray-400 mt-2">
            <span v-if="message.metadata.tokens">Tokens: {{ message.metadata.tokens }}</span>
            <span v-if="message.metadata.duration">Temps: {{ message.metadata.duration }}ms</span>
          </div>
        </div>
      </div>

      <!-- Message en cours de génération -->
      <div v-if="isGenerating" class="message assistant-message">
        <div class="message-content">
          <div class="message-header">
            <span class="font-medium">{{ selectedModel }}</span>
            <div class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
            </div>
          </div>
          <div class="message-text mt-2">
            {{ streamingMessage || 'Génération en cours...' }}
            <span class="animate-pulse">|</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Zone de saisie -->
    <div class="chat-input bg-white border-t p-4">
      <div class="flex items-end space-x-4">
        <div class="flex-1">
          <textarea
            v-model="currentMessage"
            @keydown.enter.exact.prevent="sendMessage"
            @keydown.enter.shift.exact="newLine"
            placeholder="Tapez votre message... (Entrée pour envoyer, Shift+Entrée pour nouvelle ligne)"
            class="w-full p-3 border rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
            rows="1"
            ref="messageInput"
            :disabled="!selectedModel || isGenerating"
          ></textarea>
        </div>
        <div class="flex flex-col space-y-2">
          <button
            @click="sendMessage"
            :disabled="!selectedModel || !currentMessage.trim() || isGenerating"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
            </svg>
          </button>
          <button
            @click="clearChat"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
            title="Effacer la conversation"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414L7.586 12l-1.293 1.293a1 1 0 101.414 1.414L9 13.414l2.293 2.293a1 1 0 001.414-1.414L11.414 12l1.293-1.293z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Options avancées -->
      <div class="mt-4 flex items-center space-x-4">
        <label class="flex items-center">
          <input type="checkbox" v-model="useStreaming" class="mr-2">
          <span class="text-sm">Streaming</span>
        </label>
        <div class="flex items-center space-x-2">
          <label class="text-sm">Température:</label>
          <input
            type="range"
            v-model="temperature"
            min="0"
            max="2"
            step="0.1"
            class="w-20"
          >
          <span class="text-sm w-8">{{ temperature }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OllamaChat',
  data() {
    return {
      messages: [],
      currentMessage: '',
      selectedModel: '',
      availableModels: [],
      isGenerating: false,
      streamingMessage: '',
      healthStatus: 'unknown',
      useStreaming: true,
      temperature: 0.7,
      sessionId: null,
      eventSource: null
    }
  },

  mounted() {
    this.loadModels();
    this.checkHealth();
    this.sessionId = this.generateSessionId();

    // Auto-resize textarea
    this.$nextTick(() => {
      this.autoResizeTextarea();
    });
  },

  beforeUnmount() {
    if (this.eventSource) {
      this.eventSource.close();
    }
  },

  methods: {
    async loadModels() {
      try {
        const response = await fetch('/api/ai/ollama/models');
        const data = await response.json();
        if (data.success) {
          this.availableModels = data.models.filter(model => model.is_active);
        }
      } catch (error) {
        console.error('Erreur lors du chargement des modèles:', error);
      }
    },

    async checkHealth() {
      try {
        const response = await fetch('/api/ai/ollama/health');
        const data = await response.json();
        this.healthStatus = data.status;
      } catch (error) {
        this.healthStatus = 'unhealthy';
      }
    },

    async sendMessage() {
      if (!this.currentMessage.trim() || !this.selectedModel || this.isGenerating) {
        return;
      }

      const userMessage = {
        id: Date.now(),
        role: 'user',
        content: this.currentMessage,
        timestamp: new Date()
      };

      this.messages.push(userMessage);
      const messageToSend = this.currentMessage;
      this.currentMessage = '';
      this.isGenerating = true;
      this.streamingMessage = '';

      try {
        if (this.useStreaming) {
          await this.sendStreamingMessage(messageToSend);
        } else {
          await this.sendRegularMessage(messageToSend);
        }
      } catch (error) {
        console.error('Erreur lors de l\'envoi du message:', error);
        this.messages.push({
          id: Date.now(),
          role: 'assistant',
          content: 'Erreur: ' + error.message,
          timestamp: new Date()
        });
      } finally {
        this.isGenerating = false;
        this.scrollToBottom();
      }
    },

    async sendRegularMessage(message) {
      const payload = {
        model: this.selectedModel,
        messages: this.messages.map(msg => ({
          role: msg.role,
          content: msg.content
        })),
        options: {
          temperature: parseFloat(this.temperature)
        }
      };

      const response = await fetch('/api/ai/ollama/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
      });

      const data = await response.json();

      if (data.success && data.message) {
        this.messages.push({
          id: Date.now(),
          role: 'assistant',
          content: data.message.content,
          timestamp: new Date(),
          metadata: {
            tokens: (data.prompt_eval_count || 0) + (data.eval_count || 0),
            duration: data.total_duration ? Math.round(data.total_duration / 1000000) : null
          }
        });
      } else {
        throw new Error(data.message || 'Erreur inconnue');
      }
    },

    async sendStreamingMessage(message) {
      return new Promise((resolve, reject) => {
        const url = new URL('/api/ai/ollama/stream', window.location.origin);
        url.searchParams.append('model', this.selectedModel);
        url.searchParams.append('prompt', message);
        url.searchParams.append('temperature', this.temperature);

        this.eventSource = new EventSource(url);
        let fullResponse = '';

        this.eventSource.onmessage = (event) => {
          try {
            const data = JSON.parse(event.data);
            if (data.response) {
              fullResponse += data.response;
              this.streamingMessage = fullResponse;
            }
            if (data.done) {
              this.messages.push({
                id: Date.now(),
                role: 'assistant',
                content: fullResponse,
                timestamp: new Date(),
                metadata: {
                  tokens: (data.prompt_eval_count || 0) + (data.eval_count || 0),
                  duration: data.total_duration ? Math.round(data.total_duration / 1000000) : null
                }
              });
              this.eventSource.close();
              this.eventSource = null;
              resolve();
            }
          } catch (error) {
            console.error('Erreur parsing streaming data:', error);
          }
        };

        this.eventSource.onerror = (error) => {
          this.eventSource.close();
          this.eventSource = null;
          reject(new Error('Erreur de streaming'));
        };
      });
    },

    clearChat() {
      this.messages = [];
      this.sessionId = this.generateSessionId();
    },

    formatMessage(content) {
      // Conversion markdown basique
      return content
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code>$1</code>')
        .replace(/\n/g, '<br>');
    },

    formatTime(timestamp) {
      return new Date(timestamp).toLocaleTimeString();
    },

    newLine() {
      this.currentMessage += '\n';
      this.$nextTick(() => {
        this.autoResizeTextarea();
      });
    },

    autoResizeTextarea() {
      const textarea = this.$refs.messageInput;
      if (textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 150) + 'px';
      }
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },

    generateSessionId() {
      return 'ollama_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
  },

  watch: {
    currentMessage() {
      this.$nextTick(() => {
        this.autoResizeTextarea();
      });
    },

    messages: {
      handler() {
        this.scrollToBottom();
      },
      deep: true
    }
  }
}
</script>

<style scoped>
.ollama-chat {
  display: flex;
  flex-direction: column;
  height: 100vh;
  max-height: 800px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.chat-messages {
  background-color: #f9fafb;
}

.message {
  max-width: 80%;
}

.user-message {
  margin-left: auto;
}

.user-message .message-content {
  background-color: #3b82f6;
  color: white;
  padding: 12px 16px;
  border-radius: 18px 18px 4px 18px;
}

.assistant-message .message-content {
  background-color: white;
  border: 1px solid #e5e7eb;
  padding: 12px 16px;
  border-radius: 18px 18px 18px 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
}

.user-message .message-header {
  color: rgba(255, 255, 255, 0.8);
}

.assistant-message .message-header {
  color: #6b7280;
}

.message-text {
  line-height: 1.5;
}

.message-metadata {
  display: flex;
  gap: 12px;
  margin-top: 4px;
}

.chat-input {
  background: linear-gradient(to right, #f8fafc, #f1f5f9);
}

textarea:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  border-color: #3b82f6;
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.form-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background-color: white;
  min-width: 200px;
}

.form-select:focus {
  outline: none;
  ring: 2px;
  ring-color: #3b82f6;
  border-color: #3b82f6;
}

input[type="range"] {
  accent-color: #3b82f6;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}

.animate-pulse {
  animation: pulse 1s infinite;
}
</style>


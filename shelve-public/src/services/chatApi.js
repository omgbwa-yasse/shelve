import api from './api';
import io from 'socket.io-client';

// Chat API endpoints
export const chatApi = {
  // Chat (PublicChatController)
  getChats: (params = {}) => api.get('/public/chats', { params }),
  createChat: (data) => api.post('/public/chats', data),
  joinChat: (chatId, data) => api.post(`/public/chats/${chatId}/join`, data),
  leaveChat: (chatId) => api.post(`/public/chats/${chatId}/leave`),
  getChat: (id) => api.get(`/public/chats/${id}`),

  // Messages (PublicChatMessageController)
  getMessages: (chatId, params = {}) => api.get(`/public/chats/${chatId}/messages`, { params }),
  sendMessage: (chatId, data) => api.post(`/public/chats/${chatId}/messages`, data),
  updateMessage: (chatId, messageId, data) => api.patch(`/public/chats/${chatId}/messages/${messageId}`, data),
  deleteMessage: (chatId, messageId) => api.delete(`/public/chats/${chatId}/messages/${messageId}`),

  // Participants (PublicChatParticipantController)
  getParticipants: (chatId) => api.get(`/public/chats/${chatId}/participants`),
  addParticipant: (chatId, data) => api.post(`/public/chats/${chatId}/participants`, data),
  removeParticipant: (chatId, participantId) => api.delete(`/public/chats/${chatId}/participants/${participantId}`),
  updateParticipantRole: (chatId, participantId, data) => api.patch(`/public/chats/${chatId}/participants/${participantId}`, data),
};

// WebSocket connection for real-time chat
class ChatWebSocket {
  constructor() {
    this.socket = null;
    this.isConnected = false;
  }

  connect(userId = null) {
    if (process.env.REACT_APP_ENABLE_WEBSOCKETS !== 'true') {
      console.log('WebSockets disabled');
      return;
    }

    const wsUrl = process.env.REACT_APP_WEBSOCKET_URL || 'ws://localhost:6001';
    this.socket = io(wsUrl, {
      auth: {
        userId: userId || localStorage.getItem('shelve_user_id'),
        token: localStorage.getItem('shelve_token'),
      },
      transports: ['websocket'],
    });

    this.socket.on('connect', () => {
      console.log('WebSocket connected');
      this.isConnected = true;
    });

    this.socket.on('disconnect', () => {
      console.log('WebSocket disconnected');
      this.isConnected = false;
    });

    this.socket.on('error', (error) => {
      console.error('WebSocket error:', error);
    });

    return this.socket;
  }

  disconnect() {
    if (this.socket) {
      this.socket.disconnect();
      this.socket = null;
      this.isConnected = false;
    }
  }

  joinChatRoom(chatId) {
    if (this.socket && this.isConnected) {
      this.socket.emit('join-chat', { chatId });
    }
  }

  leaveChatRoom(chatId) {
    if (this.socket && this.isConnected) {
      this.socket.emit('leave-chat', { chatId });
    }
  }

  sendMessage(chatId, message) {
    if (this.socket && this.isConnected) {
      this.socket.emit('send-message', { chatId, message });
    }
  }

  onMessage(callback) {
    if (this.socket) {
      this.socket.on('new-message', callback);
    }
  }

  onParticipantJoined(callback) {
    if (this.socket) {
      this.socket.on('participant-joined', callback);
    }
  }

  onParticipantLeft(callback) {
    if (this.socket) {
      this.socket.on('participant-left', callback);
    }
  }

  onTyping(callback) {
    if (this.socket) {
      this.socket.on('user-typing', callback);
    }
  }

  emitTyping(chatId, isTyping) {
    if (this.socket && this.isConnected) {
      this.socket.emit('typing', { chatId, isTyping });
    }
  }

  removeAllListeners() {
    if (this.socket) {
      this.socket.removeAllListeners();
    }
  }
}

export const chatWebSocket = new ChatWebSocket();

export default {
  ...chatApi,
  webSocket: chatWebSocket,
};

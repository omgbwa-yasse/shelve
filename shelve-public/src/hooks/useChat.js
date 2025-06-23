import { useState, useEffect, useCallback, useRef } from 'react';
import { chatApi } from '../services/chatApi';
import { useAuth } from '../context/AuthContext';

export const useChat = (chatId = null) => {
  const { user } = useAuth();
  const [messages, setMessages] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isTyping, setIsTyping] = useState(false);
  const [error, setError] = useState(null);
  const [chatSession, setChatSession] = useState(null);
  const [connectionStatus, setConnectionStatus] = useState('disconnected');
  const wsRef = useRef(null);
  const reconnectTimeoutRef = useRef(null);
  const retryCountRef = useRef(0);
  const maxRetries = 3;

  // Initialisation du chat
  const initializeChat = useCallback(async () => {
    try {
      setIsLoading(true);
      setError(null);

      let session;
      if (chatId) {
        // Récupérer une session existante
        session = await chatApi.getChatSession(chatId);
        if (session.messages) {
          setMessages(session.messages);
        }
      } else {
        // Créer une nouvelle session
        session = await chatApi.createChatSession({
          user_id: user?.id,
          type: 'general'
        });
      }

      setChatSession(session);

      // Établir la connexion WebSocket
      connectWebSocket(session.id);

    } catch (err) {
      setError(err.message || 'Erreur lors de l\'initialisation du chat');
    } finally {
      setIsLoading(false);
    }
  }, [chatId, user, connectWebSocket]);

  // Connexion WebSocket
  const connectWebSocket = useCallback((sessionId) => {
    if (wsRef.current?.readyState === WebSocket.OPEN) {
      return;
    }

    try {
      const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
      const wsUrl = `${protocol}//${window.location.host}/ws/chat/${sessionId}`;

      wsRef.current = new WebSocket(wsUrl);

      wsRef.current.onopen = () => {
        setConnectionStatus('connected');
        setError(null);
        retryCountRef.current = 0;
      };

      wsRef.current.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          handleWebSocketMessage(data);
        } catch (err) {
          console.error('Erreur parsing WebSocket message:', err);
        }
      };

      wsRef.current.onclose = (event) => {
        setConnectionStatus('disconnected');

        // Reconnexion automatique si nécessaire
        if (!event.wasClean && retryCountRef.current < maxRetries) {
          retryCountRef.current++;
          reconnectTimeoutRef.current = setTimeout(() => {
            connectWebSocket(sessionId);
          }, Math.pow(2, retryCountRef.current) * 1000); // Backoff exponentiel
        }
      };

      wsRef.current.onerror = (error) => {
        console.error('WebSocket error:', error);
        setConnectionStatus('error');
      };

    } catch (err) {
      console.error('Erreur connexion WebSocket:', err);
      setConnectionStatus('error');
    }
  }, []);

  // Gestion des messages WebSocket
  const handleWebSocketMessage = useCallback((data) => {
    switch (data.type) {
      case 'message':
        setMessages(prev => [...prev, data.message]);
        setIsTyping(false);
        break;

      case 'typing_start':
        setIsTyping(true);
        break;

      case 'typing_stop':
        setIsTyping(false);
        break;

      case 'error':
        setError(data.error);
        setIsTyping(false);
        break;

      case 'session_updated':
        setChatSession(prev => ({ ...prev, ...data.session }));
        break;

      default:
        console.log('Message WebSocket non géré:', data);
    }
  }, []);

  // Envoi d'un message
  const sendMessage = useCallback(async (content, type = 'text', metadata = {}) => {
    if (!chatSession || !content.trim()) {
      return;
    }

    const tempMessage = {
      id: Date.now(),
      content,
      type,
      metadata,
      role: 'user',
      timestamp: new Date().toISOString(),
      status: 'sending'
    };

    setMessages(prev => [...prev, tempMessage]);
    setError(null);

    try {
      // Envoi via API REST
      const message = await chatApi.sendMessage(chatSession.id, {
        content,
        type,
        metadata
      });

      // Mettre à jour le message temporaire
      setMessages(prev =>
        prev.map(msg =>
          msg.id === tempMessage.id
            ? { ...message, status: 'sent' }
            : msg
        )
      );

      // Envoi via WebSocket si connecté
      if (wsRef.current?.readyState === WebSocket.OPEN) {
        wsRef.current.send(JSON.stringify({
          type: 'message',
          message: {
            content,
            type,
            metadata
          }
        }));
      }

    } catch (err) {
      setError(err.message || 'Erreur lors de l\'envoi du message');

      // Marquer le message comme échoué
      setMessages(prev =>
        prev.map(msg =>
          msg.id === tempMessage.id
            ? { ...msg, status: 'failed' }
            : msg
        )
      );
    }
  }, [chatSession]);

  // Réessayer l'envoi d'un message échoué
  const retryMessage = useCallback(async (messageId) => {
    const message = messages.find(msg => msg.id === messageId);
    if (!message || message.status !== 'failed') {
      return;
    }

    await sendMessage(message.content, message.type, message.metadata);
  }, [messages, sendMessage]);

  // Démarrer/arrêter l'indicateur de frappe
  const setTypingStatus = useCallback((isTyping) => {
    if (wsRef.current?.readyState === WebSocket.OPEN) {
      wsRef.current.send(JSON.stringify({
        type: isTyping ? 'typing_start' : 'typing_stop'
      }));
    }
  }, []);

  // Supprimer un message
  const deleteMessage = useCallback(async (messageId) => {
    try {
      await chatApi.deleteMessage(chatSession.id, messageId);
      setMessages(prev => prev.filter(msg => msg.id !== messageId));
    } catch (err) {
      setError(err.message || 'Erreur lors de la suppression du message');
    }
  }, [chatSession]);

  // Effacer la conversation
  const clearChat = useCallback(async () => {
    try {
      if (chatSession) {
        await chatApi.clearChat(chatSession.id);
      }
      setMessages([]);
      setError(null);
    } catch (err) {
      setError(err.message || 'Erreur lors de l\'effacement du chat');
    }
  }, [chatSession]);

  // Fermer la connexion
  const disconnect = useCallback(() => {
    if (reconnectTimeoutRef.current) {
      clearTimeout(reconnectTimeoutRef.current);
    }

    if (wsRef.current) {
      wsRef.current.close(1000, 'User disconnect');
      wsRef.current = null;
    }

    setConnectionStatus('disconnected');
  }, []);

  // Initialisation au montage
  useEffect(() => {
    initializeChat();

    return () => {
      disconnect();
    };
  }, [initializeChat, disconnect]);

  // Nettoyage au démontage
  useEffect(() => {
    return () => {
      if (reconnectTimeoutRef.current) {
        clearTimeout(reconnectTimeoutRef.current);
      }
    };
  }, []);

  return {
    // État
    messages,
    isLoading,
    isTyping,
    error,
    chatSession,
    connectionStatus,

    // Actions
    sendMessage,
    retryMessage,
    deleteMessage,
    clearChat,
    setTypingStatus,
    disconnect,
    reconnect: () => chatSession && connectWebSocket(chatSession.id),

    // Infos
    isConnected: connectionStatus === 'connected',
    canSend: connectionStatus === 'connected' && chatSession && !isLoading
  };
};

export const useChatHistory = () => {
  const { user } = useAuth();
  const [chatSessions, setChatSessions] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  const loadChatHistory = useCallback(async () => {
    if (!user) return;

    try {
      setIsLoading(true);
      setError(null);

      const sessions = await chatApi.getChatSessions(user.id);
      setChatSessions(sessions);

    } catch (err) {
      setError(err.message || 'Erreur lors du chargement de l\'historique');
    } finally {
      setIsLoading(false);
    }
  }, [user]);

  const deleteChatSession = useCallback(async (sessionId) => {
    try {
      await chatApi.deleteChatSession(sessionId);
      setChatSessions(prev => prev.filter(session => session.id !== sessionId));
    } catch (err) {
      setError(err.message || 'Erreur lors de la suppression de la session');
    }
  }, []);

  useEffect(() => {
    loadChatHistory();
  }, [loadChatHistory]);

  return {
    chatSessions,
    isLoading,
    error,
    loadChatHistory,
    deleteChatSession
  };
};

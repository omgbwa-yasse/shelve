import React, { useState, useRef, useEffect } from 'react';
import { useChat } from '../../hooks/useChat';
import Loading from '../common/Loading';
import ErrorMessage from '../common/ErrorMessage';
import { formatDateTime } from '../../utils/dateUtils';

const ChatInterface = ({ chatId = null, className = '' }) => {
  const [message, setMessage] = useState('');
  const [isComposing, setIsComposing] = useState(false);
  const messagesEndRef = useRef(null);
  const inputRef = useRef(null);
  const composingTimeoutRef = useRef(null);

  const {
    messages,
    isLoading,
    isTyping,
    error,
    chatSession,
    connectionStatus,
    sendMessage,
    retryMessage,
    deleteMessage,
    clearChat,
    setTypingStatus,
    isConnected,
    canSend
  } = useChat(chatId);

  // Auto-scroll vers le bas quand de nouveaux messages arrivent
  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  // Focus sur l'input au chargement
  useEffect(() => {
    if (inputRef.current && isConnected) {
      inputRef.current.focus();
    }
  }, [isConnected]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!message.trim() || !canSend) {
      return;
    }

    const messageContent = message.trim();
    setMessage('');
    setIsComposing(false);
    setTypingStatus(false);

    await sendMessage(messageContent);
  };

  const handleInputChange = (e) => {
    const value = e.target.value;
    setMessage(value);

    // Gestion de l'indicateur de frappe
    if (value.trim() && !isComposing) {
      setIsComposing(true);
      setTypingStatus(true);
    } else if (!value.trim() && isComposing) {
      setIsComposing(false);
      setTypingStatus(false);
    }

    // Arrêter l'indicateur de frappe après inactivité
    if (composingTimeoutRef.current) {
      clearTimeout(composingTimeoutRef.current);
    }

    composingTimeoutRef.current = setTimeout(() => {
      if (isComposing) {
        setIsComposing(false);
        setTypingStatus(false);
      }
    }, 3000);
  };

  const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSubmit(e);
    }
  };

  const handleRetry = (messageId) => {
    retryMessage(messageId);
  };

  const handleDelete = async (messageId) => {
    if (window.confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
      await deleteMessage(messageId);
    }
  };

  const handleClearChat = async () => {
    if (window.confirm('Êtes-vous sûr de vouloir effacer toute la conversation ?')) {
      await clearChat();
    }
  };

  if (isLoading && !chatSession) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loading />
      </div>
    );
  }

  return (
    <div className={`chat-interface flex flex-col h-full bg-white rounded-lg shadow-lg ${className}`}>
      {/* En-tête du chat */}
      <div className="chat-header flex items-center justify-between p-4 border-b border-gray-200">
        <div className="flex items-center gap-3">
          <div className="flex items-center gap-2">
            <div className={`w-3 h-3 rounded-full ${
              connectionStatus === 'connected' ? 'bg-green-500' :
              connectionStatus === 'connecting' ? 'bg-yellow-500' :
              'bg-red-500'
            }`}></div>
            <span className="text-sm font-medium text-gray-700">
              Assistant IA
            </span>
          </div>

          <span className="text-xs text-gray-500">
            {connectionStatus === 'connected' ? 'En ligne' :
             connectionStatus === 'connecting' ? 'Connexion...' :
             'Hors ligne'}
          </span>
        </div>

        <div className="flex items-center gap-2">
          {messages.length > 0 && (
            <button
              onClick={handleClearChat}
              className="text-gray-400 hover:text-red-600 p-1 rounded"
              title="Effacer la conversation"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          )}
        </div>
      </div>

      {/* Zone de messages */}
      <div className="chat-messages flex-1 overflow-y-auto p-4 space-y-4">
        {error && (
          <ErrorMessage
            message={error}
            onRetry={() => window.location.reload()}
          />
        )}

        {messages.length === 0 ? (
          <div className="text-center text-gray-500 py-8">
            <div className="text-4xl mb-4">💬</div>
            <p>Commencez une conversation avec l'assistant IA</p>
            <p className="text-sm mt-2">Posez vos questions sur les archives et documents</p>
          </div>
        ) : (
          messages.map((msg) => (
            <ChatMessage
              key={msg.id}
              message={msg}
              onRetry={() => handleRetry(msg.id)}
              onDelete={() => handleDelete(msg.id)}
            />
          ))
        )}

        {isTyping && (
          <div className="flex items-center gap-2 text-gray-500 text-sm">
            <div className="flex gap-1">
              <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
              <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0.1s' }}></div>
              <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0.2s' }}></div>
            </div>
            <span>L'assistant rédige une réponse...</span>
          </div>
        )}

        <div ref={messagesEndRef} />
      </div>

      {/* Zone de saisie */}
      <div className="chat-input border-t border-gray-200 p-4">
        <form onSubmit={handleSubmit} className="flex gap-2">
          <div className="flex-1">
            <textarea
              ref={inputRef}
              value={message}
              onChange={handleInputChange}
              onKeyPress={handleKeyPress}
              placeholder="Tapez votre message..."
              disabled={!canSend}
              rows="1"
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none disabled:opacity-50"
              style={{ minHeight: '40px', maxHeight: '120px' }}
            />
          </div>

          <button
            type="submit"
            disabled={!message.trim() || !canSend}
            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </form>

        {!isConnected && (
          <div className="mt-2 text-xs text-red-600 flex items-center gap-1">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            Connexion interrompue. Tentative de reconnexion...
          </div>
        )}
      </div>
    </div>
  );
};

// Composant pour afficher un message individuel
const ChatMessage = ({ message, onRetry, onDelete }) => {
  const isUser = message.role === 'user';
  const isFailed = message.status === 'failed';

  return (
    <div className={`flex ${isUser ? 'justify-end' : 'justify-start'}`}>
      <div className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
        isUser
          ? 'bg-blue-600 text-white'
          : 'bg-gray-100 text-gray-900'
      } ${isFailed ? 'bg-red-100 border border-red-300' : ''}`}>

        <div className="message-content">
          {message.type === 'text' ? (
            <p className="whitespace-pre-wrap">{message.content}</p>
          ) : message.type === 'html' ? (
            <div dangerouslySetInnerHTML={{ __html: message.content }} />
          ) : (
            <p>{message.content}</p>
          )}
        </div>

        <div className={`flex items-center justify-between mt-2 text-xs ${
          isUser ? 'text-blue-100' : 'text-gray-500'
        }`}>
          <span>{formatDateTime(message.timestamp)}</span>

          <div className="flex items-center gap-1">
            {isFailed && (
              <button
                onClick={onRetry}
                className="hover:text-red-700 p-1"
                title="Réessayer"
              >
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </button>
            )}

            {isUser && (
              <button
                onClick={onDelete}
                className={`hover:text-red-300 p-1 ${isUser ? '' : 'hover:text-red-700'}`}
                title="Supprimer"
              >
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            )}

            {message.status === 'sending' && (
              <div className="w-3 h-3">
                <div className="w-2 h-2 bg-current rounded-full animate-pulse"></div>
              </div>
            )}

            {message.status === 'sent' && isUser && (
              <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 16.17L5.53 12.7c-.39-.39-.39-1.02 0-1.41.39-.39 1.02-.39 1.41 0L9 13.34l6.06-6.06c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41L9 16.17z"/>
              </svg>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ChatInterface;

import React, { createContext, useContext, useReducer, useEffect, useCallback } from 'react';
import { chatApi, chatWebSocket } from '../services/chatApi';
import { useAuth } from './AuthContext';
import { FEATURES } from '../utils/constants';

// Initial state
const initialState = {
  chats: [],
  activeChat: null,
  messages: [],
  participants: [],
  isConnected: false,
  isLoading: false,
  error: null,
  typingUsers: [],
  unreadCounts: {},
};

// Action types
const actionTypes = {
  SET_LOADING: 'SET_LOADING',
  SET_ERROR: 'SET_ERROR',
  CLEAR_ERROR: 'CLEAR_ERROR',
  SET_CHATS: 'SET_CHATS',
  SET_ACTIVE_CHAT: 'SET_ACTIVE_CHAT',
  SET_MESSAGES: 'SET_MESSAGES',
  ADD_MESSAGE: 'ADD_MESSAGE',
  UPDATE_MESSAGE: 'UPDATE_MESSAGE',
  DELETE_MESSAGE: 'DELETE_MESSAGE',
  SET_PARTICIPANTS: 'SET_PARTICIPANTS',
  ADD_PARTICIPANT: 'ADD_PARTICIPANT',
  REMOVE_PARTICIPANT: 'REMOVE_PARTICIPANT',
  SET_CONNECTED: 'SET_CONNECTED',
  SET_TYPING_USERS: 'SET_TYPING_USERS',
  SET_UNREAD_COUNT: 'SET_UNREAD_COUNT',
  CLEAR_UNREAD_COUNT: 'CLEAR_UNREAD_COUNT',
};

// Reducer
const chatReducer = (state, action) => {
  switch (action.type) {
    case actionTypes.SET_LOADING:
      return { ...state, isLoading: action.payload };

    case actionTypes.SET_ERROR:
      return { ...state, error: action.payload, isLoading: false };

    case actionTypes.CLEAR_ERROR:
      return { ...state, error: null };

    case actionTypes.SET_CHATS:
      return { ...state, chats: action.payload };

    case actionTypes.SET_ACTIVE_CHAT:
      return { ...state, activeChat: action.payload };

    case actionTypes.SET_MESSAGES:
      return { ...state, messages: action.payload };

    case actionTypes.ADD_MESSAGE:
      return {
        ...state,
        messages: [...state.messages, action.payload].sort((a, b) =>
          new Date(a.created_at) - new Date(b.created_at)
        )
      };

    case actionTypes.UPDATE_MESSAGE:
      return {
        ...state,
        messages: state.messages.map(msg =>
          msg.id === action.payload.id ? { ...msg, ...action.payload } : msg
        ),
      };

    case actionTypes.DELETE_MESSAGE:
      return {
        ...state,
        messages: state.messages.filter(msg => msg.id !== action.payload),
      };

    case actionTypes.SET_PARTICIPANTS:
      return { ...state, participants: action.payload };

    case actionTypes.ADD_PARTICIPANT:
      return {
        ...state,
        participants: [...state.participants, action.payload],
      };

    case actionTypes.REMOVE_PARTICIPANT:
      return {
        ...state,
        participants: state.participants.filter(p => p.id !== action.payload),
      };

    case actionTypes.SET_CONNECTED:
      return { ...state, isConnected: action.payload };

    case actionTypes.SET_TYPING_USERS:
      return { ...state, typingUsers: action.payload };

    case actionTypes.SET_UNREAD_COUNT:
      return {
        ...state,
        unreadCounts: {
          ...state.unreadCounts,
          [action.payload.chatId]: action.payload.count,
        },
      };

    case actionTypes.CLEAR_UNREAD_COUNT:
      return {
        ...state,
        unreadCounts: {
          ...state.unreadCounts,
          [action.payload]: 0,
        },
      };

    default:
      return state;
  }
};

// Create context
const ChatContext = createContext();

// Chat provider component
export const ChatProvider = ({ children }) => {
  const [state, dispatch] = useReducer(chatReducer, initialState);
  const { user, isAuthenticated } = useAuth();

  // Initialize WebSocket connection
  useEffect(() => {
    if (FEATURES.CHAT_ENABLED && isAuthenticated && user) {
      const socket = chatWebSocket.connect(user.id);

      if (socket) {
        // Connection events
        socket.on('connect', () => {
          dispatch({ type: actionTypes.SET_CONNECTED, payload: true });
        });

        socket.on('disconnect', () => {
          dispatch({ type: actionTypes.SET_CONNECTED, payload: false });
        });

        // Message events
        socket.on('new-message', (message) => {
          dispatch({ type: actionTypes.ADD_MESSAGE, payload: message });

          // Update unread count if not in active chat
          if (state.activeChat?.id !== message.chat_id) {
            const currentCount = state.unreadCounts[message.chat_id] || 0;
            dispatch({
              type: actionTypes.SET_UNREAD_COUNT,
              payload: { chatId: message.chat_id, count: currentCount + 1 },
            });
          }
        });

        socket.on('message-updated', (message) => {
          dispatch({ type: actionTypes.UPDATE_MESSAGE, payload: message });
        });

        socket.on('message-deleted', (messageId) => {
          dispatch({ type: actionTypes.DELETE_MESSAGE, payload: messageId });
        });

        // Participant events
        socket.on('participant-joined', (participant) => {
          dispatch({ type: actionTypes.ADD_PARTICIPANT, payload: participant });
        });

        socket.on('participant-left', (participantId) => {
          dispatch({ type: actionTypes.REMOVE_PARTICIPANT, payload: participantId });
        });

        // Typing events
        socket.on('user-typing', ({ userId, isTyping, chatId }) => {
          if (state.activeChat?.id === chatId) {
            dispatch({
              type: actionTypes.SET_TYPING_USERS,
              payload: isTyping
                ? [...state.typingUsers.filter(id => id !== userId), userId]
                : state.typingUsers.filter(id => id !== userId),
            });
          }
        });

        return () => {
          chatWebSocket.disconnect();
        };
      }
    }
  }, [isAuthenticated, user, state.activeChat?.id, state.typingUsers, state.unreadCounts]);

  // Load chats
  const loadChats = useCallback(async (params = {}) => {
    dispatch({ type: actionTypes.SET_LOADING, payload: true });

    try {
      const response = await chatApi.getChats(params);
      dispatch({ type: actionTypes.SET_CHATS, payload: response.data });
    } catch (error) {
      dispatch({ type: actionTypes.SET_ERROR, payload: error.message });
    } finally {
      dispatch({ type: actionTypes.SET_LOADING, payload: false });
    }
  }, []);

  // Join chat
  const joinChat = useCallback(async (chatId) => {
    dispatch({ type: actionTypes.SET_LOADING, payload: true });

    try {
      // Get chat details
      const chatResponse = await chatApi.getChat(chatId);
      const chat = chatResponse.data;

      // Get messages
      const messagesResponse = await chatApi.getMessages(chatId);
      const messages = messagesResponse.data;

      // Get participants
      const participantsResponse = await chatApi.getParticipants(chatId);
      const participants = participantsResponse.data;

      // Set active chat
      dispatch({ type: actionTypes.SET_ACTIVE_CHAT, payload: chat });
      dispatch({ type: actionTypes.SET_MESSAGES, payload: messages });
      dispatch({ type: actionTypes.SET_PARTICIPANTS, payload: participants });

      // Clear unread count
      dispatch({ type: actionTypes.CLEAR_UNREAD_COUNT, payload: chatId });

      // Join WebSocket room
      if (state.isConnected) {
        chatWebSocket.joinChatRoom(chatId);
      }

      return { success: true, chat };
    } catch (error) {
      dispatch({ type: actionTypes.SET_ERROR, payload: error.message });
      return { success: false, error: error.message };
    } finally {
      dispatch({ type: actionTypes.SET_LOADING, payload: false });
    }
  }, [state.isConnected]);

  // Leave chat
  const leaveChat = useCallback(() => {
    if (state.activeChat && state.isConnected) {
      chatWebSocket.leaveChatRoom(state.activeChat.id);
    }

    dispatch({ type: actionTypes.SET_ACTIVE_CHAT, payload: null });
    dispatch({ type: actionTypes.SET_MESSAGES, payload: [] });
    dispatch({ type: actionTypes.SET_PARTICIPANTS, payload: [] });
    dispatch({ type: actionTypes.SET_TYPING_USERS, payload: [] });
  }, [state.activeChat, state.isConnected]);

  // Send message
  const sendMessage = useCallback(async (content, type = 'text') => {
    if (!state.activeChat) return { success: false, error: 'Aucun chat actif' };

    try {
      const messageData = {
        content,
        type,
        user_id: user.id,
      };

      // Send via API
      const response = await chatApi.sendMessage(state.activeChat.id, messageData);
      const message = response.data;

      // Send via WebSocket for real-time delivery
      if (state.isConnected) {
        chatWebSocket.sendMessage(state.activeChat.id, message);
      } else {
        // Fallback: add message locally if WebSocket not connected
        dispatch({ type: actionTypes.ADD_MESSAGE, payload: message });
      }

      return { success: true, message };
    } catch (error) {
      dispatch({ type: actionTypes.SET_ERROR, payload: error.message });
      return { success: false, error: error.message };
    }
  }, [state.activeChat, state.isConnected, user]);

  // Update message
  const updateMessage = useCallback(async (messageId, content) => {
    if (!state.activeChat) return { success: false, error: 'Aucun chat actif' };

    try {
      const response = await chatApi.updateMessage(state.activeChat.id, messageId, { content });
      const updatedMessage = response.data;

      dispatch({ type: actionTypes.UPDATE_MESSAGE, payload: updatedMessage });
      return { success: true, message: updatedMessage };
    } catch (error) {
      dispatch({ type: actionTypes.SET_ERROR, payload: error.message });
      return { success: false, error: error.message };
    }
  }, [state.activeChat]);

  // Delete message
  const deleteMessage = useCallback(async (messageId) => {
    if (!state.activeChat) return { success: false, error: 'Aucun chat actif' };

    try {
      await chatApi.deleteMessage(state.activeChat.id, messageId);
      dispatch({ type: actionTypes.DELETE_MESSAGE, payload: messageId });
      return { success: true };
    } catch (error) {
      dispatch({ type: actionTypes.SET_ERROR, payload: error.message });
      return { success: false, error: error.message };
    }
  }, [state.activeChat]);

  // Start typing
  const startTyping = useCallback(() => {
    if (state.activeChat && state.isConnected) {
      chatWebSocket.emitTyping(state.activeChat.id, true);
    }
  }, [state.activeChat, state.isConnected]);

  // Stop typing
  const stopTyping = useCallback(() => {
    if (state.activeChat && state.isConnected) {
      chatWebSocket.emitTyping(state.activeChat.id, false);
    }
  }, [state.activeChat, state.isConnected]);

  // Clear error
  const clearError = useCallback(() => {
    dispatch({ type: actionTypes.CLEAR_ERROR });
  }, []);

  // Create new chat
  const createChat = useCallback(async (chatData) => {
    dispatch({ type: actionTypes.SET_LOADING, payload: true });

    try {
      const response = await chatApi.createChat(chatData);
      const newChat = response.data;

      // Reload chats to include the new one
      await loadChats();

      return { success: true, chat: newChat };
    } catch (error) {
      dispatch({ type: actionTypes.SET_ERROR, payload: error.message });
      return { success: false, error: error.message };
    } finally {
      dispatch({ type: actionTypes.SET_LOADING, payload: false });
    }
  }, [loadChats]);

  const value = {
    // State
    ...state,

    // Actions
    loadChats,
    joinChat,
    leaveChat,
    sendMessage,
    updateMessage,
    deleteMessage,
    startTyping,
    stopTyping,
    clearError,
    createChat,
  };

  return (
    <ChatContext.Provider value={value}>
      {children}
    </ChatContext.Provider>
  );
};

// Custom hook to use chat context
export const useChat = () => {
  const context = useContext(ChatContext);

  if (!context) {
    throw new Error('useChat must be used within a ChatProvider');
  }

  return context;
};

export default ChatContext;

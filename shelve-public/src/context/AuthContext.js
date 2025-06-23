import React, { createContext, useContext, useReducer, useEffect } from 'react';
import { userApi } from '../services/shelveApi';
import { auth as authUtils } from '../utils/helpers';

// Initial state
const initialState = {
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,
  error: null,
};

// Action types
const actionTypes = {
  SET_LOADING: 'SET_LOADING',
  LOGIN_SUCCESS: 'LOGIN_SUCCESS',
  LOGIN_FAILURE: 'LOGIN_FAILURE',
  LOGOUT: 'LOGOUT',
  UPDATE_USER: 'UPDATE_USER',
  CLEAR_ERROR: 'CLEAR_ERROR',
};

// Reducer
const authReducer = (state, action) => {
  switch (action.type) {
    case actionTypes.SET_LOADING:
      return {
        ...state,
        isLoading: action.payload,
      };

    case actionTypes.LOGIN_SUCCESS:
      return {
        ...state,
        user: action.payload.user,
        token: action.payload.token,
        isAuthenticated: true,
        isLoading: false,
        error: null,
      };

    case actionTypes.LOGIN_FAILURE:
      return {
        ...state,
        user: null,
        token: null,
        isAuthenticated: false,
        isLoading: false,
        error: action.payload,
      };

    case actionTypes.LOGOUT:
      return {
        ...state,
        user: null,
        token: null,
        isAuthenticated: false,
        isLoading: false,
        error: null,
      };

    case actionTypes.UPDATE_USER:
      return {
        ...state,
        user: { ...state.user, ...action.payload },
      };

    case actionTypes.CLEAR_ERROR:
      return {
        ...state,
        error: null,
      };

    default:
      return state;
  }
};

// Create context
const AuthContext = createContext();

// Auth provider component
export const AuthProvider = ({ children }) => {
  const [state, dispatch] = useReducer(authReducer, initialState);

  // Initialize auth state from localStorage
  useEffect(() => {
    const initializeAuth = () => {
      try {
        const token = authUtils.getToken();
        const user = authUtils.getUser();

        if (token && user) {
          dispatch({
            type: actionTypes.LOGIN_SUCCESS,
            payload: { user, token },
          });
        } else {
          dispatch({ type: actionTypes.SET_LOADING, payload: false });
        }
      } catch (error) {
        console.error('Error initializing auth:', error);
        dispatch({ type: actionTypes.SET_LOADING, payload: false });
      }
    };

    initializeAuth();
  }, []);

  // Login function
  const login = async (credentials) => {
    dispatch({ type: actionTypes.SET_LOADING, payload: true });

    try {
      const response = await userApi.loginUser(credentials);
      const { user, token } = response.data;

      // Store in localStorage
      authUtils.setToken(token);
      authUtils.setUser(user);
      authUtils.setUserId(user.id);

      dispatch({
        type: actionTypes.LOGIN_SUCCESS,
        payload: { user, token },
      });

      return { success: true, user, token };
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Erreur lors de la connexion';

      dispatch({
        type: actionTypes.LOGIN_FAILURE,
        payload: errorMessage,
      });

      return { success: false, error: errorMessage };
    }
  };

  // Register function
  const register = async (userData) => {
    dispatch({ type: actionTypes.SET_LOADING, payload: true });

    try {
      const response = await userApi.registerUser(userData);
      const { user, token } = response.data;

      // Store in localStorage
      authUtils.setToken(token);
      authUtils.setUser(user);
      authUtils.setUserId(user.id);

      dispatch({
        type: actionTypes.LOGIN_SUCCESS,
        payload: { user, token },
      });

      return { success: true, user, token };
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Erreur lors de l\'inscription';

      dispatch({
        type: actionTypes.LOGIN_FAILURE,
        payload: errorMessage,
      });

      return { success: false, error: errorMessage };
    }
  };

  // Logout function
  const logout = () => {
    authUtils.logout();
    dispatch({ type: actionTypes.LOGOUT });
  };

  // Update user profile
  const updateProfile = async (userId, userData) => {
    try {
      const response = await userApi.updateUserProfile(userId, userData);
      const updatedUser = response.data;

      // Update localStorage
      authUtils.setUser(updatedUser);

      dispatch({
        type: actionTypes.UPDATE_USER,
        payload: updatedUser,
      });

      return { success: true, user: updatedUser };
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Erreur lors de la mise à jour du profil';
      return { success: false, error: errorMessage };
    }
  };

  // Get user preferences
  const getUserPreferences = async (userId) => {
    try {
      const response = await userApi.getUserPreferences(userId);
      return { success: true, preferences: response.data };
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Erreur lors de la récupération des préférences';
      return { success: false, error: errorMessage };
    }
  };

  // Update user preferences
  const updatePreferences = async (userId, preferences) => {
    try {
      const response = await userApi.updateUserPreferences(userId, preferences);
      return { success: true, preferences: response.data };
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Erreur lors de la mise à jour des préférences';
      return { success: false, error: errorMessage };
    }
  };

  // Clear error
  const clearError = () => {
    dispatch({ type: actionTypes.CLEAR_ERROR });
  };

  // Check if user has specific role
  const hasRole = (role) => {
    return state.user?.role === role;
  };

  // Check if user can perform action
  const canPerformAction = (action) => {
    if (!state.isAuthenticated) return false;

    // Define permissions based on user role
    const permissions = {
      guest: [],
      registered: ['view_profile', 'update_profile', 'submit_feedback', 'request_documents'],
      moderator: ['moderate_chat', 'manage_events'],
      admin: ['manage_users', 'manage_content'],
    };

    const userRole = state.user?.role || 'guest';
    const userPermissions = permissions[userRole] || [];

    return userPermissions.includes(action);
  };

  const value = {
    // State
    ...state,

    // Actions
    login,
    register,
    logout,
    updateProfile,
    getUserPreferences,
    updatePreferences,
    clearError,

    // Utilities
    hasRole,
    canPerformAction,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

// Custom hook to use auth context
export const useAuth = () => {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }

  return context;
};

export default AuthContext;

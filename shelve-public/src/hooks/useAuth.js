import { useState, useEffect, useCallback } from 'react';
import { authApi } from '../services/api';

export const useAuth = () => {
  const [user, setUser] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [error, setError] = useState(null);

  // Vérifier le token au chargement
  useEffect(() => {
    const checkAuthStatus = async () => {
      try {
        const token = localStorage.getItem('auth_token');
        if (token) {
          // Vérifier la validité du token
          const userData = await authApi.verifyToken(token);
          setUser(userData);
          setIsAuthenticated(true);
        }      } catch (err) {
        // Token invalide, le supprimer
        console.log('Token invalide, déconnexion automatique');
        localStorage.removeItem('auth_token');
        setUser(null);
        setIsAuthenticated(false);
      } finally {
        setIsLoading(false);
      }
    };

    checkAuthStatus();
  }, []);

  // Connexion
  const login = useCallback(async (credentials) => {
    try {
      setIsLoading(true);
      setError(null);

      const response = await authApi.login(credentials);
      const { user: userData, token } = response;

      // Stocker le token
      localStorage.setItem('auth_token', token);

      // Mettre à jour l'état
      setUser(userData);
      setIsAuthenticated(true);

      return { success: true, user: userData };
    } catch (err) {
      setError(err.message || 'Erreur de connexion');
      return { success: false, error: err.message };
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Inscription
  const register = useCallback(async (userData) => {
    try {
      setIsLoading(true);
      setError(null);

      const response = await authApi.register(userData);
      const { user: newUser, token } = response;

      // Stocker le token
      localStorage.setItem('auth_token', token);

      // Mettre à jour l'état
      setUser(newUser);
      setIsAuthenticated(true);

      return { success: true, user: newUser };
    } catch (err) {
      setError(err.message || 'Erreur d\'inscription');
      return { success: false, error: err.message };
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Déconnexion
  const logout = useCallback(async () => {
    try {
      // Appeler l'API pour invalider le token côté serveur
      await authApi.logout();
    } catch (err) {
      console.error('Erreur lors de la déconnexion:', err);
    } finally {
      // Nettoyer l'état local dans tous les cas
      localStorage.removeItem('auth_token');
      setUser(null);
      setIsAuthenticated(false);
      setError(null);
    }
  }, []);

  // Mot de passe oublié
  const forgotPassword = useCallback(async (email) => {
    try {
      setError(null);
      await authApi.forgotPassword(email);
      return { success: true };
    } catch (err) {
      setError(err.message || 'Erreur lors de l\'envoi du lien de récupération');
      return { success: false, error: err.message };
    }
  }, []);

  // Réinitialiser le mot de passe
  const resetPassword = useCallback(async (token, password) => {
    try {
      setError(null);
      await authApi.resetPassword(token, password);
      return { success: true };
    } catch (err) {
      setError(err.message || 'Erreur lors de la réinitialisation');
      return { success: false, error: err.message };
    }
  }, []);

  // Mettre à jour le profil
  const updateProfile = useCallback(async (profileData) => {
    try {
      setError(null);
      const updatedUser = await authApi.updateProfile(profileData);
      setUser(updatedUser);
      return { success: true, user: updatedUser };
    } catch (err) {
      setError(err.message || 'Erreur lors de la mise à jour du profil');
      return { success: false, error: err.message };
    }
  }, []);

  // Changer le mot de passe
  const changePassword = useCallback(async (currentPassword, newPassword) => {
    try {
      setError(null);
      await authApi.changePassword(currentPassword, newPassword);
      return { success: true };
    } catch (err) {
      setError(err.message || 'Erreur lors du changement de mot de passe');
      return { success: false, error: err.message };
    }
  }, []);

  // Vérifier l'email
  const verifyEmail = useCallback(async (token) => {
    try {
      setError(null);
      await authApi.verifyEmail(token);

      // Mettre à jour le statut de vérification de l'utilisateur
      if (user) {
        setUser({ ...user, email_verified_at: new Date().toISOString() });
      }

      return { success: true };
    } catch (err) {
      setError(err.message || 'Erreur lors de la vérification de l\'email');
      return { success: false, error: err.message };
    }
  }, [user]);

  // Renvoyer l'email de vérification
  const resendVerificationEmail = useCallback(async () => {
    try {
      setError(null);
      await authApi.resendVerificationEmail();
      return { success: true };
    } catch (err) {
      setError(err.message || 'Erreur lors de l\'envoi de l\'email de vérification');
      return { success: false, error: err.message };
    }
  }, []);

  // Effacer les erreurs
  const clearError = useCallback(() => {
    setError(null);
  }, []);

  return {
    // État
    user,
    isLoading,
    isAuthenticated,
    error,

    // Méthodes d'authentification
    login,
    register,
    logout,

    // Gestion du mot de passe
    forgotPassword,
    resetPassword,
    changePassword,

    // Gestion du profil
    updateProfile,

    // Vérification email
    verifyEmail,
    resendVerificationEmail,

    // Utilitaires
    clearError,

    // Informations dérivées
    isEmailVerified: user?.email_verified_at != null,
    userRole: user?.role || 'guest',
    hasPermission: (permission) => user?.permissions?.includes(permission) || false
  };
};

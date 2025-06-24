import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_SHELVE_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000, // Augmenté à 30 secondes
});

// Intercepteur pour les requêtes
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('shelve_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Debug: log des requêtes en développement
    if (process.env.NODE_ENV === 'development') {
      console.log('API Request:', {
        method: config.method?.toUpperCase(),
        url: `${config.baseURL}${config.url}`,
        params: config.params,
        data: config.data
      });
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Intercepteur pour les réponses
api.interceptors.response.use(
  (response) => {
    // Debug: log des réponses en développement
    if (process.env.NODE_ENV === 'development') {
      console.log('API Response:', {
        status: response.status,
        url: response.config.url,
        data: response.data
      });
    }
    return response;
  },
  (error) => {
    console.error('API Error:', {
      message: error.message,
      code: error.code,
      response: error.response?.data,
      config: {
        method: error.config?.method,
        url: error.config?.url,
        params: error.config?.params
      }
    });

    if (error.response?.status === 401) {
      localStorage.removeItem('shelve_token');
      localStorage.removeItem('shelve_user');
      window.location.href = '/user/register';
    }
    return Promise.reject(error);
  }
);

// Authentication API
export const authApi = {
  login: (credentials) => api.post('/public/users/login', credentials),
  register: (userData) => api.post('/public/users/register', userData),
  logout: () => api.post('/public/users/logout'),
  verifyToken: (token) => api.post('/public/users/verify-token', { token }),
  forgotPassword: (email) => api.post('/public/users/forgot-password', { email }),
  resetPassword: (token, password) => api.post('/public/users/reset-password', { token, password }),
  updateProfile: (profileData) => api.patch('/public/users/profile', profileData),
  changePassword: (currentPassword, newPassword) => api.patch('/public/users/password', {
    current_password: currentPassword,
    new_password: newPassword
  }),
  verifyEmail: (token) => api.post('/public/users/verify-email', { token }),
  resendVerificationEmail: () => api.post('/public/users/resend-verification'),
};

export default api;

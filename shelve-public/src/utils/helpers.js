import { STORAGE_KEYS, NOTIFICATION_TYPES } from './constants';

// Local storage helpers
export const storage = {
  get: (key) => {
    try {
      const item = localStorage.getItem(key);
      return item ? JSON.parse(item) : null;
    } catch (error) {
      console.error(`Error getting ${key} from localStorage:`, error);
      return null;
    }
  },

  set: (key, value) => {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      return true;
    } catch (error) {
      console.error(`Error setting ${key} in localStorage:`, error);
      return false;
    }
  },

  remove: (key) => {
    try {
      localStorage.removeItem(key);
      return true;
    } catch (error) {
      console.error(`Error removing ${key} from localStorage:`, error);
      return false;
    }
  },

  clear: () => {
    try {
      localStorage.clear();
      return true;
    } catch (error) {
      console.error('Error clearing localStorage:', error);
      return false;
    }
  },
};

// User session helpers
export const auth = {
  getToken: () => storage.get(STORAGE_KEYS.TOKEN),
  setToken: (token) => storage.set(STORAGE_KEYS.TOKEN, token),
  removeToken: () => storage.remove(STORAGE_KEYS.TOKEN),

  getUser: () => storage.get(STORAGE_KEYS.USER),
  setUser: (user) => storage.set(STORAGE_KEYS.USER, user),
  removeUser: () => storage.remove(STORAGE_KEYS.USER),

  getUserId: () => storage.get(STORAGE_KEYS.USER_ID),
  setUserId: (userId) => storage.set(STORAGE_KEYS.USER_ID, userId),

  isAuthenticated: () => {
    const token = auth.getToken();
    return token !== null && token !== undefined && token !== '';
  },

  logout: () => {
    auth.removeToken();
    auth.removeUser();
    storage.remove(STORAGE_KEYS.USER_ID);
    storage.remove(STORAGE_KEYS.PREFERENCES);
  },
};

// String utilities
export const stringUtils = {
  truncate: (str, length = 100, suffix = '...') => {
    if (!str || str.length <= length) return str;
    return str.substring(0, length) + suffix;
  },

  capitalize: (str) => {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
  },

  capitalizeWords: (str) => {
    if (!str) return str;
    return str.split(' ').map(word => stringUtils.capitalize(word)).join(' ');
  },

  slugify: (str) => {
    if (!str) return '';
    return str
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  },

  removeHtml: (str) => {
    if (!str) return str;
    return str.replace(/<[^>]*>/g, '');
  },

  formatBytes: (bytes, decimals = 2) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  },

  // Truncate text to specified length
  truncateText: (text, maxLength = 100, suffix = '...') => {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength - suffix.length) + suffix;
  },
};

// Array utilities
export const arrayUtils = {
  unique: (arr) => [...new Set(arr)],

  chunk: (arr, size) => {
    const chunks = [];
    for (let i = 0; i < arr.length; i += size) {
      chunks.push(arr.slice(i, i + size));
    }
    return chunks;
  },

  groupBy: (arr, key) => {
    return arr.reduce((groups, item) => {
      const group = item[key];
      groups[group] = groups[group] || [];
      groups[group].push(item);
      return groups;
    }, {});
  },

  sortBy: (arr, key, direction = 'asc') => {
    return [...arr].sort((a, b) => {
      const aVal = typeof key === 'function' ? key(a) : a[key];
      const bVal = typeof key === 'function' ? key(b) : b[key];

      if (direction === 'desc') {
        return bVal > aVal ? 1 : -1;
      }
      return aVal > bVal ? 1 : -1;
    });
  },
};

// URL utilities
export const urlUtils = {
  buildQueryString: (params) => {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        if (Array.isArray(value)) {
          value.forEach(v => query.append(key, v));
        } else {
          query.append(key, value);
        }
      }
    });
    return query.toString();
  },

  parseQueryString: (queryString) => {
    const params = new URLSearchParams(queryString);
    const result = {};
    for (const [key, value] of params.entries()) {
      if (result[key]) {
        if (Array.isArray(result[key])) {
          result[key].push(value);
        } else {
          result[key] = [result[key], value];
        }
      } else {
        result[key] = value;
      }
    }
    return result;
  },

  getFileExtension: (url) => {
    return url.split('.').pop().toLowerCase();
  },

  isValidUrl: (string) => {
    try {
      new URL(string);
      return true;
    } catch (_) {
      return false;
    }
  },
};

// Error handling utilities
export const errorUtils = {
  getErrorMessage: (error) => {
    if (typeof error === 'string') return error;
    if (error?.response?.data?.message) return error.response.data.message;
    if (error?.message) return error.message;
    return 'Une erreur inattendue s\'est produite';
  },

  getErrorType: (error) => {
    if (error?.response?.status >= 500) return NOTIFICATION_TYPES.ERROR;
    if (error?.response?.status >= 400) return NOTIFICATION_TYPES.WARNING;
    return NOTIFICATION_TYPES.ERROR;
  },

  formatValidationErrors: (errors) => {
    if (typeof errors === 'object') {
      return Object.entries(errors).map(([field, messages]) => ({
        field,
        messages: Array.isArray(messages) ? messages : [messages],
      }));
    }
    return [];
  },
};

// Debounce utility
export const debounce = (func, wait, immediate = false) => {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      timeout = null;
      if (!immediate) func(...args);
    };
    const callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func(...args);
  };
};

// Throttle utility
export const throttle = (func, limit) => {
  let inThrottle;
  return function executedFunction(...args) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
};

// Copy to clipboard
export const copyToClipboard = async (text) => {
  try {
    if (navigator.clipboard) {
      await navigator.clipboard.writeText(text);
      return true;
    } else {
      // Fallback for older browsers
      const textArea = document.createElement('textarea');
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
      const successful = document.execCommand('copy');
      document.body.removeChild(textArea);
      return successful;
    }
  } catch (error) {
    console.error('Error copying to clipboard:', error);
    return false;
  }
};

// Generate random ID
export const generateId = (length = 8) => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let result = '';
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return result;
};

// Check if device is mobile
export const isMobile = () => {
  return window.innerWidth <= 768;
};

// Check if device is tablet
export const isTablet = () => {
  return window.innerWidth > 768 && window.innerWidth <= 1024;
};

// Check if device is desktop
export const isDesktop = () => {
  return window.innerWidth > 1024;
};

// Scroll to element
export const scrollToElement = (elementId, offset = 0) => {
  const element = document.getElementById(elementId);
  if (element) {
    const y = element.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }
};

// Format number with locale
export const formatNumber = (number, locale = 'fr-FR') => {
  return new Intl.NumberFormat(locale).format(number);
};

// Format date and time
export const formatDateTime = (date, options = {}) => {
  const defaultOptions = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    ...options
  };

  return new Date(date).toLocaleDateString('fr-FR', defaultOptions);
};

// Format file size in human readable format
export const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Truncate text to specified length
export const truncateText = (text, maxLength = 100, suffix = '...') => {
  if (!text || text.length <= maxLength) return text;
  return text.substring(0, maxLength - suffix.length) + suffix;
};

// Get contrast color (black or white) for background
export const getContrastColor = (hexColor) => {
  // Remove # if present
  const color = hexColor.replace('#', '');

  // Convert to RGB
  const r = parseInt(color.substr(0, 2), 16);
  const g = parseInt(color.substr(2, 2), 16);
  const b = parseInt(color.substr(4, 2), 16);

  // Calculate luminance
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

  return luminance > 0.5 ? '#000000' : '#ffffff';
};

const helpers = {
  storage,
  auth,
  stringUtils,
  arrayUtils,
  urlUtils,
  errorUtils,
  debounce,
  throttle,
  copyToClipboard,
  generateId,
  isMobile,
  isTablet,
  isDesktop,
  scrollToElement,
  formatNumber,
  formatDateTime,
  formatFileSize,
  truncateText,
  getContrastColor,
};

export default helpers;

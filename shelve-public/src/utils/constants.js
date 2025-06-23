// Application constants
export const APP_CONFIG = {
  NAME: process.env.REACT_APP_APP_NAME || 'Shelve Public',
  VERSION: process.env.REACT_APP_APP_VERSION || '1.0.0',
  API_URL: process.env.REACT_APP_SHELVE_API_URL || 'http://localhost:8000/api',
  WEBSOCKET_URL: process.env.REACT_APP_WEBSOCKET_URL || 'ws://localhost:6001',
};

// Feature flags
export const FEATURES = {
  CHAT_ENABLED: process.env.REACT_APP_ENABLE_CHAT === 'true',
  WEBSOCKETS_ENABLED: process.env.REACT_APP_ENABLE_WEBSOCKETS === 'true',
};

// File upload constants
export const FILE_UPLOAD = {
  MAX_SIZE: parseInt(process.env.REACT_APP_MAX_FILE_SIZE) || 10485760, // 10MB
  ALLOWED_TYPES: process.env.REACT_APP_ALLOWED_FILE_TYPES?.split(',') || ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'],
};

// Pagination
export const PAGINATION = {
  DEFAULT_PAGE_SIZE: 20,
  PAGE_SIZE_OPTIONS: [10, 20, 50, 100],
};

// Pagination defaults (alias)
export const PAGINATION_DEFAULTS = {
  PER_PAGE: PAGINATION.DEFAULT_PAGE_SIZE,
  PAGE_SIZE_OPTIONS: PAGINATION.PAGE_SIZE_OPTIONS,
};

// API endpoints
export const API_ENDPOINTS = {
  // Events
  EVENTS: '/public/events',
  EVENT_REGISTER: (id) => `/public/events/${id}/register`,

  // News
  NEWS: '/public/news',
  NEWS_LATEST: '/public/news/latest',

  // Records
  RECORDS: '/public/records',
  RECORDS_SEARCH: '/public/records/search',
  RECORDS_EXPORT: '/public/records/export',

  // Pages
  PAGES: '/public/pages',
  PAGE_BY_CATEGORY: (category) => `/public/pages/category/${category}`,

  // Templates
  TEMPLATES: '/public/templates',
  TEMPLATES_BY_TYPE: (type) => `/public/templates/type/${type}`,

  // Search
  SEARCH: '/public/search',
  SEARCH_SUGGESTIONS: '/public/search/suggestions',
  SEARCH_HISTORY: '/public/search/history',

  // Feedback
  FEEDBACK: '/public/feedback',
  FEEDBACK_TYPES: '/public/feedback/types',

  // Document Requests
  DOCUMENT_REQUESTS: '/public/document-requests',
  DOCUMENT_REQUEST_TRACK: (id) => `/public/document-requests/${id}/track`,

  // Responses
  RESPONSES: '/public/responses',

  // Users
  USERS: '/public/users',
  USER_REGISTER: '/public/users/register',
  USER_LOGIN: '/public/users/login',
  USER_PROFILE: '/public/users/profile',
  USER_ACTIVITY: '/public/users/activity',
  USER_PREFERENCES: '/public/users/preferences',
};

// Event types
export const EVENT_TYPES = [
  { label: 'Conférence', value: 'conference', color: '#3B82F6' },
  { label: 'Atelier', value: 'workshop', color: '#10B981' },
  { label: 'Exposition', value: 'exhibition', color: '#F59E0B' },
  { label: 'Visite guidée', value: 'guided_tour', color: '#8B5CF6' },
  { label: 'Formation', value: 'training', color: '#EF4444' },
  { label: 'Séminaire', value: 'seminar', color: '#06B6D4' },
  { label: 'Colloque', value: 'symposium', color: '#84CC16' },
  { label: 'Autre', value: 'other', color: '#6B7280' },
];

// Event status
export const EVENT_STATUS = {
  DRAFT: 'draft',
  PUBLISHED: 'published',
  CANCELLED: 'cancelled',
  COMPLETED: 'completed',
};

// Document request status
export const DOCUMENT_REQUEST_STATUS = {
  PENDING: 'pending',
  APPROVED: 'approved',
  REJECTED: 'rejected',
  PROCESSING: 'processing',
  COMPLETED: 'completed',
};

// Feedback types
export const FEEDBACK_TYPES = [
  { label: 'Suggestion', value: 'suggestion' },
  { label: 'Problème technique', value: 'bug' },
  { label: 'Question', value: 'question' },
  { label: 'Évaluation', value: 'rating' },
  { label: 'Autre', value: 'other' },
];

// Chat message types
export const CHAT_MESSAGE_TYPES = {
  USER: 'user',
  ASSISTANT: 'assistant',
  SYSTEM: 'system',
  ERROR: 'error',
};

// User roles
export const USER_ROLES = {
  GUEST: 'guest',
  REGISTERED: 'registered',
  ADMIN: 'admin',
};

// Search filters
export const SEARCH_FILTERS = {
  TYPE: 'type',
  DATE_RANGE: 'date_range',
  CATEGORY: 'category',
  STATUS: 'status',
  LOCATION: 'location',
};

// Notification types
export const NOTIFICATION_TYPES = {
  SUCCESS: 'success',
  ERROR: 'error',
  WARNING: 'warning',
  INFO: 'info',
};

// Local storage keys
export const STORAGE_KEYS = {
  TOKEN: 'shelve_token',
  REFRESH_TOKEN: 'shelve_refresh_token',
  USER: 'shelve_user',
  USER_ID: 'shelve_user_id',
  PREFERENCES: 'shelve_preferences',
  SEARCH_HISTORY: 'shelve_search_history',
  CHAT_SETTINGS: 'shelve_chat_settings',
};

// Date formats
export const DATE_FORMATS = {
  DISPLAY: 'dd/MM/yyyy',
  DISPLAY_WITH_TIME: 'dd/MM/yyyy HH:mm',
  API: 'yyyy-MM-dd',
  API_WITH_TIME: "yyyy-MM-dd'T'HH:mm:ss",
};

// Record types
export const RECORD_TYPES = [
  { label: 'Document', value: 'document', icon: 'document' },
  { label: 'Image', value: 'image', icon: 'image' },
  { label: 'Video', value: 'video', icon: 'video' },
  { label: 'Audio', value: 'audio', icon: 'audio' },
  { label: 'Archive', value: 'archive', icon: 'archive' },
  { label: 'Manuscrit', value: 'manuscript', icon: 'manuscript' },
  { label: 'Livre', value: 'book', icon: 'book' },
  { label: 'Correspondance', value: 'correspondence', icon: 'mail' },
  { label: 'Carte', value: 'map', icon: 'map' },
  { label: 'Plan', value: 'plan', icon: 'plan' },
  { label: 'Photographie', value: 'photography', icon: 'photo' },
  { label: 'Autre', value: 'other', icon: 'other' }
];

// Responsive breakpoints
export const BREAKPOINTS = {
  MOBILE: '768px',
  TABLET: '1024px',
  DESKTOP: '1200px',
};

const constants = {
  APP_CONFIG,
  FEATURES,
  FILE_UPLOAD,
  PAGINATION,
  PAGINATION_DEFAULTS,
  API_ENDPOINTS,
  EVENT_TYPES,
  EVENT_STATUS,
  DOCUMENT_REQUEST_STATUS,
  FEEDBACK_TYPES,
  CHAT_MESSAGE_TYPES,
  USER_ROLES,
  SEARCH_FILTERS,
  NOTIFICATION_TYPES,
  STORAGE_KEYS,
  DATE_FORMATS,
  RECORD_TYPES,
  BREAKPOINTS,
};

export default constants;

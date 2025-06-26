import api from './api';

// Events (PublicEventController)
export const eventsApi = {
  getEvents: (params = {}) => api.get('/public/events', { params }),
  getEvent: (id) => api.get(`/public/events/${id}`),
  registerToEvent: (eventId, data) => api.post(`/public/events/${eventId}/register`, data),
  getEventRegistrations: (eventId) => api.get(`/public/events/${eventId}/registrations`),
};

// News (PublicNewsController)
export const newsApi = {
  getNews: (params = {}) => api.get('/public/news', { params }),
  getNewsArticle: (id) => api.get(`/public/news/${id}`),
  getNewsById: (id) => api.get(`/public/news/${id}`),
  getLatestNews: (limit = 5) => api.get('/public/news/latest', { params: { limit } }),
};

// Records (PublicRecordApiController - New API)
export const recordsApi = {
  getRecords: (params = {}) => api.get('/public/records', { params }),
  getRecord: (id) => api.get(`/public/records/${id}`),
  searchRecords: (query, filters = {}) => api.post('/public/records/search', { 
    query, 
    filters: filters,
    per_page: filters.per_page || 20
  }),
  exportRecords: (params) => api.post('/public/records/export', { params, responseType: 'blob' }),
  getSearchSuggestions: (query) => api.get('/public/search/suggestions', { params: { q: query } }),
  getPopularSearches: () => api.get('/public/search/popular'),
  getStatistics: () => api.get('/public/records/statistics'),
  getFilters: () => api.get('/public/records/filters'),
  exportSearchResults: (query, filters = {}) => api.post('/public/records/export/search', { 
    query, 
    filters,
    format: 'csv'
  }, { responseType: 'blob' }),
};

// Pages (PublicPageController)
export const pagesApi = {
  getPages: (params = {}) => api.get('/public/pages', { params }),
  getPage: (slug) => api.get(`/public/pages/${slug}`),
  getPageByCategory: (category) => api.get(`/public/pages/category/${category}`),
};

// Templates (PublicTemplateController)
export const templatesApi = {
  getTemplates: (params = {}) => api.get('/public/templates', { params }),
  getTemplate: (id) => api.get(`/public/templates/${id}`),
  getTemplatesByType: (type) => api.get(`/public/templates/type/${type}`),
};

// Search (PublicSearchLogController)
export const searchApi = {
  performSearch: (query, filters = {}) => api.post('/public/search', { query, ...filters }),
  getSearchHistory: (userId) => api.get(`/public/search/history/${userId}`),
  getSearchSuggestions: (query) => api.get('/public/search/suggestions', { params: { q: query } }),
  saveSearch: (data) => api.post('/public/search/save', data),
};

// Feedback (PublicFeedbackController)
export const feedbackApi = {
  submitFeedback: (data) => api.post('/public/feedback', data),
  getFeedbackStatus: (id) => api.get(`/public/feedback/${id}/status`),
  getFeedbackTypes: () => api.get('/public/feedback/types'),
};

// Document Requests (PublicDocumentRequestController)
export const documentRequestApi = {
  submitRequest: (data) => api.post('/public/document-requests', data),
  trackRequest: (id) => api.get(`/public/document-requests/${id}/track`),
  getRequestHistory: (userId) => api.get(`/public/document-requests/user/${userId}`),
  updateRequest: (id, data) => api.patch(`/public/document-requests/${id}`, data),
};

// Responses (PublicResponseController)
export const responsesApi = {
  getResponses: (params = {}) => api.get('/public/responses', { params }),
  submitResponse: (data) => api.post('/public/responses', data),
  getResponse: (id) => api.get(`/public/responses/${id}`),
  getResponsesByRequest: (requestId) => api.get(`/public/responses/request/${requestId}`),
};

// User Management (PublicUserController)
export const userApi = {
  registerUser: (data) => api.post('/public/users/register', data),
  loginUser: (data) => api.post('/public/users/login', data),
  getUserProfile: (id) => api.get(`/public/users/${id}`),
  updateUserProfile: (id, data) => api.patch(`/public/users/${id}`, data),
  getUserActivity: (id) => api.get(`/public/users/${id}/activity`),
  getUserPreferences: (id) => api.get(`/public/users/${id}/preferences`),
  updateUserPreferences: (id, data) => api.patch(`/public/users/${id}/preferences`, data),
  getUserDashboard: () => api.get('/public/users/dashboard'),
  deleteUserAccount: () => api.delete('/public/users/account'),
};

const shelveApi = {
  events: eventsApi,
  news: newsApi,
  records: recordsApi,
  pages: pagesApi,
  templates: templatesApi,
  search: searchApi,
  feedback: feedbackApi,
  documentRequest: documentRequestApi,
  responses: responsesApi,
  user: userApi,

  // Convenience methods
  getEvents: eventsApi.getEvents,
  getEvent: eventsApi.getEvent,
  getNews: newsApi.getNews,
  getNewsById: newsApi.getNewsById,
  getRecords: recordsApi.getRecords,
  getRecord: recordsApi.getRecord,
  searchRecords: recordsApi.searchRecords,
  submitDocumentRequest: documentRequestApi.submitRequest,
  submitFeedback: feedbackApi.submitFeedback,
  getUserDashboard: userApi.getUserDashboard,
  updateUserProfile: userApi.updateUserProfile,
  deleteUserAccount: userApi.deleteUserAccount,
};

export default shelveApi;

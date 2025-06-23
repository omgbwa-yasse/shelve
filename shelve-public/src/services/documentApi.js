import api from './api';

// Document and attachment handling
export const documentApi = {
  // Response Attachments (PublicResponseAttachmentController)
  uploadAttachment: (responseId, formData, onProgress = null) => {
    const config = {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    };

    if (onProgress) {
      config.onUploadProgress = (progressEvent) => {
        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        onProgress(percentCompleted);
      };
    }

    return api.post(`/public/responses/${responseId}/attachments`, formData, config);
  },

  downloadAttachment: (attachmentId) => {
    return api.get(`/public/attachments/${attachmentId}/download`, {
      responseType: 'blob',
    });
  },

  getAttachment: (attachmentId) => api.get(`/public/attachments/${attachmentId}`),

  deleteAttachment: (attachmentId) => api.delete(`/public/attachments/${attachmentId}`),

  getAttachmentsByResponse: (responseId) => api.get(`/public/responses/${responseId}/attachments`),

  // File validation helpers
  validateFile: (file) => {
    const maxSize = parseInt(process.env.REACT_APP_MAX_FILE_SIZE) || 10485760; // 10MB default
    const allowedTypes = process.env.REACT_APP_ALLOWED_FILE_TYPES?.split(',') || ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

    const errors = [];

    if (file.size > maxSize) {
      errors.push(`Le fichier dépasse la taille maximale autorisée (${Math.round(maxSize / 1024 / 1024)}MB)`);
    }

    const fileExtension = file.name.split('.').pop().toLowerCase();
    if (!allowedTypes.includes(fileExtension)) {
      errors.push(`Type de fichier non autorisé. Types acceptés: ${allowedTypes.join(', ')}`);
    }

    return {
      isValid: errors.length === 0,
      errors,
    };
  },

  // File utilities
  formatFileSize: (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  },

  getFileIcon: (filename) => {
    const extension = filename.split('.').pop().toLowerCase();
    const iconMap = {
      pdf: '📄',
      doc: '📝',
      docx: '📝',
      xls: '📊',
      xlsx: '📊',
      ppt: '📈',
      pptx: '📈',
      jpg: '🖼️',
      jpeg: '🖼️',
      png: '🖼️',
      gif: '🖼️',
      zip: '📦',
      rar: '📦',
      txt: '📃',
    };
    return iconMap[extension] || '📎';
  },

  // Preview functionality
  canPreview: (filename) => {
    const extension = filename.split('.').pop().toLowerCase();
    const previewableTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    return previewableTypes.includes(extension);
  },

  getPreviewUrl: (attachmentId) => {
    return `${process.env.REACT_APP_SHELVE_API_URL}/public/attachments/${attachmentId}/preview`;
  },

  // Bulk operations
  uploadMultipleAttachments: async (responseId, files, onProgress = null) => {
    const results = [];
    let completedUploads = 0;

    for (const file of files) {
      const formData = new FormData();
      formData.append('file', file);

      try {
        const result = await documentApi.uploadAttachment(responseId, formData, (progress) => {
          if (onProgress) {
            const totalProgress = ((completedUploads / files.length) * 100) + (progress / files.length);
            onProgress(Math.round(totalProgress));
          }
        });
        results.push({ file: file.name, success: true, data: result.data });
        completedUploads++;
      } catch (error) {
        results.push({ file: file.name, success: false, error: error.message });
        completedUploads++;
      }
    }

    return results;
  },

  // Document request helpers
  createDocumentRequestForm: () => {
    const formData = new FormData();
    return {
      append: (key, value) => formData.append(key, value),
      appendFile: (key, file) => formData.append(key, file),
      getFormData: () => formData,
    };
  },
};

export default documentApi;

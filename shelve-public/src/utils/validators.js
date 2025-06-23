// Email validation
export const isValidEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

// Alias for backward compatibility
export const validateEmail = isValidEmail;

// Phone validation (French format)
export const isValidPhone = (phone) => {
  const phoneRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
  return phoneRegex.test(phone);
};

// Alias for backward compatibility
export const validatePhone = isValidPhone;

// Password validation
export const validatePassword = (password) => {
  const errors = [];

  if (!password) {
    errors.push('Le mot de passe est requis');
    return { isValid: false, errors };
  }

  if (password.length < 8) {
    errors.push('Le mot de passe doit contenir au moins 8 caractères');
  }

  if (!/[A-Z]/.test(password)) {
    errors.push('Le mot de passe doit contenir au moins une majuscule');
  }

  if (!/[a-z]/.test(password)) {
    errors.push('Le mot de passe doit contenir au moins une minuscule');
  }

  if (!/\d/.test(password)) {
    errors.push('Le mot de passe doit contenir au moins un chiffre');
  }

  if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    errors.push('Le mot de passe doit contenir au moins un caractère spécial');
  }

  return {
    isValid: errors.length === 0,
    errors,
  };
};

// Required field validation
export const isRequired = (value, fieldName = 'Ce champ') => {
  if (value === null || value === undefined || value === '') {
    return {
      isValid: false,
      error: `${fieldName} est requis`,
    };
  }

  if (typeof value === 'string' && value.trim() === '') {
    return {
      isValid: false,
      error: `${fieldName} est requis`,
    };
  }

  return { isValid: true };
};

// Minimum length validation
export const hasMinLength = (value, minLength, fieldName = 'Ce champ') => {
  if (!value || value.length < minLength) {
    return {
      isValid: false,
      error: `${fieldName} doit contenir au moins ${minLength} caractères`,
    };
  }
  return { isValid: true };
};

// Maximum length validation
export const hasMaxLength = (value, maxLength, fieldName = 'Ce champ') => {
  if (value && value.length > maxLength) {
    return {
      isValid: false,
      error: `${fieldName} ne peut pas dépasser ${maxLength} caractères`,
    };
  }
  return { isValid: true };
};

// URL validation
export const isValidUrl = (url) => {
  try {
    new URL(url);
    return { isValid: true };
  } catch (error) {
    return {
      isValid: false,
      error: 'URL invalide',
    };
  }
};

// Date validation
export const isValidDate = (date, fieldName = 'Cette date') => {
  if (!date) {
    return {
      isValid: false,
      error: `${fieldName} est requise`,
    };
  }

  const dateObj = new Date(date);
  if (isNaN(dateObj.getTime())) {
    return {
      isValid: false,
      error: `${fieldName} est invalide`,
    };
  }

  return { isValid: true };
};

// Future date validation
export const isFutureDate = (date, fieldName = 'Cette date') => {
  const dateValidation = isValidDate(date, fieldName);
  if (!dateValidation.isValid) {
    return dateValidation;
  }

  const dateObj = new Date(date);
  const now = new Date();

  if (dateObj <= now) {
    return {
      isValid: false,
      error: `${fieldName} doit être dans le futur`,
    };
  }

  return { isValid: true };
};

// Past date validation
export const isPastDate = (date, fieldName = 'Cette date') => {
  const dateValidation = isValidDate(date, fieldName);
  if (!dateValidation.isValid) {
    return dateValidation;
  }

  const dateObj = new Date(date);
  const now = new Date();

  if (dateObj >= now) {
    return {
      isValid: false,
      error: `${fieldName} doit être dans le passé`,
    };
  }

  return { isValid: true };
};

// Numeric validation
export const isNumeric = (value, fieldName = 'Cette valeur') => {
  if (value === null || value === undefined || value === '') {
    return {
      isValid: false,
      error: `${fieldName} est requise`,
    };
  }

  if (isNaN(value) || isNaN(parseFloat(value))) {
    return {
      isValid: false,
      error: `${fieldName} doit être un nombre`,
    };
  }

  return { isValid: true };
};

// Positive number validation
export const isPositiveNumber = (value, fieldName = 'Cette valeur') => {
  const numericValidation = isNumeric(value, fieldName);
  if (!numericValidation.isValid) {
    return numericValidation;
  }

  const num = parseFloat(value);
  if (num <= 0) {
    return {
      isValid: false,
      error: `${fieldName} doit être positive`,
    };
  }

  return { isValid: true };
};

// Range validation
export const isInRange = (value, min, max, fieldName = 'Cette valeur') => {
  const numericValidation = isNumeric(value, fieldName);
  if (!numericValidation.isValid) {
    return numericValidation;
  }

  const num = parseFloat(value);
  if (num < min || num > max) {
    return {
      isValid: false,
      error: `${fieldName} doit être entre ${min} et ${max}`,
    };
  }

  return { isValid: true };
};

// File validation
export const validateFile = (file, options = {}) => {
  const {
    maxSize = 10 * 1024 * 1024, // 10MB default
    allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'],
    required = false,
  } = options;

  const errors = [];

  if (!file) {
    if (required) {
      errors.push('Un fichier est requis');
    }
    return {
      isValid: !required,
      errors,
    };
  }

  // Check file size
  if (file.size > maxSize) {
    const maxSizeMB = Math.round(maxSize / 1024 / 1024);
    errors.push(`La taille du fichier ne peut pas dépasser ${maxSizeMB}MB`);
  }

  // Check file type
  const fileExtension = file.name.split('.').pop().toLowerCase();
  if (!allowedTypes.includes(fileExtension)) {
    errors.push(`Type de fichier non autorisé. Types acceptés: ${allowedTypes.join(', ')}`);
  }

  return {
    isValid: errors.length === 0,
    errors,
  };
};

// Multiple files validation
export const validateFiles = (files, options = {}) => {
  const {
    maxFiles = 5,
    maxTotalSize = 50 * 1024 * 1024, // 50MB default
    ...fileOptions
  } = options;

  const errors = [];

  if (!files || files.length === 0) {
    if (fileOptions.required) {
      errors.push('Au moins un fichier est requis');
    }
    return {
      isValid: !fileOptions.required,
      errors,
    };
  }

  // Check number of files
  if (files.length > maxFiles) {
    errors.push(`Vous ne pouvez pas sélectionner plus de ${maxFiles} fichiers`);
  }

  // Check total size
  const totalSize = Array.from(files).reduce((sum, file) => sum + file.size, 0);
  if (totalSize > maxTotalSize) {
    const maxTotalSizeMB = Math.round(maxTotalSize / 1024 / 1024);
    errors.push(`La taille totale des fichiers ne peut pas dépasser ${maxTotalSizeMB}MB`);
  }

  // Validate each file
  Array.from(files).forEach((file, index) => {
    const fileValidation = validateFile(file, { ...fileOptions, required: false });
    if (!fileValidation.isValid) {
      fileValidation.errors.forEach(error => {
        errors.push(`Fichier ${index + 1}: ${error}`);
      });
    }
  });

  return {
    isValid: errors.length === 0,
    errors,
  };
};

// Form validation utility
export const validateForm = (formData, validationRules) => {
  const errors = {};
  let isValid = true;

  Object.entries(validationRules).forEach(([fieldName, rules]) => {
    const fieldValue = formData[fieldName];
    const fieldErrors = [];

    rules.forEach(rule => {
      let validation;

      if (typeof rule === 'function') {
        validation = rule(fieldValue, fieldName);
      } else if (typeof rule === 'object') {
        const { validator, options = {} } = rule;
        validation = validator(fieldValue, { fieldName, ...options });
      }

      if (validation && !validation.isValid) {
        if (validation.error) {
          fieldErrors.push(validation.error);
        } else if (validation.errors) {
          fieldErrors.push(...validation.errors);
        }
      }
    });

    if (fieldErrors.length > 0) {
      errors[fieldName] = fieldErrors;
      isValid = false;
    }
  });

  return {
    isValid,
    errors,
  };
};

// Custom validator creator
export const createValidator = (validatorFn, errorMessage) => {
  return (value, options = {}) => {
    const { fieldName = 'Ce champ' } = options;
    const isValid = validatorFn(value);

    return {
      isValid,
      error: isValid ? null : errorMessage.replace('{field}', fieldName),
    };
  };
};

// Common validation rules
export const validationRules = {
  required: (fieldName) => (value) => isRequired(value, fieldName),
  email: () => (value) => {
    if (!value) return { isValid: true }; // Optional field
    return {
      isValid: isValidEmail(value),
      error: isValidEmail(value) ? null : 'Email invalide',
    };
  },
  phone: () => (value) => {
    if (!value) return { isValid: true }; // Optional field
    return {
      isValid: isValidPhone(value),
      error: isValidPhone(value) ? null : 'Numéro de téléphone invalide',
    };
  },
  minLength: (min, fieldName) => (value) => hasMinLength(value, min, fieldName),
  maxLength: (max, fieldName) => (value) => hasMaxLength(value, max, fieldName),
  password: () => validatePassword,
  url: () => isValidUrl,
  date: (fieldName) => (value) => isValidDate(value, fieldName),
  futureDate: (fieldName) => (value) => isFutureDate(value, fieldName),
  pastDate: (fieldName) => (value) => isPastDate(value, fieldName),
  numeric: (fieldName) => (value) => isNumeric(value, fieldName),
  positive: (fieldName) => (value) => isPositiveNumber(value, fieldName),
  range: (min, max, fieldName) => (value) => isInRange(value, min, max, fieldName),
};

export default {
  isValidEmail,
  isValidPhone,
  validatePassword,
  isRequired,
  hasMinLength,
  hasMaxLength,
  isValidUrl,
  isValidDate,
  isFutureDate,
  isPastDate,
  isNumeric,
  isPositiveNumber,
  isInRange,
  validateFile,
  validateFiles,
  validateForm,
  createValidator,
  validationRules,
};

// Classes d'erreurs personnalisées pour le système MCP

class BaseError extends Error {
    constructor(message, statusCode = 500, code = 'INTERNAL_ERROR') {
        super(message);
        this.name = this.constructor.name;
        this.statusCode = statusCode;
        this.code = code;
        this.timestamp = new Date().toISOString();
        
        Error.captureStackTrace(this, this.constructor);
    }

    toJSON() {
        return {
            name: this.name,
            message: this.message,
            code: this.code,
            statusCode: this.statusCode,
            timestamp: this.timestamp
        };
    }
}

class ValidationError extends BaseError {
    constructor(message, details = null) {
        super(message, 400, 'VALIDATION_ERROR');
        this.details = details;
    }

    toJSON() {
        return {
            ...super.toJSON(),
            details: this.details
        };
    }
}

class OllamaError extends BaseError {
    constructor(message, originalError = null) {
        super(message, 503, 'OLLAMA_ERROR');
        this.originalError = originalError;
    }

    toJSON() {
        return {
            ...super.toJSON(),
            originalError: this.originalError?.message
        };
    }
}

class DatabaseError extends BaseError {
    constructor(message, query = null) {
        super(message, 500, 'DATABASE_ERROR');
        this.query = query;
    }

    toJSON() {
        return {
            ...super.toJSON(),
            query: this.query
        };
    }
}

class NotFoundError extends BaseError {
    constructor(resource, identifier = null) {
        const message = identifier 
            ? `${resource} avec l'identifiant '${identifier}' introuvable`
            : `${resource} introuvable`;
        
        super(message, 404, 'NOT_FOUND');
        this.resource = resource;
        this.identifier = identifier;
    }

    toJSON() {
        return {
            ...super.toJSON(),
            resource: this.resource,
            identifier: this.identifier
        };
    }
}

class AuthenticationError extends BaseError {
    constructor(message = 'Authentication requise') {
        super(message, 401, 'AUTHENTICATION_ERROR');
    }
}

class AuthorizationError extends BaseError {
    constructor(message = 'Permissions insuffisantes') {
        super(message, 403, 'AUTHORIZATION_ERROR');
    }
}

class RateLimitError extends BaseError {
    constructor(message = 'Limite de taux dépassée') {
        super(message, 429, 'RATE_LIMIT_ERROR');
    }
}

class ConfigurationError extends BaseError {
    constructor(message, parameter = null) {
        super(message, 500, 'CONFIGURATION_ERROR');
        this.parameter = parameter;
    }

    toJSON() {
        return {
            ...super.toJSON(),
            parameter: this.parameter
        };
    }
}

class ProcessingError extends BaseError {
    constructor(message, operation = null, details = null) {
        super(message, 500, 'PROCESSING_ERROR');
        this.operation = operation;
        this.details = details;
    }

    toJSON() {
        return {
            ...super.toJSON(),
            operation: this.operation,
            details: this.details
        };
    }
}

// Fonction utilitaire pour créer une erreur à partir d'un objet Error existant
function createErrorFromException(error, defaultMessage = 'Une erreur inattendue s\'est produite') {
    if (error instanceof BaseError) {
        return error;
    }

    // Erreurs spécifiques
    if (error.code === 'ECONNREFUSED') {
        return new OllamaError('Service Ollama indisponible', error);
    }

    if (error.code === 'ETIMEDOUT') {
        return new OllamaError('Timeout de la requête Ollama', error);
    }

    if (error.code && error.code.startsWith('ER_')) {
        return new DatabaseError(error.message, error.sql);
    }

    // Erreur générique
    return new BaseError(error.message || defaultMessage, 500, 'UNEXPECTED_ERROR');
}

// Fonction pour déterminer si une erreur est récupérable
function isRecoverableError(error) {
    if (error instanceof BaseError) {
        return [
            'OLLAMA_ERROR',
            'RATE_LIMIT_ERROR',
            'DATABASE_ERROR'
        ].includes(error.code);
    }

    return false;
}

module.exports = {
    BaseError,
    ValidationError,
    OllamaError,
    DatabaseError,
    NotFoundError,
    AuthenticationError,
    AuthorizationError,
    RateLimitError,
    ConfigurationError,
    ProcessingError,
    createErrorFromException,
    isRecoverableError
};

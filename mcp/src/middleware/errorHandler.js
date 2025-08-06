const logger = require('../utils/logger');
const { BaseError, createErrorFromException } = require('../utils/errors');

const errorHandler = (err, req, res, next) => {
    // Convertir l'erreur en erreur typée si nécessaire
    const error = err instanceof BaseError ? err : createErrorFromException(err);

    // Logging détaillé de l'erreur
    const logData = {
        error: error.toJSON(),
        request: {
            method: req.method,
            url: req.url,
            headers: req.headers,
            body: req.body,
            params: req.params,
            query: req.query,
            ip: req.ip,
            userAgent: req.get('User-Agent')
        },
        timestamp: new Date().toISOString()
    };

    // Niveau de log selon la sévérité
    if (error.statusCode >= 500) {
        logger.error('Erreur serveur:', logData);
    } else if (error.statusCode >= 400) {
        logger.warn('Erreur client:', logData);
    } else {
        logger.info('Information:', logData);
    }

    // Réponse selon l'environnement
    const isDevelopment = process.env.NODE_ENV === 'development';
    
    const errorResponse = {
        success: false,
        error: {
            message: error.message,
            code: error.code,
            statusCode: error.statusCode,
            timestamp: error.timestamp
        }
    };

    // Ajouter des détails en mode développement
    if (isDevelopment) {
        errorResponse.error.stack = error.stack;
        errorResponse.error.details = error.details || error.originalError;
        errorResponse.request = {
            method: req.method,
            url: req.url,
            body: req.body
        };
    }

    // Réponse HTTP
    res.status(error.statusCode).json(errorResponse);
};

// Middleware pour les routes non trouvées
const notFoundHandler = (req, res, next) => {
    const error = {
        success: false,
        error: {
            message: `Route ${req.method} ${req.path} non trouvée`,
            code: 'ROUTE_NOT_FOUND',
            statusCode: 404,
            timestamp: new Date().toISOString()
        }
    };

    logger.warn('Route non trouvée:', {
        method: req.method,
        url: req.url,
        ip: req.ip
    });

    res.status(404).json(error);
};

// Middleware pour capturer les erreurs asynchrones
const asyncHandler = (fn) => {
    return (req, res, next) => {
        Promise.resolve(fn(req, res, next)).catch(next);
    };
};

// Middleware de validation générique
const validateRequest = (schema) => {
    return asyncHandler(async (req, res, next) => {
        try {
            const { error, value } = schema.validate(req.body, {
                abortEarly: false,
                stripUnknown: true
            });

            if (error) {
                const ValidationError = require('../utils/errors').ValidationError;
                const details = error.details.map(detail => ({
                    field: detail.path.join('.'),
                    message: detail.message,
                    value: detail.context?.value
                }));

                throw new ValidationError('Données de requête invalides', details);
            }

            req.validatedBody = value;
            next();
        } catch (err) {
            next(err);
        }
    });
};

module.exports = {
    errorHandler,
    notFoundHandler,
    asyncHandler,
    validateRequest
};

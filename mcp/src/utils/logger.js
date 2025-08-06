const winston = require('winston');
const path = require('path');

// Configuration des niveaux de log personnalisés
const logLevels = {
    error: 0,
    warn: 1,
    info: 2,
    http: 3,
    debug: 4
};

const logColors = {
    error: 'red',
    warn: 'yellow',
    info: 'green',
    http: 'magenta',
    debug: 'blue'
};

winston.addColors(logColors);

// Format pour les logs
const logFormat = winston.format.combine(
    winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
    winston.format.errors({ stack: true }),
    winston.format.colorize({ all: true }),
    winston.format.printf(({ level, message, timestamp, stack, ...meta }) => {
        let log = `${timestamp} [${level}]: ${message}`;
        
        if (Object.keys(meta).length > 0) {
            log += ` ${JSON.stringify(meta)}`;
        }
        
        if (stack) {
            log += `\n${stack}`;
        }
        
        return log;
    })
);

// Format pour les fichiers (sans couleurs)
const fileFormat = winston.format.combine(
    winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
    winston.format.errors({ stack: true }),
    winston.format.json()
);

// Configuration des transports
const transports = [
    // Console
    new winston.transports.Console({
        level: process.env.LOG_LEVEL || 'info',
        format: logFormat
    }),
    
    // Fichier pour toutes les logs
    new winston.transports.File({
        filename: path.join(__dirname, '../../logs/app.log'),
        level: 'debug',
        format: fileFormat,
        maxsize: 5242880, // 5MB
        maxFiles: 5
    }),
    
    // Fichier spécifique pour les erreurs
    new winston.transports.File({
        filename: path.join(__dirname, '../../logs/error.log'),
        level: 'error',
        format: fileFormat,
        maxsize: 5242880, // 5MB
        maxFiles: 5
    })
];

// Créer le logger
const logger = winston.createLogger({
    levels: logLevels,
    level: process.env.LOG_LEVEL || 'info',
    format: fileFormat,
    transports,
    exceptionHandlers: [
        new winston.transports.File({
            filename: path.join(__dirname, '../../logs/exceptions.log'),
            format: fileFormat
        })
    ],
    rejectionHandlers: [
        new winston.transports.File({
            filename: path.join(__dirname, '../../logs/rejections.log'),
            format: fileFormat
        })
    ]
});

// Si on n'est pas en production, ajouter des logs plus verbeux
if (process.env.NODE_ENV !== 'production') {
    logger.add(new winston.transports.Console({
        format: winston.format.combine(
            winston.format.colorize(),
            winston.format.simple()
        )
    }));
}

// Méthodes utilitaires
logger.request = (req, res, next) => {
    const start = Date.now();
    
    res.on('finish', () => {
        const duration = Date.now() - start;
        const { method, url, ip } = req;
        const { statusCode } = res;
        
        logger.http(`${method} ${url}`, {
            statusCode,
            duration: `${duration}ms`,
            ip,
            userAgent: req.get('User-Agent')
        });
    });
    
    if (next) next();
};

logger.performance = (operation, duration) => {
    logger.info(`Performance: ${operation}`, {
        duration: `${duration}ms`,
        type: 'performance'
    });
};

logger.security = (event, details = {}) => {
    logger.warn(`Security event: ${event}`, {
        ...details,
        type: 'security',
        timestamp: new Date().toISOString()
    });
};

module.exports = logger;

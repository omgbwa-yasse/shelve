const winston = require('winston');

// Configurer le logger avec Winston
const logger = winston.createLogger({
  level: process.env.LOG_LEVEL || 'info',
  format: winston.format.combine(
    winston.format.timestamp({
      format: 'YYYY-MM-DD HH:mm:ss'
    }),
    winston.format.printf(info => `${info.timestamp} [${info.level.toUpperCase()}]: ${info.message}`)
  ),
  transports: [
    // Logs sur la console
    new winston.transports.Console({
      format: winston.format.combine(
        winston.format.colorize(),
        winston.format.printf(info => `${info.timestamp} [${info.level}]: ${info.message}`)
      )
    }),
    // Logs dans un fichier
    new winston.transports.File({
      filename: 'logs/mcp-error.log',
      level: 'error'
    }),
    new winston.transports.File({
      filename: 'logs/mcp-combined.log'
    })
  ]
});

// Si nous sommes en environnement de développement, afficher plus d'informations
if (process.env.NODE_ENV !== 'production') {
  logger.add(new winston.transports.Console({
    format: winston.format.simple()
  }));
}

module.exports = { logger };

const database = require('./database');
const ollama = require('./ollama');
const server = require('./server');

module.exports = {
    database,
    ollama,
    server,
    port: process.env.PORT || 3001,
    nodeEnv: process.env.NODE_ENV || 'development',
    logLevel: process.env.LOG_LEVEL || 'info',
    maxFileSize: process.env.MAX_FILE_SIZE || '10mb',
    rateLimitWindow: process.env.RATE_LIMIT_WINDOW || 15 * 60 * 1000, // 15 minutes
    rateLimitMax: process.env.RATE_LIMIT_MAX || 100
};

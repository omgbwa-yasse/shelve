const express = require('express');
const cors = require('cors');
const dotenv = require('dotenv');

const config = require('./config');
const routes = require('./routes');
const { errorHandler } = require('./middleware/errorHandler');
const logger = require('./utils/logger');

// Charger les variables d'environnement
dotenv.config();

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware global
app.use(cors());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Logging des requêtes
app.use((req, res, next) => {
    logger.info(`${req.method} ${req.path}`, {
        ip: req.ip,
        userAgent: req.get('User-Agent')
    });
    next();
});

// Routes
app.use('/api', routes);

// Route de test
app.get('/', (req, res) => {
    res.json({
        name: 'Shelve MCP Server',
        version: '1.0.0',
        status: 'running',
        timestamp: new Date().toISOString()
    });
});

// Gestion d'erreurs
app.use(errorHandler);

// Démarrage du serveur
app.listen(PORT, () => {
    logger.info(`Serveur MCP démarré sur le port ${PORT}`);
    logger.info(`Environnement: ${process.env.NODE_ENV || 'development'}`);
});

// Gestion des signaux de fermeture
process.on('SIGTERM', () => {
    logger.info('Signal SIGTERM reçu, fermeture du serveur...');
    process.exit(0);
});

process.on('SIGINT', () => {
    logger.info('Signal SIGINT reçu, fermeture du serveur...');
    process.exit(0);
});

module.exports = app;

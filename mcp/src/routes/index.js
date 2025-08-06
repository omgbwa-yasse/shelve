const express = require('express');
const recordsRoutes = require('./api/records');
const modelsRoutes = require('./api/models');
const settingsRoutes = require('./api/settings');
const healthRoutes = require('./api/health');
const { notFoundHandler } = require('../middleware/errorHandler');

const router = express.Router();

// Middleware pour toutes les routes API
router.use((req, res, next) => {
    res.header('Content-Type', 'application/json');
    next();
});

// Routes API
router.use('/records', recordsRoutes);
router.use('/models', modelsRoutes);
router.use('/settings', settingsRoutes);
router.use('/health', healthRoutes);

// Route racine de l'API
router.get('/', (req, res) => {
    res.json({
        name: 'Shelve MCP Server API',
        version: '1.0.0',
        status: 'running',
        timestamp: new Date().toISOString(),
        endpoints: {
            records: {
                path: '/api/records',
                description: 'Traitement des documents (résumés, mots-clés, analyse)',
                methods: ['GET', 'POST']
            },
            models: {
                path: '/api/models',
                description: 'Gestion des modèles IA Ollama',
                methods: ['GET']
            },
            settings: {
                path: '/api/settings',
                description: 'Configuration du système',
                methods: ['GET', 'POST', 'PUT']
            },
            health: {
                path: '/api/health',
                description: 'Vérification de l\'état du système',
                methods: ['GET']
            }
        },
        documentation: {
            api: '/docs/API.md',
            deployment: '/docs/DEPLOYMENT.md',
            development: '/docs/DEVELOPMENT.md'
        }
    });
});

// Gestion des routes API non trouvées
router.use('*', notFoundHandler);

module.exports = router;

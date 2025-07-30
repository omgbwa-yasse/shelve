const recordHandler = require('./handlers/recordHandler');
const modelHandler = require('./handlers/modelHandler');
const settingsHandler = require('./handlers/settingsHandler');
const recordRoutes = require('./routes/recordRoutes');
const { logger } = require('./utils/logger');

/**
 * Configurer toutes les routes de l'application
 * @param {Express} app - Instance de l'application Express
 */
function setupRoutes(app) {
  // Middleware pour loguer toutes les requêtes
  app.use((req, res, next) => {
    logger.info(`${req.method} ${req.url}`);
    next();
  });

  // Route de base pour vérifier que le serveur est opérationnel
  app.get('/', (req, res) => {
    res.json({
      status: 'OK',
      message: 'Serveur MCP opérationnel',
      version: '1.0.0'
    });
  });

  // Routes liées aux records
  app.post('/records/summarize', recordHandler.summarize);
  app.post('/records/title', recordHandler.reformatTitle);
  app.post('/records/keywords', recordHandler.extractKeywords);
  app.post('/records/analyze', recordHandler.analyzeContent);

  // Routes spécifiques aux records avec ID dans l'URL
  app.use('/api/records', recordRoutes);

  // Routes liées aux modèles
  app.get('/models', modelHandler.getAvailableModels);
  app.get('/models/default', modelHandler.getDefaultModel);

  // Routes liées aux paramètres
  app.get('/settings', settingsHandler.getSettings);
  app.get('/settings/:name', settingsHandler.getSetting);

  // Route 404 pour les endpoints non trouvés
  app.use('*', (req, res) => {
    res.status(404).json({
      error: true,
      message: 'Route non trouvée'
    });
  });
}

module.exports = { setupRoutes };

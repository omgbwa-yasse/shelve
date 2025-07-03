// Point d'entrée principal pour le serveur MCP
const express = require('express');
const config = require('./src/config');
const apiRoutes = require('./src/routes/api.routes');
const authMiddleware = require('./src/middleware/auth.middleware');
const enrichmentController = require('./src/controllers/enrichment.controller');

// Initialiser l'application Express
const app = express();

// Middleware pour parser le JSON
app.use(express.json());

// Routes de santé (publiques)
app.get('/health', enrichmentController.healthCheck);

// Middleware d'authentification pour les routes API
app.use('/api', authMiddleware);

// Routes API
app.use('/api', apiRoutes);

// Route 404 pour les routes non trouvées
app.use((req, res) => {
  res.status(404).json({
    status: 'error',
    message: `Route non trouvée: ${req.method} ${req.originalUrl}`
  });
});

// Middleware de gestion des erreurs
app.use((err, req, res, next) => {
  console.error('Erreur non gérée:', err);
  res.status(err.status || 500).json({
    status: 'error',
    message: err.message || 'Erreur serveur interne',
    stack: process.env.NODE_ENV === 'development' ? err.stack : undefined
  });
});

// Démarrer le serveur
const PORT = config.server.port;

app.listen(PORT, () => {
  console.log(`Serveur MCP en cours d'exécution sur le port ${PORT}`);
  console.log(`URL Ollama configurée: ${config.ollama.baseUrl}`);
  console.log(`URL API Laravel configurée: ${config.laravel.apiUrl}`);
  console.log(`Environnement: ${config.server.env}`);
});

module.exports = app; // Pour les tests

const express = require('express');
const cors = require('cors');
const dotenv = require('dotenv');
const { setupRoutes } = require('./routes');
const { logger } = require('./utils/logger');
const { initDatabaseConnection } = require('./utils/database');

// Charger les variables d'environnement
dotenv.config();

// Initialiser l'application Express
const app = express();
const port = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Initialiser la connexion à la base de données
initDatabaseConnection()
  .then(() => {
    logger.info('Base de données connectée avec succès');
  })
  .catch(err => {
    logger.error('Erreur de connexion à la base de données:', err);
    process.exit(1);
  });

// Routes
setupRoutes(app);

// Gestion des erreurs
app.use((err, req, res, next) => {
  logger.error(`Erreur: ${err.message}`);
  res.status(err.statusCode || 500).json({
    error: true,
    message: err.message || 'Une erreur interne est survenue',
  });
});

// Démarrer le serveur
app.listen(port, () => {
  logger.info(`Serveur MCP démarré sur le port ${port}`);
});

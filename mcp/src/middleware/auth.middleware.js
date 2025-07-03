// Middleware d'authentification pour l'API
const config = require('../config');

/**
 * Middleware pour vérifier le token API
 * @param {Object} req - Requête Express
 * @param {Object} res - Réponse Express
 * @param {Function} next - Fonction suivante
 */
function authMiddleware(req, res, next) {
  const authHeader = req.headers.authorization;
  const apiToken = config.laravel.apiToken;

  // Si pas de token configuré, ignorer la vérification
  if (!apiToken) {
    console.warn('Aucun token API configuré, l\'authentification est désactivée');
    return next();
  }

  // Vérifier le header d'autorisation
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'Token API manquant ou invalide' });
  }

  // Extraire et vérifier le token
  const token = authHeader.split(' ')[1];

  if (token !== apiToken) {
    return res.status(401).json({ error: 'Token API invalide' });
  }

  next();
}

module.exports = authMiddleware;

// Middleware pour la validation des requêtes avec Zod
/**
 * Middleware de validation avec Zod pour le corps de la requête
 * @param {Object} schema - Schéma Zod à utiliser pour la validation
 * @returns {Function} - Middleware Express
 */
function validateRequest(schema) {
  return (req, res, next) => {
    try {
      const validation = schema.safeParse(req.body);

      if (!validation.success) {
        return res.status(400).json({
          success: false,
          error: 'Données invalides',
          details: validation.error.errors
        });
      }

      // Remplacer le corps de la requête par les données validées
      req.validatedData = validation.data;
      next();
    } catch (error) {
      console.error('Erreur de validation:', error);
      return res.status(400).json({
        success: false,
        error: 'Erreur de validation',
        message: error.message
      });
    }
  };
}

/**
 * Middleware de validation avec Zod pour les paramètres d'URL
 * @param {Object} schema - Schéma Zod à utiliser pour la validation
 * @returns {Function} - Middleware Express
 */
function validateParams(schema) {
  return (req, res, next) => {
    try {
      // Convertir les paramètres numériques si nécessaire
      const params = { ...req.params };
      if (params.slipId) {
        params.slipId = parseInt(params.slipId, 10);
      }
      if (params.recordId) {
        params.recordId = parseInt(params.recordId, 10);
      }

      const validation = schema.safeParse(params);

      if (!validation.success) {
        return res.status(400).json({
          success: false,
          error: 'Paramètres d\'URL invalides',
          details: validation.error.errors
        });
      }

      // Stocker les paramètres validés
      req.validatedParams = validation.data;
      next();
    } catch (error) {
      console.error('Erreur de validation des paramètres:', error);
      return res.status(400).json({
        success: false,
        error: 'Erreur de validation des paramètres',
        message: error.message
      });
    }
  };
}

/**
 * Middleware de validation avec Zod pour les paramètres de requête (query)
 * @param {Object} schema - Schéma Zod à utiliser pour la validation
 * @returns {Function} - Middleware Express
 */
function validateQuery(schema) {
  return (req, res, next) => {
    try {
      // Préparer les paramètres de requête
      const query = { ...req.query };

      // Conversion des valeurs booléennes
      if (query.includeValidation !== undefined) {
        query.includeValidation = query.includeValidation === 'true';
      }

      const validation = schema.safeParse(query);

      if (!validation.success) {
        return res.status(400).json({
          success: false,
          error: 'Paramètres de requête invalides',
          details: validation.error.errors
        });
      }

      // Stocker les paramètres de requête validés
      req.validatedQuery = validation.data;
      next();
    } catch (error) {
      console.error('Erreur de validation des paramètres de requête:', error);
      return res.status(400).json({
        success: false,
        error: 'Erreur de validation des paramètres de requête',
        message: error.message
      });
    }
  };
}

module.exports = {
  validateRequest,
  validateParams,
  validateQuery
};

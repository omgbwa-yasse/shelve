// Middleware pour la validation des requêtes avec Zod
/**
 * Middleware de validation avec Zod
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

module.exports = validateRequest;

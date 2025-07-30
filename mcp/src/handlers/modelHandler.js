const ollamaService = require('../services/ollamaService');
const { logger } = require('../utils/logger');

/**
 * Récupère la liste des modèles disponibles
 */
module.exports.getAvailableModels = async (req, res) => {
  try {
    // Initialiser le service Ollama si ce n'est pas déjà fait
    if (!ollamaService.defaultModel) {
      await ollamaService.initialize();
    }

    const models = await ollamaService.listModels();

    return res.json({
      success: true,
      models
    });
  } catch (error) {
    logger.error(`Erreur lors de la récupération des modèles: ${error.message}`);
    return res.status(500).json({ error: true, message: error.message });
  }
};

/**
 * Récupère le modèle par défaut
 */
module.exports.getDefaultModel = async (req, res) => {
  try {
    // Initialiser le service Ollama si ce n'est pas déjà fait
    if (!ollamaService.defaultModel) {
      await ollamaService.initialize();
    }

    const defaultModel = ollamaService.getDefaultModel();

    return res.json({
      success: true,
      model: defaultModel
    });
  } catch (error) {
    logger.error(`Erreur lors de la récupération du modèle par défaut: ${error.message}`);
    return res.status(500).json({ error: true, message: error.message });
  }
};

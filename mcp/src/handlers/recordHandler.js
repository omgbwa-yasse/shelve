const ollamaService = require('../services/ollamaService');
const recordService = require('../services/recordService');
const { logger } = require('../utils/logger');
const { getSettings } = require('../utils/database');
const { buildPrompt } = require('../utils/templateLoader');

/**
 * Initialise les services
 */
async function initialize() {
  await ollamaService.initialize();
  await recordService.initialize();

  logger.info('RecordHandler initialisé avec succès');
}

// Initialiser au démarrage
initialize();

/**
 * Génère un résumé pour un record
 */
module.exports.summarize = async (req, res) => {
  try {
    const { recordId, content } = req.body;

    // Si un ID de record est fourni, utiliser le service de record
    if (recordId) {
      const result = await recordService.generateSummary(recordId);
      return res.json({
        success: true,
        recordId: result.recordId,
        name: result.recordName,
        summary: result.summary
      });
    }

    // Sinon, traiter directement le contenu fourni
    if (!content) {
      return res.status(400).json({
        error: true,
        message: 'Le contenu ou un recordId est requis'
      });
    }

    // Utiliser le template général pour le contenu brut
    const prompt = buildPrompt('summarize', { content }, 'prompts');
    const summary = await ollamaService.generateCompletion(prompt);

    return res.json({
      success: true,
      summary
    });
  } catch (error) {
    logger.error(`Erreur lors de la génération du résumé: ${error.message}`);
    return res.status(500).json({ error: true, message: error.message });
  }
};

/**
 * Reformate le titre d'un record
 */
module.exports.reformatTitle = async (req, res) => {
  try {
    const { recordId, title } = req.body;

    // Si un ID de record est fourni, utiliser le service de record
    if (recordId) {
      const result = await recordService.reformatTitle(recordId);
      return res.json({
        success: true,
        recordId: result.recordId,
        originalTitle: result.originalTitle,
        title: result.newTitle
      });
    }

    // Sinon, traiter directement le titre fourni
    if (!title) {
      return res.status(400).json({
        error: true,
        message: 'Le titre ou un recordId est requis'
      });
    }

    // Utiliser le template général pour le titre brut
    const prompt = buildPrompt('title', { title }, 'prompts');
    const formattedTitle = await ollamaService.generateCompletion(prompt);

    return res.json({
      success: true,
      title: formattedTitle
    });
  } catch (error) {
    logger.error(`Erreur lors de la reformulation du titre: ${error.message}`);
    return res.status(500).json({ error: true, message: error.message });
  }
};

/**
 * Extrait les mots-clés d'un record
 */
module.exports.extractKeywords = async (req, res) => {
  try {
    const { recordId, content } = req.body;

    // Si un ID de record est fourni, utiliser le service de record
    if (recordId) {
      const result = await recordService.extractKeywords(recordId);
      return res.json({
        success: true,
        recordId: result.recordId,
        name: result.recordName,
        keywords: result.keywords
      });
    }

    // Sinon, traiter directement le contenu fourni
    if (!content) {
      return res.status(400).json({
        error: true,
        message: 'Le contenu ou un recordId est requis'
      });
    }

    // Utiliser le template général pour le contenu brut
    const prompt = buildPrompt('keywords', { content }, 'prompts');
    const keywordsText = await ollamaService.generateCompletion(prompt);

    // Transformer la chaîne de mots-clés en tableau
    const keywords = keywordsText
      .split(',')
      .map(keyword => keyword.trim())
      .filter(keyword => keyword.length > 0);

    return res.json({
      success: true,
      keywords
    });
  } catch (error) {
    logger.error(`Erreur lors de l'extraction des mots-clés: ${error.message}`);
    return res.status(500).json({ error: true, message: error.message });
  }
};

/**
 * Analyse le contenu d'un record
 */
module.exports.analyzeContent = async (req, res) => {
  try {
    const { recordId, content } = req.body;

    // Si un ID de record est fourni, utiliser le service de record
    if (recordId) {
      const result = await recordService.analyzeContent(recordId);
      return res.json({
        success: true,
        recordId: result.recordId,
        name: result.recordName,
        analysis: result.analysis
      });
    }

    // Sinon, traiter directement le contenu fourni
    if (!content) {
      return res.status(400).json({
        error: true,
        message: 'Le contenu ou un recordId est requis'
      });
    }

    // Utiliser le template général pour le contenu brut
    const prompt = buildPrompt('analyze', { content }, 'prompts');
    const analysis = await ollamaService.generateCompletion(prompt);

    return res.json({
      success: true,
      analysis
    });
  } catch (error) {
    logger.error(`Erreur lors de l'analyse du contenu: ${error.message}`);
    return res.status(500).json({ error: true, message: error.message });
  }
};

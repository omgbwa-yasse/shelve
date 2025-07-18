// Contrôleur pour les fonctionnalités d'enrichissement des records
const recordsService = require('../services/records.service');
const aiService = require('../services/ai.service');

/**
 * Contrôleur pour les opérations d'enrichissement de records
 */
class EnrichmentController {
  /**
   * Formater le titre d'un record selon le format: objet, action administrative : typologie documentaire (date?)
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async formatRecordTitle(req, res, next) {
    try {
      const { recordId, title, context, modelName } = req.body;

      const formattedTitle = await recordsService.formatRecordTitle(
        recordId,
        title,
        context,
        modelName
      );

      res.json({
        success: true,
        recordId,
        formattedTitle
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Générer un résumé pour un record
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async generateSummary(req, res, next) {
    try {
      const { recordId, recordData, maxLength, modelName } = req.body;

      const summary = await recordsService.generateSummary(
        recordId,
        recordData,
        maxLength,
        modelName
      );

      res.json({
        success: true,
        recordId,
        summary
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Extraire des mots-clés catégorisés d'un record
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async extractCategorizedKeywords(req, res, next) {
    try {
      const { recordId, recordData, modelName, autoAssign } = req.body;

      const result = await recordsService.extractCategorizedKeywords(
        recordId,
        recordData,
        modelName,
        autoAssign
      );

      res.json({
        success: true,
        recordId,
        keywords: result.keywords,
        thesaurusMatches: result.thesaurusMatches
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Rechercher des termes dans le thésaurus
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async searchThesaurus(req, res, next) {
    try {
      const { recordId, content, modelName, maxTerms } = req.body;

      const matches = await recordsService.searchThesaurus(
        recordId,
        content,
        modelName,
        maxTerms
      );

      res.json({
        success: true,
        recordId,
        matches
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Assigner des termes à un record
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async assignTerms(req, res, next) {
    try {
      const { recordId, terms } = req.body;

      const result = await recordsService.assignTerms(recordId, terms);

      res.json({
        success: true,
        recordId,
        assigned: result.assigned
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Vérifier la disponibilité d'Ollama
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async checkOllama(req, res, next) {
    try {
      const status = await aiService.checkStatus();

      res.json({
        success: true,
        available: status.available,
        models: status.models || []
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Vérifier l'état de santé de l'API
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   */
  healthCheck(req, res) {
    res.json({
      status: 'ok',
      timestamp: new Date().toISOString(),
      service: 'MCP API',
      version: '1.0.0'
    });
  }
}

module.exports = new EnrichmentController();

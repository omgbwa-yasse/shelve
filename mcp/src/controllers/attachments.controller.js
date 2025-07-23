// Contrôleur pour les fonctionnalités d'analyse des documents numériques (attachments)
const attachmentService = require('../services/attachment.service');
const aiService = require('../services/ai.service');

/**
 * Contrôleur pour les opérations d'analyse des attachments
 */
class AttachmentsController {
  /**
   * Analyser plusieurs documents numériques et proposer une description et indexation
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async analyzeDocuments(req, res, next) {
    try {
      const { attachmentIds, analysisOptions, modelName } = req.body;

      console.log(`Analyse de ${attachmentIds.length} documents demandée`);

      // Récupérer et analyser les documents
      const analysis = await attachmentService.analyzeMultipleDocuments(
        attachmentIds,
        analysisOptions,
        modelName
      );

      res.json({
        success: true,
        analysis: {
          suggestedRecord: analysis.suggestedRecord,
          thesaurusTerms: analysis.thesaurusTerms,
          documentSummary: analysis.documentSummary,
          confidence: analysis.confidence,
          processedDocuments: analysis.processedDocuments
        }
      });
    } catch (error) {
      console.error('Erreur lors de l\'analyse des documents:', error);
      next(error);
    }
  }

  /**
   * Analyser un seul document et extraire du contenu
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async analyzeSingleDocument(req, res, next) {
    try {
      const { attachmentId, extractionOptions, modelName } = req.body;

      console.log(`Analyse du document ${attachmentId} demandée`);

      const analysis = await attachmentService.analyzeSingleDocument(
        attachmentId,
        extractionOptions,
        modelName
      );

      res.json({
        success: true,
        documentId: attachmentId,
        analysis: {
          extractedText: analysis.extractedText,
          summary: analysis.summary,
          keywords: analysis.keywords,
          suggestedTerms: analysis.suggestedTerms,
          metadata: analysis.metadata
        }
      });
    } catch (error) {
      console.error('Erreur lors de l\'analyse du document:', error);
      next(error);
    }
  }

  /**
   * Obtenir les métadonnées d'un ou plusieurs documents
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async getDocumentsMetadata(req, res, next) {
    try {
      const { attachmentIds } = req.body;

      console.log(`Récupération des métadonnées pour ${attachmentIds.length} documents`);

      const metadata = await attachmentService.getDocumentsMetadata(attachmentIds);

      res.json({
        success: true,
        documents: metadata
      });
    } catch (error) {
      console.error('Erreur lors de la récupération des métadonnées:', error);
      next(error);
    }
  }

  /**
   * Proposer une description de record basée sur l'analyse de documents
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async suggestRecordDescription(req, res, next) {
    try {
      const { attachmentIds, contextualInfo, recordTemplate, modelName } = req.body;

      console.log(`Génération de description de record pour ${attachmentIds.length} documents`);

      const suggestion = await attachmentService.generateRecordDescription(
        attachmentIds,
        contextualInfo,
        recordTemplate,
        modelName
      );

      res.json({
        success: true,
        suggestion: {
          title: suggestion.title,
          description: suggestion.description,
          dates: suggestion.dates,
          scope: suggestion.scope,
          arrangement: suggestion.arrangement,
          accessConditions: suggestion.accessConditions,
          relatedMaterials: suggestion.relatedMaterials,
          notes: suggestion.notes,
          suggestedLevel: suggestion.suggestedLevel,
          confidence: suggestion.confidence
        }
      });
    } catch (error) {
      console.error('Erreur lors de la génération de description:', error);
      next(error);
    }
  }

  /**
   * Proposer une indexation thésaurus basée sur l'analyse de documents
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async suggestThesaurusIndexing(req, res, next) {
    try {
      const { attachmentIds, thesaurusScope, maxTerms, modelName } = req.body;

      console.log(`Génération d'indexation thésaurus pour ${attachmentIds.length} documents`);

      const indexing = await attachmentService.generateThesaurusIndexing(
        attachmentIds,
        thesaurusScope,
        maxTerms,
        modelName
      );

      res.json({
        success: true,
        indexing: {
          suggestedTerms: indexing.suggestedTerms,
          conceptualAnalysis: indexing.conceptualAnalysis,
          weights: indexing.weights,
          confidence: indexing.confidence,
          unmatchedConcepts: indexing.unmatchedConcepts
        }
      });
    } catch (error) {
      console.error('Erreur lors de la génération d\'indexation:', error);
      next(error);
    }
  }

  /**
   * Analyser un lot de documents et générer un record complet avec indexation
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   * @param {Function} next - Fonction next d'Express
   */
  async generateCompleteRecord(req, res, next) {
    try {
      const { attachmentIds, recordOptions, indexingOptions, modelName } = req.body;

      console.log(`Génération complète de record et indexation pour ${attachmentIds.length} documents`);

      const result = await attachmentService.generateCompleteRecordWithIndexing(
        attachmentIds,
        recordOptions,
        indexingOptions,
        modelName
      );

      res.json({
        success: true,
        result: {
          record: result.record,
          indexing: result.indexing,
          documentAnalysis: result.documentAnalysis,
          processingStats: result.processingStats,
          recommendations: result.recommendations
        }
      });
    } catch (error) {
      console.error('Erreur lors de la génération complète:', error);
      next(error);
    }
  }

  /**
   * Vérifier la disponibilité du service d'analyse de documents
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   */
  healthCheck(req, res) {
    res.json({
      status: 'ok',
      timestamp: new Date().toISOString(),
      service: 'Attachments Analysis API',
      version: '1.0.0'
    });
  }
}

module.exports = new AttachmentsController();

// Contrôleur pour l'enrichissement des records
const enrichmentService = require('../services/enrichment.service');
const ollamaService = require('../services/ollama.service');

class EnrichmentController {
  /**
   * Traitement d'enrichissement d'un record
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   */
  async enrichRecord(req, res) {
    try {
      const { recordId, recordData, modelName, mode } = req.validatedData;

      console.log(`Traitement demandé pour l'enregistrement #${recordId} avec le modèle ${modelName} en mode ${mode}`);

      // Traitement spécifique pour le formatage du titre
      if (mode === 'format_title') {
        const result = await enrichmentService.formatTitle(recordData.name, modelName);
        return res.json({
          success: result.success,
          recordId,
          originalTitle: result.originalTitle,
          formattedTitle: result.formattedTitle,
          mode,
          model: modelName
        });
      }

      // Traitement spécifique pour l'extraction de mots-clés et recherche thésaurus
      if (mode === 'extract_keywords') {
        // Concaténer toutes les informations disponibles pour une meilleure extraction
        const contentToAnalyze = [
          recordData.name,
          recordData.content,
          recordData.biographical_history,
          recordData.archival_history,
          recordData.note
        ].filter(Boolean).join("\n\n");

        const result = await enrichmentService.searchThesaurusTerms(contentToAnalyze, modelName);
        return res.json({
          success: result.success,
          recordId,
          extractedKeywords: result.extractedKeywords,
          matchedTerms: result.matchedTerms,
          mode,
          model: modelName
        });
      }

      // Traitement spécifique pour l'extraction de mots-clés catégorisés
      if (mode === 'categorized_keywords') {
        // Concaténer toutes les informations disponibles pour une meilleure extraction
        const contentToAnalyze = [
          recordData.name,
          recordData.content,
          recordData.biographical_history,
          recordData.archival_history,
          recordData.note
        ].filter(Boolean).join("\n\n");

        const result = await enrichmentService.extractCategorizedKeywords(contentToAnalyze, modelName);
        return res.json({
          success: result.success,
          recordId,
          extractedKeywords: result.extractedKeywords,
          matchedTerms: result.matchedTerms,
          allExtractedKeywords: result.allExtractedKeywords,
          mode,
          model: modelName
        });
      }

      // Autres modes d'enrichissement (enrich, summarize, analyze)
      const result = await enrichmentService.processRecord(recordData, modelName, mode);

      return res.json({
        success: result.success,
        recordId,
        enrichedContent: result.enrichedContent,
        mode: result.mode,
        model: result.model,
        stats: result.stats
      });
    } catch (error) {
      console.error('Erreur lors du traitement:', error);
      res.status(500).json({
        success: false,
        error: error.message || 'Erreur serveur interne',
        details: error.response?.data || null
      });
    }
  }

  /**
   * Recherche de termes dans le thésaurus
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   */
  async searchThesaurus(req, res) {
    try {
      const { content, modelName, maxTerms } = req.validatedData;

      const result = await enrichmentService.searchThesaurusTerms(content, modelName, maxTerms);

      return res.json({
        success: result.success,
        extractedKeywords: result.extractedKeywords,
        matchedTerms: result.matchedTerms,
        model: modelName
      });
    } catch (error) {
      console.error('Erreur lors de la recherche dans le thésaurus:', error);
      return res.status(500).json({
        success: false,
        error: `Erreur serveur: ${error.message}`
      });
    }
  }

  /**
   * Extraction de mots-clés catégorisés
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   */
  async extractCategorizedKeywords(req, res) {
    try {
      const { recordId, recordData, modelName, autoAssign } = req.validatedData;

      // Concaténer toutes les informations disponibles pour une meilleure extraction
      const contentToAnalyze = [
        recordData.name,
        recordData.content,
        recordData.biographical_history,
        recordData.archival_history,
        recordData.note
      ].filter(Boolean).join("\n\n");

      // Extraire les mots-clés catégorisés
      const extractionResult = await enrichmentService.extractCategorizedKeywords(contentToAnalyze, modelName);

      let assignmentResult = { message: "Aucune assignation demandée" };
      let thesaurusResults = {};

      // Si l'assignation automatique est demandée
      if (autoAssign && extractionResult.success) {
        // Assigner les termes trouvés au record
        assignmentResult = await enrichmentService.assignTermsToRecord(
          recordId,
          extractionResult.matchedTerms
        );
        thesaurusResults = extractionResult.matchedTerms;
      }

      return res.json({
        success: extractionResult.success,
        record_id: recordId,
        model: modelName,
        extraction: {
          keywords: extractionResult.extractedKeywords,
          matchedTerms: extractionResult.matchedTerms
        },
        assignment: autoAssign ? assignmentResult : undefined
      });
    } catch (error) {
      console.error('Erreur lors de l\'extraction et assignation de mots-clés:', error);
      return res.status(500).json({
        success: false,
        error: `Erreur serveur: ${error.message}`
      });
    }
  }

  /**
   * Assignation de termes à un record
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   */
  async assignTerms(req, res) {
    try {
      const { recordId, terms } = req.validatedData;

      // Organiser les termes par catégorie
      const categorizedTerms = {
        geographic: terms.filter(t => t.type === 'geographic'),
        thematic: terms.filter(t => t.type === 'thematic'),
        typologic: terms.filter(t => t.type === 'typology' || t.type === 'typologic')
      };

      // Assigner les termes au record
      const assignmentResult = await enrichmentService.assignTermsToRecord(recordId, categorizedTerms);

      return res.json({
        success: assignmentResult.success,
        record_id: recordId,
        assignment: assignmentResult
      });
    } catch (error) {
      console.error('Erreur lors de l\'assignation des termes:', error);
      return res.status(500).json({
        success: false,
        error: `Erreur serveur: ${error.message}`
      });
    }
  }

  /**
   * Vérification de la santé du service
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   */
  healthCheck(req, res) {
    res.json({ status: 'ok', timestamp: new Date().toISOString() });
  }

  /**
   * Vérification de la disponibilité d'Ollama
   * @param {Object} req - Requête Express
   * @param {Object} res - Réponse Express
   */
  async checkOllama(req, res) {
    try {
      const status = await ollamaService.checkStatus();
      res.json(status);
    } catch (error) {
      res.status(500).json({
        status: 'error',
        message: `Impossible de se connecter à Ollama: ${error.message}`,
        details: error.response?.data || null
      });
    }
  }
}

module.exports = new EnrichmentController();

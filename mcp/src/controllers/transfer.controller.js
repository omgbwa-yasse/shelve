// Contrôleur pour les fonctionnalités liées au transfert d'archives
const enrichmentService = require('../services/enrichment.service');
const laravelApiService = require('../services/laravel-api.service');

/**
 * Contrôleur pour les opérations d'enrichissement liées au transfert d'archives
 */
class TransferController {
  /**
   * Enrichit les métadonnées d'un bordereau de transfert
   */
  async enhanceSlip(req, res) {
    try {
      const { slipId } = req.validatedParams || req.params;
      const { modelName = 'llama3' } = req.validatedQuery || req.query;
      const model = modelName;

      // Récupérer les données du bordereau depuis l'API Laravel
      const slipData = await laravelApiService.getSlipDetails(slipId);

      if (!slipData.success) {
        return res.status(404).json({
          success: false,
          error: 'Bordereau non trouvé ou inaccessible'
        });
      }

      // Récupérer les enregistrements associés au bordereau
      const recordsData = await laravelApiService.getSlipRecords(slipId);

      if (!recordsData.success) {
        return res.status(500).json({
          success: false,
          error: 'Impossible de récupérer les enregistrements du bordereau'
        });
      }

      // Enrichir les métadonnées du bordereau
      const result = await enrichmentService.enhanceTransferSlip(
        slipData.slip,
        recordsData.records,
        model
      );

      return res.json(result);
    } catch (error) {
      console.error('Erreur lors de l\'enrichissement du bordereau:', error);
      return res.status(500).json({
        success: false,
        error: error.message || 'Une erreur est survenue lors de l\'enrichissement du bordereau'
      });
    }
  }

  /**
   * Valide la conformité d'un ensemble de documents pour le transfert
   */
  async validateRecords(req, res) {
    try {
      const { slipId } = req.validatedParams || req.params;
      const { modelName = 'llama3' } = req.validatedQuery || req.query;
      const model = modelName;

      // Récupérer les enregistrements associés au bordereau
      const recordsData = await laravelApiService.getSlipRecords(slipId);

      if (!recordsData.success) {
        return res.status(500).json({
          success: false,
          error: 'Impossible de récupérer les enregistrements du bordereau'
        });
      }

      // Valider les enregistrements
      const result = await enrichmentService.validateTransferRecords(
        recordsData.records,
        model
      );

      return res.json(result);
    } catch (error) {
      console.error('Erreur lors de la validation des documents:', error);
      return res.status(500).json({
        success: false,
        error: error.message || 'Une erreur est survenue lors de la validation des documents'
      });
    }
  }

  /**
   * Suggère un plan de classement pour les records d'un bordereau
   */
  async suggestClassification(req, res) {
    try {
      const { slipId } = req.validatedParams || req.params;
      const { modelName = 'llama3' } = req.validatedQuery || req.query;
      const model = modelName;

      // Récupérer les enregistrements associés au bordereau
      const recordsData = await laravelApiService.getSlipRecords(slipId);

      if (!recordsData.success) {
        return res.status(500).json({
          success: false,
          error: 'Impossible de récupérer les enregistrements du bordereau'
        });
      }

      // Générer le plan de classement
      const result = await enrichmentService.suggestClassificationScheme(
        recordsData.records,
        model
      );

      return res.json(result);
    } catch (error) {
      console.error('Erreur lors de la génération du plan de classement:', error);
      return res.status(500).json({
        success: false,
        error: error.message || 'Une erreur est survenue lors de la génération du plan de classement'
      });
    }
  }

  /**
   * Génère un rapport détaillé sur un transfert d'archives
   */
  async generateReport(req, res) {
    try {
      const { slipId } = req.validatedParams || req.params;
      const { modelName = 'llama3', includeValidation = true } = req.validatedQuery || req.query;
      const model = modelName;
      const shouldIncludeValidation = includeValidation === true || includeValidation === 'true';

      // Récupérer les données du bordereau
      const slipData = await laravelApiService.getSlipDetails(slipId);

      if (!slipData.success) {
        return res.status(404).json({
          success: false,
          error: 'Bordereau non trouvé ou inaccessible'
        });
      }

      // Récupérer les enregistrements associés au bordereau
      const recordsData = await laravelApiService.getSlipRecords(slipId);

      if (!recordsData.success) {
        return res.status(500).json({
          success: false,
          error: 'Impossible de récupérer les enregistrements du bordereau'
        });
      }

      // Éventuellement effectuer une validation des documents
      let validationResults = null;
      if (shouldIncludeValidation) {
        validationResults = await enrichmentService.validateTransferRecords(
          recordsData.records,
          model
        );
      }

      // Générer le rapport
      const result = await enrichmentService.generateTransferReport(
        slipData.slip,
        recordsData.records,
        validationResults,
        model
      );

      return res.json(result);
    } catch (error) {
      console.error('Erreur lors de la génération du rapport:', error);
      return res.status(500).json({
        success: false,
        error: error.message || 'Une erreur est survenue lors de la génération du rapport'
      });
    }
  }
}

module.exports = new TransferController();

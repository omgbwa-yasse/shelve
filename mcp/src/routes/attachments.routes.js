// Routes API pour l'analyse des documents numériques (attachments)
const express = require('express');
const router = express.Router();
const attachmentsController = require('../controllers/attachments.controller');
const { validateRequest } = require('../middleware/validation.middleware');
const schemas = require('../schemas/attachment.schemas');

// Route pour analyser plusieurs documents et proposer description + indexation
router.post(
  '/analyze-documents',
  validateRequest(schemas.AnalyzeDocumentsSchema),
  attachmentsController.analyzeDocuments
);

// Route pour analyser un seul document
router.post(
  '/analyze-single',
  validateRequest(schemas.AnalyzeSingleDocumentSchema),
  attachmentsController.analyzeSingleDocument
);

// Route pour récupérer les métadonnées de documents
router.post(
  '/metadata',
  validateRequest(schemas.GetDocumentsMetadataSchema),
  attachmentsController.getDocumentsMetadata
);

// Route pour proposer une description de record
router.post(
  '/suggest-record',
  validateRequest(schemas.SuggestRecordDescriptionSchema),
  attachmentsController.suggestRecordDescription
);

// Route pour proposer une indexation thésaurus
router.post(
  '/suggest-indexing',
  validateRequest(schemas.SuggestThesaurusIndexingSchema),
  attachmentsController.suggestThesaurusIndexing
);

// Route pour générer un record complet avec indexation
router.post(
  '/generate-complete',
  validateRequest(schemas.GenerateCompleteRecordSchema),
  attachmentsController.generateCompleteRecord
);

// Route de santé pour le service d'attachments
router.get('/health', attachmentsController.healthCheck);

module.exports = router;

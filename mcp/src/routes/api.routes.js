// Routes API pour la gestion des records et des attachments
const express = require('express');
const router = express.Router();
const { z } = require('zod');
const recordsController = require('../controllers/records.controller');
const enrichmentController = require('../controllers/enrichment.controller');
const attachmentsRoutes = require('./attachments.routes');
const { validateRequest } = require('../middleware/validation.middleware');
const authMiddleware = require('../middleware/auth.middleware');
const schemas = require('../schemas/validation');

// Routes pour l'enrichissement multi-provider
router.post(
  '/enrich/:id/title',
  enrichmentController.formatTitle
);

router.post(
  '/enrich/:id/summary', 
  enrichmentController.generateSummary
);

router.post(
  '/enrich/:id/keywords',
  enrichmentController.extractKeywords
);

router.post(
  '/enrich/:id/categorized-keywords',
  enrichmentController.extractCategorizedKeywords
);

router.post(
  '/enrich/:id/complete',
  enrichmentController.enrichRecord
);

router.get(
  '/providers/status',
  enrichmentController.checkStatus
);

router.post(
  '/providers/clear-cache',
  enrichmentController.clearCache
);

// Routes legacy (pour compatibilité)
router.post(
  '/format-title',
  validateRequest(schemas.FormatTitleSchema),
  recordsController.formatRecordTitle
);

router.post(
  '/summarize',
  validateRequest(schemas.SummarizeRequestSchema),
  recordsController.generateSummary
);

router.post(
  '/categorized-keywords',
  validateRequest(schemas.CategorizedKeywordsSchema),
  recordsController.extractCategorizedKeywords
);

// Route pour rechercher des mots-clés dans le thésaurus
router.post(
  '/thesaurus/search',
  validateRequest(schemas.ThesaurusSearchSchema),
  recordsController.searchThesaurus
);

// Route pour assigner des termes à un record
router.post(
  '/assign-terms',
  validateRequest(schemas.AssignTermsSchema),
  recordsController.assignTerms
);

// Route pour vérifier la disponibilité d'Ollama
router.get('/check-ollama', recordsController.checkOllama);

// Routes pour l'analyse des attachments
router.use('/attachments', attachmentsRoutes);

module.exports = router;

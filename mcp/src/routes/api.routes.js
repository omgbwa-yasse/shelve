// Routes API pour l'enrichissement des records
const express = require('express');
const router = express.Router();
const { z } = require('zod');
const enrichmentController = require('../controllers/enrichment.controller');
const transferController = require('../controllers/transfer.controller');
const { validateRequest, validateParams, validateQuery } = require('../middleware/validation.middleware');
const authMiddleware = require('../middleware/auth.middleware');
const schemas = require('../schemas/validation');

// Route pour enrichir une description
router.post(
  '/enrich',
  validateRequest(schemas.EnrichRequestSchema),
  enrichmentController.enrichRecord
);

// Route pour rechercher des mots-clés dans le thésaurus
router.post(
  '/thesaurus/search',
  validateRequest(schemas.ThesaurusSearchSchema),
  enrichmentController.searchThesaurus
);

// Route pour extraire des mots-clés catégorisés
router.post(
  '/categorized-keywords',
  validateRequest(schemas.CategorizedKeywordsSchema),
  enrichmentController.extractCategorizedKeywords
);

// Route pour assigner des termes à un record
router.post(
  '/assign-terms',
  validateRequest(schemas.AssignTermsSchema),
  enrichmentController.assignTerms
);

// Route pour vérifier la disponibilité d'Ollama
router.get('/check-ollama', enrichmentController.checkOllama);

// Routes pour le transfert d'archives
router.get(
  '/transfer/slips/:slipId/enhance',
  authMiddleware,
  validateParams(schemas.TransferSlipSchema),
  validateQuery(z.object({ modelName: z.string().optional() })),
  transferController.enhanceSlip
);
router.get(
  '/transfer/slips/:slipId/validate',
  authMiddleware,
  validateParams(schemas.ValidateRecordsSchema),
  validateQuery(z.object({ modelName: z.string().optional() })),
  transferController.validateRecords
);
router.get(
  '/transfer/slips/:slipId/classify',
  authMiddleware,
  validateParams(schemas.ClassificationSchema),
  validateQuery(z.object({ modelName: z.string().optional() })),
  transferController.suggestClassification
);
router.get(
  '/transfer/slips/:slipId/report',
  authMiddleware,
  validateParams(schemas.TransferSlipSchema),
  validateQuery(z.object({ 
    modelName: z.string().optional(),
    includeValidation: z.enum(['true', 'false']).optional()
  })),
  transferController.generateReport
);

module.exports = router;

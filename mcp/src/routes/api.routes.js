// Routes API pour l'enrichissement des records
const express = require('express');
const router = express.Router();
const { z } = require('zod');
const enrichmentController = require('../controllers/enrichment.controller');
const { validateRequest } = require('../middleware/validation.middleware');
const authMiddleware = require('../middleware/auth.middleware');
const schemas = require('../schemas/validation');

// Route pour formater le titre d'un record
router.post(
  '/format-title',
  validateRequest(schemas.FormatTitleSchema),
  enrichmentController.formatRecordTitle
);

// Route pour générer un résumé d'un record
router.post(
  '/summarize',
  validateRequest(schemas.SummarizeRequestSchema),
  enrichmentController.generateSummary
);

// Route pour extraire des mots-clés catégorisés
router.post(
  '/categorized-keywords',
  validateRequest(schemas.CategorizedKeywordsSchema),
  enrichmentController.extractCategorizedKeywords
);

// Route pour rechercher des mots-clés dans le thésaurus
router.post(
  '/thesaurus/search',
  validateRequest(schemas.ThesaurusSearchSchema),
  enrichmentController.searchThesaurus
);

// Route pour assigner des termes à un record
router.post(
  '/assign-terms',
  validateRequest(schemas.AssignTermsSchema),
  enrichmentController.assignTerms
);

// Route pour vérifier la disponibilité d'Ollama
router.get('/check-ollama', enrichmentController.checkOllama);

module.exports = router;

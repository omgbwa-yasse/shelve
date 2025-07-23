// Routes API pour la gestion des records et des attachments
const express = require('express');
const router = express.Router();
const { z } = require('zod');
const recordsController = require('../controllers/records.controller');
const attachmentsRoutes = require('./attachments.routes');
const { validateRequest } = require('../middleware/validation.middleware');
const authMiddleware = require('../middleware/auth.middleware');
const schemas = require('../schemas/validation');

// Route pour formater le titre d'un record
router.post(
  '/format-title',
  validateRequest(schemas.FormatTitleSchema),
  recordsController.formatRecordTitle
);

// Route pour générer un résumé d'un record
router.post(
  '/summarize',
  validateRequest(schemas.SummarizeRequestSchema),
  recordsController.generateSummary
);

// Route pour extraire des mots-clés catégorisés
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

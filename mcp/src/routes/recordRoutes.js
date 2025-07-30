const express = require('express');
const recordHandler = require('../handlers/recordHandler');
const { logger } = require('../utils/logger');

// Créer un router Express
const router = express.Router();

/**
 * @route POST /api/records/:id/reformat-title
 * @desc Reformule le titre d'un record
 * @access Public
 */
router.post('/:id/reformat-title', async (req, res) => {
  try {
    // Extraire l'ID du record des paramètres de la route
    const recordId = parseInt(req.params.id, 10);

    if (isNaN(recordId)) {
      return res.status(400).json({
        error: true,
        message: 'ID de record invalide'
      });
    }

    // Passer la requête au handler en ajoutant l'ID du record
    req.body.recordId = recordId;
    return await recordHandler.reformatTitle(req, res);
  } catch (error) {
    logger.error(`Erreur lors du traitement de la route /records/${req.params.id}/reformat-title: ${error.message}`);
    return res.status(500).json({
      error: true,
      message: `Une erreur est survenue lors de la reformulation du titre: ${error.message}`
    });
  }
});

/**
 * @route POST /api/records/:id/summarize
 * @desc Génère un résumé pour un record
 * @access Public
 */
router.post('/:id/summarize', async (req, res) => {
  try {
    // Extraire l'ID du record des paramètres de la route
    const recordId = parseInt(req.params.id, 10);

    if (isNaN(recordId)) {
      return res.status(400).json({
        error: true,
        message: 'ID de record invalide'
      });
    }

    // Passer la requête au handler en ajoutant l'ID du record
    req.body.recordId = recordId;
    return await recordHandler.summarize(req, res);
  } catch (error) {
    logger.error(`Erreur lors du traitement de la route /records/${req.params.id}/summarize: ${error.message}`);
    return res.status(500).json({
      error: true,
      message: `Une erreur est survenue lors de la génération du résumé: ${error.message}`
    });
  }
});

/**
 * @route POST /api/records/:id/extract-keywords
 * @desc Extrait les mots-clés d'un record
 * @access Public
 */
router.post('/:id/extract-keywords', async (req, res) => {
  try {
    // Extraire l'ID du record des paramètres de la route
    const recordId = parseInt(req.params.id, 10);

    if (isNaN(recordId)) {
      return res.status(400).json({
        error: true,
        message: 'ID de record invalide'
      });
    }

    // Passer la requête au handler en ajoutant l'ID du record
    req.body.recordId = recordId;
    return await recordHandler.extractKeywords(req, res);
  } catch (error) {
    logger.error(`Erreur lors du traitement de la route /records/${req.params.id}/extract-keywords: ${error.message}`);
    return res.status(500).json({
      error: true,
      message: `Une erreur est survenue lors de l'extraction des mots-clés: ${error.message}`
    });
  }
});

/**
 * @route POST /api/records/:id/analyze
 * @desc Analyse le contenu d'un record
 * @access Public
 */
router.post('/:id/analyze', async (req, res) => {
  try {
    // Extraire l'ID du record des paramètres de la route
    const recordId = parseInt(req.params.id, 10);

    if (isNaN(recordId)) {
      return res.status(400).json({
        error: true,
        message: 'ID de record invalide'
      });
    }

    // Passer la requête au handler en ajoutant l'ID du record
    req.body.recordId = recordId;
    return await recordHandler.analyzeContent(req, res);
  } catch (error) {
    logger.error(`Erreur lors du traitement de la route /records/${req.params.id}/analyze: ${error.message}`);
    return res.status(500).json({
      error: true,
      message: `Une erreur est survenue lors de l'analyse du contenu: ${error.message}`
    });
  }
});

// Exporter le router
module.exports = router;

const express = require('express');
const ollamaService = require('../../services/ai/ollamaService');
const { asyncHandler } = require('../../middleware/errorHandler');
const logger = require('../../utils/logger');

const router = express.Router();

/**
 * @route GET /api/models
 * @desc Obtenir la liste des modèles disponibles
 */
router.get('/', asyncHandler(async (req, res) => {
    const models = await ollamaService.listModels();

    res.json({
        success: true,
        data: {
            models,
            count: models.length,
            default: ollamaService.defaultModel,
            taskModels: {
                summary: ollamaService.getModelForTask('summary'),
                keywords: ollamaService.getModelForTask('keywords'),
                title: ollamaService.getModelForTask('title'),
                analysis: ollamaService.getModelForTask('analysis')
            }
        }
    });
}));

/**
 * @route GET /api/models/:modelName
 * @desc Obtenir les détails d'un modèle spécifique
 */
router.get('/:modelName', asyncHandler(async (req, res) => {
    const { modelName } = req.params;
    const models = await ollamaService.listModels();

    const model = models.find(m => m.name === modelName);

    if (!model) {
        return res.status(404).json({
            success: false,
            error: {
                message: `Modèle '${modelName}' non trouvé`,
                code: 'MODEL_NOT_FOUND'
            }
        });
    }

    res.json({
        success: true,
        data: model
    });
}));

/**
 * @route POST /api/models/pull
 * @desc Télécharger un nouveau modèle
 */
router.post('/pull', asyncHandler(async (req, res) => {
    const { modelName } = req.body;

    if (!modelName) {
        return res.status(400).json({
            success: false,
            error: {
                message: 'Nom de modèle requis',
                code: 'MODEL_NAME_REQUIRED'
            }
        });
    }

    logger.info(`Début du téléchargement du modèle: ${modelName}`);

    const result = await ollamaService.pullModel(modelName);

    res.json({
        success: true,
        data: {
            message: `Modèle '${modelName}' téléchargé avec succès`,
            modelName,
            result
        }
    });
}));

/**
 * @route GET /api/models/test/:modelName
 * @desc Tester un modèle avec un prompt simple
 */
router.get('/test/:modelName', asyncHandler(async (req, res) => {
    const { modelName } = req.params;
    const testPrompt = 'Bonjour, pouvez-vous répondre brièvement pour confirmer que vous fonctionnez correctement ?';

    const startTime = Date.now();

    try {
        const response = await ollamaService.generateCompletion(modelName, testPrompt, {
            max_tokens: 100,
            temperature: 0.3
        });

        const duration = Date.now() - startTime;

        res.json({
            success: true,
            data: {
                modelName,
                testPrompt,
                response: response.trim(),
                responseTime: `${duration}ms`,
                status: 'working'
            }
        });

    } catch (error) {
        const duration = Date.now() - startTime;

        res.status(503).json({
            success: false,
            error: {
                message: `Échec du test du modèle '${modelName}'`,
                details: error.message,
                responseTime: `${duration}ms`,
                status: 'failed'
            }
        });
    }
}));

/**
 * @route GET /api/models/stats/usage
 * @desc Obtenir les statistiques d'utilisation des modèles
 */
router.get('/stats/usage', asyncHandler(async (req, res) => {
    // TODO: Implémenter les statistiques depuis une base de données ou cache
    const stats = {
        totalRequests: 0,
        modelUsage: {},
        averageResponseTime: 0,
        successRate: 0,
        lastUpdated: new Date().toISOString()
    };

    res.json({
        success: true,
        data: stats
    });
}));

module.exports = router;

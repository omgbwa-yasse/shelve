const express = require('express');
const recordsController = require('../../controllers/recordsController');
const { asyncHandler, validateRequest } = require('../../middleware/errorHandler');
const Joi = require('joi');

const router = express.Router();

// Schémas de validation
const summarySchema = Joi.object({
    content: Joi.string().required().min(10).max(50000),
    model: Joi.string().optional(),
    options: Joi.object({
        maxLength: Joi.number().integer().min(50).max(1000).default(200),
        minLength: Joi.number().integer().min(20).max(500).default(50),
        type: Joi.string().valid('basic', 'detailed', 'technical').default('basic'),
        language: Joi.string().valid('fr', 'en').default('fr')
    }).optional()
});

const keywordsSchema = Joi.object({
    content: Joi.string().required().min(10).max(30000),
    count: Joi.number().integer().min(3).max(20).default(10),
    model: Joi.string().optional(),
    options: Joi.object({
        language: Joi.string().valid('fr', 'en').default('fr'),
        includeNER: Joi.boolean().default(false),
        minLength: Joi.number().integer().min(2).max(10).default(3),
        excludeCommon: Joi.boolean().default(true)
    }).optional()
});

const titleSchema = Joi.object({
    title: Joi.string().optional().max(500),
    content: Joi.string().optional().max(10000),
    model: Joi.string().optional(),
    options: Joi.object({
        style: Joi.string().valid('formal', 'casual', 'technical').default('formal'),
        language: Joi.string().valid('fr', 'en').default('fr'),
        maxLength: Joi.number().integer().min(10).max(200).default(100),
        type: Joi.string().valid('reformulation', 'archival', 'generation').default('reformulation')
    }).optional()
}).or('title', 'content');

const analysisSchema = Joi.object({
    content: Joi.string().required().min(10).max(100000),
    type: Joi.string().valid('general', 'content', 'structure', 'sentiment', 'technical').default('general'),
    model: Joi.string().optional(),
    options: Joi.object({
        language: Joi.string().valid('fr', 'en').default('fr'),
        depth: Joi.string().valid('shallow', 'deep').default('shallow')
    }).optional()
});

const completeProcessingSchema = Joi.object({
    content: Joi.string().required().min(10).max(50000),
    model: Joi.string().optional(),
    options: Joi.object({
        summary: Joi.object({
            maxLength: Joi.number().integer().min(50).max(1000).default(200),
            type: Joi.string().valid('basic', 'detailed', 'technical').default('basic')
        }).optional(),
        keywords: Joi.object({
            count: Joi.number().integer().min(3).max(20).default(10),
            includeNER: Joi.boolean().default(false)
        }).optional(),
        keywordCount: Joi.number().integer().min(3).max(20).default(10),
        analysis: Joi.object({
            depth: Joi.string().valid('shallow', 'deep').default('shallow')
        }).optional(),
        analysisType: Joi.string().valid('general', 'content', 'structure').default('general')
    }).optional()
});

// Routes

/**
 * @route GET /api/records
 * @desc Obtenir les informations sur les opérations disponibles
 */
router.get('/', (req, res) => {
    res.json({
        success: true,
        data: {
            operations: {
                summarize: {
                    method: 'POST',
                    path: '/api/records/summarize',
                    description: 'Générer un résumé de contenu',
                    parameters: ['content', 'model?', 'options?']
                },
                keywords: {
                    method: 'POST',
                    path: '/api/records/keywords',
                    description: 'Extraire des mots-clés',
                    parameters: ['content', 'count?', 'model?', 'options?']
                },
                'keywords-with-scores': {
                    method: 'POST',
                    path: '/api/records/keywords-with-scores',
                    description: 'Extraire des mots-clés avec scores',
                    parameters: ['content', 'count?', 'model?']
                },
                'reformulate-title': {
                    method: 'POST',
                    path: '/api/records/reformulate-title',
                    description: 'Reformuler un titre (standard, archivistique ou génération)',
                    parameters: ['title|content', 'model?', 'options?'],
                    types: ['reformulation', 'archival', 'generation']
                },
                analyze: {
                    method: 'POST',
                    path: '/api/records/analyze',
                    description: 'Analyser le contenu',
                    parameters: ['content', 'type?', 'model?', 'options?']
                },
                'process-complete': {
                    method: 'POST',
                    path: '/api/records/process-complete',
                    description: 'Traitement complet (résumé + mots-clés + analyse)',
                    parameters: ['content', 'model?', 'options?']
                }
            },
            stats: {
                method: 'GET',
                path: '/api/records/stats',
                description: 'Statistiques d\'utilisation'
            }
        }
    });
});

/**
 * @route POST /api/records/summarize
 * @desc Générer un résumé de contenu
 */
router.post('/summarize', 
    validateRequest(summarySchema),
    asyncHandler(recordsController.summarize)
);

/**
 * @route POST /api/records/keywords
 * @desc Extraire des mots-clés
 */
router.post('/keywords',
    validateRequest(keywordsSchema),
    asyncHandler(recordsController.extractKeywords)
);

/**
 * @route POST /api/records/keywords-with-scores
 * @desc Extraire des mots-clés avec scores
 */
router.post('/keywords-with-scores',
    validateRequest(keywordsSchema),
    asyncHandler(recordsController.extractKeywordsWithScores)
);

/**
 * @route POST /api/records/reformulate-title
 * @desc Reformuler un titre
 */
router.post('/reformulate-title',
    validateRequest(titleSchema),
    asyncHandler(recordsController.reformulateTitle)
);

/**
 * @route POST /api/records/analyze
 * @desc Analyser le contenu
 */
router.post('/analyze',
    validateRequest(analysisSchema),
    asyncHandler(recordsController.analyze)
);

/**
 * @route POST /api/records/process-complete
 * @desc Traitement complet (résumé + mots-clés + analyse)
 */
router.post('/process-complete',
    validateRequest(completeProcessingSchema),
    asyncHandler(recordsController.processComplete)
);

/**
 * @route GET /api/records/stats
 * @desc Obtenir les statistiques d'utilisation
 */
router.get('/stats',
    asyncHandler(recordsController.getStats)
);

module.exports = router;

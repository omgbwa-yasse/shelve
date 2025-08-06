const express = require('express');
const { asyncHandler } = require('../../middleware/errorHandler');
const logger = require('../../utils/logger');

const router = express.Router();

// Stockage temporaire des paramètres (à remplacer par une base de données)
let settings = {
    system: {
        logLevel: process.env.LOG_LEVEL || 'info',
        maxConcurrentRequests: parseInt(process.env.OLLAMA_MAX_CONCURRENT) || 5,
        requestTimeout: parseInt(process.env.OLLAMA_TIMEOUT) || 120000
    },
    ollama: {
        baseURL: process.env.OLLAMA_BASE_URL || 'http://localhost:11434',
        defaultModel: process.env.OLLAMA_DEFAULT_MODEL || 'llama3.2',
        models: {
            summary: process.env.OLLAMA_SUMMARY_MODEL || 'llama3.2',
            keywords: process.env.OLLAMA_KEYWORDS_MODEL || 'llama3.2',
            title: process.env.OLLAMA_TITLE_MODEL || 'llama3.2',
            analysis: process.env.OLLAMA_ANALYSIS_MODEL || 'llama3.2'
        }
    },
    processing: {
        defaultSummaryLength: 200,
        defaultKeywordCount: 10,
        maxContentLength: 50000,
        enableCache: true
    },
    security: {
        rateLimitEnabled: true,
        rateLimitWindow: parseInt(process.env.RATE_LIMIT_WINDOW) || 900000,
        rateLimitMax: parseInt(process.env.RATE_LIMIT_MAX) || 100
    }
};

/**
 * @route GET /api/settings
 * @desc Obtenir tous les paramètres de configuration
 */
router.get('/', asyncHandler(async (req, res) => {
    res.json({
        success: true,
        data: settings
    });
}));

/**
 * @route GET /api/settings/:category
 * @desc Obtenir les paramètres d'une catégorie spécifique
 */
router.get('/:category', asyncHandler(async (req, res) => {
    const { category } = req.params;

    if (!settings[category]) {
        return res.status(404).json({
            success: false,
            error: {
                message: `Catégorie de paramètres '${category}' non trouvée`,
                code: 'CATEGORY_NOT_FOUND',
                availableCategories: Object.keys(settings)
            }
        });
    }

    res.json({
        success: true,
        data: {
            category,
            settings: settings[category]
        }
    });
}));

/**
 * @route PUT /api/settings/:category
 * @desc Mettre à jour les paramètres d'une catégorie
 */
router.put('/:category', asyncHandler(async (req, res) => {
    const { category } = req.params;
    const updates = req.body;

    if (!settings[category]) {
        return res.status(404).json({
            success: false,
            error: {
                message: `Catégorie de paramètres '${category}' non trouvée`,
                code: 'CATEGORY_NOT_FOUND'
            }
        });
    }

    // Validation basique des types de données
    const validationResult = validateSettings(category, updates);
    if (!validationResult.isValid) {
        return res.status(400).json({
            success: false,
            error: {
                message: 'Paramètres invalides',
                code: 'INVALID_SETTINGS',
                details: validationResult.errors
            }
        });
    }

    // Sauvegarder les anciens paramètres pour le rollback
    const previousSettings = { ...settings[category] };

    try {
        // Mettre à jour les paramètres
        settings[category] = {
            ...settings[category],
            ...updates
        };

        logger.info(`Paramètres mis à jour pour la catégorie: ${category}`, {
            category,
            updates,
            user: req.user || 'anonymous'
        });

        res.json({
            success: true,
            data: {
                message: `Paramètres de la catégorie '${category}' mis à jour avec succès`,
                category,
                updated: updates,
                current: settings[category]
            }
        });

    } catch (error) {
        // Rollback en cas d'erreur
        settings[category] = previousSettings;

        logger.error(`Erreur mise à jour paramètres ${category}:`, error.message);
        throw error;
    }
}));

/**
 * @route POST /api/settings/reset/:category
 * @desc Réinitialiser les paramètres d'une catégorie aux valeurs par défaut
 */
router.post('/reset/:category', asyncHandler(async (req, res) => {
    const { category } = req.params;

    if (!settings[category]) {
        return res.status(404).json({
            success: false,
            error: {
                message: `Catégorie de paramètres '${category}' non trouvée`,
                code: 'CATEGORY_NOT_FOUND'
            }
        });
    }

    const defaultSettings = getDefaultSettings(category);
    const previousSettings = { ...settings[category] };

    settings[category] = defaultSettings;

    logger.info(`Paramètres réinitialisés pour la catégorie: ${category}`, {
        category,
        previous: previousSettings,
        current: defaultSettings
    });

    res.json({
        success: true,
        data: {
            message: `Paramètres de la catégorie '${category}' réinitialisés`,
            category,
            settings: defaultSettings
        }
    });
}));

/**
 * @route GET /api/settings/export/all
 * @desc Exporter tous les paramètres
 */
router.get('/export/all', asyncHandler(async (req, res) => {
    const exportData = {
        timestamp: new Date().toISOString(),
        version: '1.0.0',
        settings
    };

    res.json({
        success: true,
        data: exportData
    });
}));

/**
 * @route POST /api/settings/import
 * @desc Importer des paramètres
 */
router.post('/import', asyncHandler(async (req, res) => {
    const { settings: importedSettings, overwrite = false } = req.body;

    if (!importedSettings || typeof importedSettings !== 'object') {
        return res.status(400).json({
            success: false,
            error: {
                message: 'Paramètres d\'importation invalides',
                code: 'INVALID_IMPORT_DATA'
            }
        });
    }

    const backup = { ...settings };
    const imported = [];
    const errors = [];

    try {
        for (const [category, categorySettings] of Object.entries(importedSettings)) {
            if (settings[category]) {
                const validationResult = validateSettings(category, categorySettings);

                if (validationResult.isValid) {
                    if (overwrite) {
                        settings[category] = categorySettings;
                    } else {
                        settings[category] = {
                            ...settings[category],
                            ...categorySettings
                        };
                    }
                    imported.push(category);
                } else {
                    errors.push({
                        category,
                        errors: validationResult.errors
                    });
                }
            } else {
                errors.push({
                    category,
                    errors: ['Catégorie inconnue']
                });
            }
        }

        logger.info('Importation de paramètres', {
            imported,
            errors: errors.length,
            overwrite
        });

        res.json({
            success: true,
            data: {
                message: 'Importation des paramètres terminée',
                imported,
                errors,
                totalCategories: imported.length,
                errorCount: errors.length
            }
        });

    } catch (error) {
        // Rollback complet en cas d'erreur
        settings = backup;
        logger.error('Erreur importation paramètres:', error.message);
        throw error;
    }
}));

// Fonctions utilitaires

function validateSettings(category, updates) {
    const errors = [];

    switch (category) {
        case 'system':
            if (updates.logLevel && !['error', 'warn', 'info', 'debug'].includes(updates.logLevel)) {
                errors.push('logLevel doit être: error, warn, info, ou debug');
            }
            if (updates.maxConcurrentRequests && (updates.maxConcurrentRequests < 1 || updates.maxConcurrentRequests > 20)) {
                errors.push('maxConcurrentRequests doit être entre 1 et 20');
            }
            if (updates.requestTimeout && (updates.requestTimeout < 5000 || updates.requestTimeout > 300000)) {
                errors.push('requestTimeout doit être entre 5000 et 300000 ms');
            }
            break;

        case 'ollama':
            if (updates.baseURL && !updates.baseURL.match(/^https?:\/\/.+/)) {
                errors.push('baseURL doit être une URL valide');
            }
            break;

        case 'processing':
            if (updates.defaultSummaryLength && (updates.defaultSummaryLength < 50 || updates.defaultSummaryLength > 1000)) {
                errors.push('defaultSummaryLength doit être entre 50 et 1000');
            }
            if (updates.defaultKeywordCount && (updates.defaultKeywordCount < 3 || updates.defaultKeywordCount > 20)) {
                errors.push('defaultKeywordCount doit être entre 3 et 20');
            }
            if (updates.maxContentLength && (updates.maxContentLength < 1000 || updates.maxContentLength > 100000)) {
                errors.push('maxContentLength doit être entre 1000 et 100000');
            }
            break;

        case 'security':
            if (updates.rateLimitWindow && (updates.rateLimitWindow < 60000 || updates.rateLimitWindow > 3600000)) {
                errors.push('rateLimitWindow doit être entre 60000 et 3600000 ms');
            }
            if (updates.rateLimitMax && (updates.rateLimitMax < 10 || updates.rateLimitMax > 1000)) {
                errors.push('rateLimitMax doit être entre 10 et 1000');
            }
            break;
    }

    return {
        isValid: errors.length === 0,
        errors
    };
}

function getDefaultSettings(category) {
    const defaults = {
        system: {
            logLevel: 'info',
            maxConcurrentRequests: 5,
            requestTimeout: 120000
        },
        ollama: {
            baseURL: 'http://localhost:11434',
            defaultModel: 'llama3.2',
            models: {
                summary: 'llama3.2',
                keywords: 'llama3.2',
                title: 'llama3.2',
                analysis: 'llama3.2'
            }
        },
        processing: {
            defaultSummaryLength: 200,
            defaultKeywordCount: 10,
            maxContentLength: 50000,
            enableCache: true
        },
        security: {
            rateLimitEnabled: true,
            rateLimitWindow: 900000,
            rateLimitMax: 100
        }
    };

    return defaults[category] || {};
}

module.exports = router;

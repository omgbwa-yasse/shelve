const summaryService = require('../services/processing/summaryService');
const keywordService = require('../services/processing/keywordService');
const titleService = require('../services/processing/titleService');
const analysisService = require('../services/processing/analysisService');
const logger = require('../utils/logger');
const { ValidationError, ProcessingError } = require('../utils/errors');

class RecordsController {
    /**
     * Générer un résumé de contenu
     */
    async summarize(req, res, next) {
        try {
            const { content, model, options = {} } = req.body;
            
            if (!content || content.trim().length === 0) {
                throw new ValidationError('Le contenu est requis et ne peut pas être vide');
            }

            if (content.length > 50000) {
                throw new ValidationError('Le contenu est trop long (maximum 50 000 caractères)');
            }

            const startTime = Date.now();
            
            const summary = await summaryService.generate(content, model, options);
            
            const duration = Date.now() - startTime;
            logger.performance('Summary generation', duration);

            res.json({ 
                success: true, 
                data: {
                    summary,
                    metadata: {
                        model: model || 'default',
                        originalLength: content.length,
                        summaryLength: summary.length,
                        processingTime: duration,
                        options
                    }
                }
            });
        } catch (error) {
            next(error);
        }
    }

    /**
     * Extraire des mots-clés
     */
    async extractKeywords(req, res, next) {
        try {
            const { content, count, model, options = {} } = req.body;
            
            if (!content || content.trim().length === 0) {
                throw new ValidationError('Le contenu est requis et ne peut pas être vide');
            }

            if (content.length > 30000) {
                throw new ValidationError('Le contenu est trop long pour l\'extraction de mots-clés (maximum 30 000 caractères)');
            }

            const startTime = Date.now();
            
            const keywords = await keywordService.extract(content, count, model, options);
            
            const duration = Date.now() - startTime;
            logger.performance('Keywords extraction', duration);

            res.json({ 
                success: true, 
                data: {
                    keywords,
                    metadata: {
                        model: model || 'default',
                        count: keywords.length,
                        requestedCount: count,
                        originalLength: content.length,
                        processingTime: duration,
                        options
                    }
                }
            });
        } catch (error) {
            next(error);
        }
    }

    /**
     * Extraire des mots-clés avec scores
     */
    async extractKeywordsWithScores(req, res, next) {
        try {
            const { content, count, model } = req.body;
            
            if (!content || content.trim().length === 0) {
                throw new ValidationError('Le contenu est requis et ne peut pas être vide');
            }

            const startTime = Date.now();
            
            const keywordsWithScores = await keywordService.extractWithScores(content, count, model);
            
            const duration = Date.now() - startTime;
            logger.performance('Keywords with scores extraction', duration);

            res.json({ 
                success: true, 
                data: {
                    keywords: keywordsWithScores,
                    metadata: {
                        model: model || 'default',
                        count: keywordsWithScores.length,
                        processingTime: duration
                    }
                }
            });
        } catch (error) {
            next(error);
        }
    }

    /**
     * Reformuler un titre
     */
    async reformulateTitle(req, res, next) {
        try {
            const { title, content, model, options = {} } = req.body;
            
            if (!title && !content) {
                throw new ValidationError('Le titre ou le contenu est requis');
            }

            if (title && title.length > 500) {
                throw new ValidationError('Le titre est trop long (maximum 500 caractères)');
            }

            const startTime = Date.now();
            
            const newTitle = await titleService.reformulate(title, content, model, options);
            
            const duration = Date.now() - startTime;
            logger.performance('Title reformulation', duration);

            res.json({ 
                success: true, 
                data: {
                    title: newTitle,
                    metadata: {
                        model: model || 'default',
                        originalTitle: title,
                        hasContent: !!content,
                        processingTime: duration,
                        options
                    }
                }
            });
        } catch (error) {
            next(error);
        }
    }

    /**
     * Analyser le contenu
     */
    async analyze(req, res, next) {
        try {
            const { content, type = 'general', model, options = {} } = req.body;
            
            if (!content || content.trim().length === 0) {
                throw new ValidationError('Le contenu est requis et ne peut pas être vide');
            }

            if (content.length > 100000) {
                throw new ValidationError('Le contenu est trop long pour l\'analyse (maximum 100 000 caractères)');
            }

            const validTypes = ['general', 'content', 'structure', 'sentiment', 'technical'];
            if (!validTypes.includes(type)) {
                throw new ValidationError(`Type d'analyse invalide. Types valides: ${validTypes.join(', ')}`);
            }

            const startTime = Date.now();
            
            const analysis = await analysisService.analyze(content, type, model, options);
            
            const duration = Date.now() - startTime;
            logger.performance(`${type} analysis`, duration);

            res.json({ 
                success: true, 
                data: {
                    analysis,
                    metadata: {
                        model: model || 'default',
                        type,
                        contentLength: content.length,
                        processingTime: duration,
                        options
                    }
                }
            });
        } catch (error) {
            next(error);
        }
    }

    /**
     * Traitement complet (résumé + mots-clés + analyse)
     */
    async processComplete(req, res, next) {
        try {
            const { content, model, options = {} } = req.body;
            
            if (!content || content.trim().length === 0) {
                throw new ValidationError('Le contenu est requis et ne peut pas être vide');
            }

            if (content.length > 50000) {
                throw new ValidationError('Le contenu est trop long pour le traitement complet (maximum 50 000 caractères)');
            }

            const startTime = Date.now();
            
            // Exécution en parallèle des différents traitements
            const [summary, keywords, analysis] = await Promise.all([
                summaryService.generate(content, model, options.summary || {}),
                keywordService.extract(content, options.keywordCount || 10, model, options.keywords || {}),
                analysisService.analyze(content, options.analysisType || 'general', model, options.analysis || {})
            ]);
            
            const duration = Date.now() - startTime;
            logger.performance('Complete processing', duration);

            res.json({ 
                success: true, 
                data: {
                    summary,
                    keywords,
                    analysis,
                    metadata: {
                        model: model || 'default',
                        contentLength: content.length,
                        processingTime: duration,
                        options
                    }
                }
            });
        } catch (error) {
            next(error);
        }
    }

    /**
     * Obtenir les statistiques du contrôleur
     */
    async getStats(req, res, next) {
        try {
            // TODO: Implémenter les statistiques depuis une base de données ou cache
            const stats = {
                totalRequests: 0,
                successfulRequests: 0,
                averageProcessingTime: 0,
                popularOperations: [],
                lastUpdated: new Date().toISOString()
            };

            res.json({
                success: true,
                data: stats
            });
        } catch (error) {
            next(error);
        }
    }
}

module.exports = new RecordsController();

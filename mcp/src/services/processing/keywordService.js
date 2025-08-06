const ollamaService = require('../ai/ollamaService');
const promptService = require('../ai/promptService');
const logger = require('../../utils/logger');

class KeywordService {
    constructor() {
        this.defaultCount = 10;
        this.maxCount = 20;
        this.minCount = 3;
    }

    async extract(content, count = null, model = null, options = {}) {
        try {
            if (!content || content.trim().length === 0) {
                throw new Error('Contenu vide fourni pour l\'extraction de mots-clés');
            }

            const {
                language = 'fr',
                includeNER = false, // Named Entity Recognition
                minLength = 3,
                excludeCommon = true
            } = options;

            // Valider et ajuster le nombre de mots-clés
            const targetCount = this.validateCount(count);
            const targetModel = model || ollamaService.getModelForTask('keywords');

            // Obtenir le prompt pour l'extraction
            const prompt = await promptService.getKeywordsPrompt(content, {
                count: targetCount,
                language,
                includeNER,
                minLength,
                excludeCommon
            });

            logger.debug('Extraction de mots-clés', {
                contentLength: content.length,
                model: targetModel,
                count: targetCount,
                language
            });

            // Extraire les mots-clés
            const response = await ollamaService.generateCompletion(targetModel, prompt, {
                temperature: 0.2, // Très déterministe pour l'extraction
                max_tokens: targetCount * 10 // Estimation large
            });

            // Parser et nettoyer les mots-clés
            const keywords = this.parseKeywords(response, targetCount);

            logger.info('Mots-clés extraits avec succès', {
                originalLength: content.length,
                keywordsCount: keywords.length,
                model: targetModel
            });

            return keywords;

        } catch (error) {
            logger.error('Erreur extraction mots-clés:', {
                message: error.message,
                contentLength: content?.length || 0,
                model,
                count
            });
            throw new Error(`Impossible d'extraire les mots-clés: ${error.message}`);
        }
    }

    validateCount(count) {
        if (!count) return this.defaultCount;
        
        const numCount = parseInt(count);
        if (isNaN(numCount) || numCount < this.minCount) {
            return this.minCount;
        }
        if (numCount > this.maxCount) {
            return this.maxCount;
        }
        
        return numCount;
    }

    parseKeywords(response, targetCount) {
        if (!response) return [];

        // Patterns pour identifier les mots-clés dans la réponse
        const patterns = [
            /\d+\.\s*(.+)/g, // Format "1. mot-clé"
            /[-*]\s*(.+)/g,  // Format "- mot-clé" ou "* mot-clé"
            /(.+)/g          // Fallback: chaque ligne
        ];

        let keywords = [];

        // Essayer chaque pattern
        for (const pattern of patterns) {
            const matches = [...response.matchAll(pattern)];
            if (matches.length > 0) {
                keywords = matches.map(match => match[1].trim());
                break;
            }
        }

        // Si aucun pattern ne fonctionne, diviser par lignes ou virgules
        if (keywords.length === 0) {
            keywords = response.split(/[,\n]/)
                .map(kw => kw.trim())
                .filter(kw => kw.length > 0);
        }

        // Nettoyer et filtrer les mots-clés
        keywords = keywords
            .map(kw => this.cleanKeyword(kw))
            .filter(kw => kw && kw.length >= 2)
            .filter((kw, index, arr) => arr.indexOf(kw) === index) // Supprimer les doublons
            .slice(0, targetCount);

        return keywords;
    }

    cleanKeyword(keyword) {
        if (!keyword) return '';

        // Supprimer la ponctuation en début/fin
        keyword = keyword.replace(/^[^\w\u00C0-\u017F]+|[^\w\u00C0-\u017F]+$/g, '');
        
        // Supprimer les guillemets
        keyword = keyword.replace(/^["']|["']$/g, '');
        
        // Nettoyer les espaces multiples
        keyword = keyword.replace(/\s+/g, ' ').trim();
        
        // Capitaliser la première lettre
        if (keyword.length > 0) {
            keyword = keyword.charAt(0).toUpperCase() + keyword.slice(1).toLowerCase();
        }

        return keyword;
    }

    async extractWithScores(content, count = null, model = null) {
        try {
            const keywords = await this.extract(content, count, model);
            
            // Calculer des scores basiques basés sur la fréquence
            const scores = this.calculateKeywordScores(content, keywords);
            
            return keywords.map((keyword, index) => ({
                keyword,
                score: scores[index] || 0.5,
                rank: index + 1
            }));

        } catch (error) {
            logger.error('Erreur extraction mots-clés avec scores:', error.message);
            throw error;
        }
    }

    calculateKeywordScores(content, keywords) {
        const contentLower = content.toLowerCase();
        const contentLength = content.length;

        return keywords.map(keyword => {
            const keywordLower = keyword.toLowerCase();
            const matches = (contentLower.match(new RegExp(keywordLower, 'g')) || []).length;
            
            // Score basé sur la fréquence relative
            const frequency = matches / contentLength * 1000;
            
            // Score basé sur la position (mots en début plus importants)
            const firstIndex = contentLower.indexOf(keywordLower);
            const positionScore = firstIndex === -1 ? 0 : (1 - firstIndex / contentLength);
            
            // Score combiné
            return Math.min(1, (frequency * 0.7 + positionScore * 0.3));
        });
    }
}

module.exports = new KeywordService();

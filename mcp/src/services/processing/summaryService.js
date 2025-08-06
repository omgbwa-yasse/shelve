const ollamaService = require('../ai/ollamaService');
const promptService = require('../ai/promptService');
const logger = require('../../utils/logger');

class SummaryService {
    constructor() {
        this.defaultMaxLength = 200;
        this.defaultMinLength = 50;
    }

    async generate(content, model = null, options = {}) {
        try {
            if (!content || content.trim().length === 0) {
                throw new Error('Contenu vide fourni pour le résumé');
            }

            const {
                maxLength = this.defaultMaxLength,
                minLength = this.defaultMinLength,
                type = 'basic',
                language = 'fr'
            } = options;

            // Obtenir le modèle approprié
            const targetModel = model || ollamaService.getModelForTask('summary');

            // Obtenir le prompt pour le résumé
            const prompt = await promptService.getSummaryPrompt(content, {
                maxLength,
                minLength,
                type,
                language
            });

            logger.debug('Génération de résumé', {
                contentLength: content.length,
                model: targetModel,
                type,
                maxLength,
                minLength
            });

            // Générer le résumé
            const summary = await ollamaService.generateCompletion(targetModel, prompt, {
                temperature: 0.3, // Plus déterministe pour les résumés
                max_tokens: Math.max(maxLength + 50, 100)
            });

            // Nettoyer et valider le résumé
            const cleanedSummary = this.cleanSummary(summary, maxLength);

            logger.info('Résumé généré avec succès', {
                originalLength: content.length,
                summaryLength: cleanedSummary.length,
                model: targetModel
            });

            return cleanedSummary;

        } catch (error) {
            logger.error('Erreur génération résumé:', {
                message: error.message,
                contentLength: content?.length || 0,
                model
            });
            throw new Error(`Impossible de générer le résumé: ${error.message}`);
        }
    }

    cleanSummary(summary, maxLength) {
        if (!summary) return '';

        // Supprimer les espaces en trop et les retours à la ligne multiples
        let cleaned = summary.trim().replace(/\n\s*\n/g, '\n').replace(/\s+/g, ' ');

        // Tronquer si nécessaire tout en préservant les phrases complètes
        if (cleaned.length > maxLength) {
            cleaned = cleaned.substring(0, maxLength);
            
            // Chercher le dernier point, point d'exclamation ou point d'interrogation
            const lastSentenceEnd = Math.max(
                cleaned.lastIndexOf('.'),
                cleaned.lastIndexOf('!'),
                cleaned.lastIndexOf('?')
            );

            if (lastSentenceEnd > maxLength * 0.7) { // Au moins 70% du texte
                cleaned = cleaned.substring(0, lastSentenceEnd + 1);
            } else {
                // Sinon, tronquer au dernier espace
                const lastSpace = cleaned.lastIndexOf(' ');
                if (lastSpace > 0) {
                    cleaned = cleaned.substring(0, lastSpace) + '...';
                }
            }
        }

        return cleaned;
    }

    async generateMultiple(content, types = ['basic', 'detailed'], model = null) {
        try {
            const summaries = {};

            for (const type of types) {
                summaries[type] = await this.generate(content, model, { type });
            }

            return summaries;

        } catch (error) {
            logger.error('Erreur génération résumés multiples:', error.message);
            throw error;
        }
    }
}

module.exports = new SummaryService();

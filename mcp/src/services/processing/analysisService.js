const ollamaService = require('../ai/ollamaService');
const promptService = require('../ai/promptService');
const logger = require('../../utils/logger');

class AnalysisService {
    constructor() {
        this.supportedTypes = [
            'general',
            'content',
            'structure',
            'sentiment',
            'technical'
        ];
    }

    async analyze(content, type = 'general', model = null, options = {}) {
        try {
            if (!content || content.trim().length === 0) {
                throw new Error('Contenu vide fourni pour l\'analyse');
            }

            if (!this.supportedTypes.includes(type)) {
                throw new Error(`Type d'analyse non supporté: ${type}`);
            }

            const {
                language = 'fr',
                depth = 'shallow'
            } = options;

            // Obtenir le modèle approprié
            const targetModel = model || ollamaService.getModelForTask('analysis');

            // Obtenir le prompt pour l'analyse
            const prompt = await promptService.getAnalysisPrompt(content, type, {
                language,
                depth
            });

            logger.debug('Analyse de contenu', {
                contentLength: content.length,
                type,
                model: targetModel,
                depth,
                language
            });

            // Effectuer l'analyse
            const analysis = await ollamaService.generateCompletion(targetModel, prompt, {
                temperature: 0.3, // Analyse objective
                max_tokens: this.getMaxTokensForType(type)
            });

            // Structurer le résultat selon le type
            const structuredAnalysis = this.structureAnalysis(analysis, type);

            logger.info('Analyse complétée avec succès', {
                contentLength: content.length,
                type,
                model: targetModel,
                analysisLength: analysis.length
            });

            return structuredAnalysis;

        } catch (error) {
            logger.error('Erreur analyse contenu:', {
                message: error.message,
                contentLength: content?.length || 0,
                type,
                model
            });
            throw new Error(`Impossible d'analyser le contenu: ${error.message}`);
        }
    }

    getMaxTokensForType(type) {
        const tokenLimits = {
            'general': 500,
            'content': 600,
            'structure': 400,
            'sentiment': 300,
            'technical': 700
        };

        return tokenLimits[type] || 500;
    }

    structureAnalysis(rawAnalysis, type) {
        const baseStructure = {
            type,
            content: rawAnalysis.trim(),
            timestamp: new Date().toISOString(),
            confidence: this.calculateConfidence(rawAnalysis)
        };

        // Structuration spécifique selon le type
        switch (type) {
            case 'general':
                return {
                    ...baseStructure,
                    sections: this.extractSections(rawAnalysis),
                    keyPoints: this.extractKeyPoints(rawAnalysis)
                };

            case 'content':
                return {
                    ...baseStructure,
                    quality: this.assessContentQuality(rawAnalysis),
                    completeness: this.assessCompleteness(rawAnalysis),
                    relevance: this.assessRelevance(rawAnalysis)
                };

            case 'structure':
                return {
                    ...baseStructure,
                    organization: this.assessOrganization(rawAnalysis),
                    coherence: this.assessCoherence(rawAnalysis),
                    flow: this.assessFlow(rawAnalysis)
                };

            case 'sentiment':
                return {
                    ...baseStructure,
                    tone: this.extractTone(rawAnalysis),
                    emotion: this.extractEmotion(rawAnalysis),
                    objectivity: this.assessObjectivity(rawAnalysis)
                };

            case 'technical':
                return {
                    ...baseStructure,
                    complexity: this.assessComplexity(rawAnalysis),
                    terminology: this.extractTerminology(rawAnalysis),
                    accuracy: this.assessAccuracy(rawAnalysis)
                };

            default:
                return baseStructure;
        }
    }

    calculateConfidence(analysis) {
        // Calcul basique de confiance basé sur la longueur et la structure
        const length = analysis.length;
        const hasStructure = /\d+\.|[-*]|\n/.test(analysis);
        const hasSpecificTerms = /(analyse|évaluation|conclusion|résultat)/i.test(analysis);

        let confidence = 0.5; // Base

        if (length > 100) confidence += 0.2;
        if (length > 300) confidence += 0.1;
        if (hasStructure) confidence += 0.1;
        if (hasSpecificTerms) confidence += 0.1;

        return Math.min(1.0, confidence);
    }

    extractSections(analysis) {
        // Tentative d'extraction de sections du texte d'analyse
        const lines = analysis.split('\n').filter(line => line.trim());
        const sections = [];

        for (const line of lines) {
            if (line.match(/^\d+\.|^[-*]/)) {
                sections.push(line.trim());
            }
        }

        return sections.length > 0 ? sections : [analysis];
    }

    extractKeyPoints(analysis) {
        // Extraction simple de points clés
        const sentences = analysis.split(/[.!?]+/).filter(s => s.trim().length > 20);
        return sentences.slice(0, 3).map(s => s.trim());
    }

    assessContentQuality(analysis) {
        // Évaluation basique de la qualité
        const positiveIndicators = /excellente?|bonne?|qualité|riche|détaillé/gi;
        const negativeIndicators = /faible|manque|insuffisant|limité/gi;

        const positiveMatches = (analysis.match(positiveIndicators) || []).length;
        const negativeMatches = (analysis.match(negativeIndicators) || []).length;

        if (positiveMatches > negativeMatches) return 'high';
        if (negativeMatches > positiveMatches) return 'low';
        return 'medium';
    }

    assessCompleteness(analysis) {
        const completeIndicators = /complet|exhaustif|détaillé|approfondi/gi;
        const incompleteIndicators = /incomplet|partiel|manque|superficiel/gi;

        const completeMatches = (analysis.match(completeIndicators) || []).length;
        const incompleteMatches = (analysis.match(incompleteIndicators) || []).length;

        if (completeMatches > incompleteMatches) return 'complete';
        if (incompleteMatches > completeMatches) return 'incomplete';
        return 'partial';
    }

    assessRelevance(analysis) {
        const relevantIndicators = /pertinent|approprié|adapté|utile/gi;
        const irrelevantIndicators = /hors-sujet|inapproprié|non pertinent/gi;

        const relevantMatches = (analysis.match(relevantIndicators) || []).length;
        const irrelevantMatches = (analysis.match(irrelevantIndicators) || []).length;

        if (relevantMatches > irrelevantMatches) return 'high';
        if (irrelevantMatches > relevantMatches) return 'low';
        return 'medium';
    }

    assessOrganization(analysis) {
        const organizedIndicators = /structuré|organisé|logique|cohérent/gi;
        const disorganizedIndicators = /désorganisé|confus|incohérent/gi;

        const organizedMatches = (analysis.match(organizedIndicators) || []).length;
        const disorganizedMatches = (analysis.match(disorganizedIndicators) || []).length;

        if (organizedMatches > disorganizedMatches) return 'well-organized';
        if (disorganizedMatches > organizedMatches) return 'poorly-organized';
        return 'moderately-organized';
    }

    assessCoherence(analysis) {
        const coherentIndicators = /cohérent|fluide|logique|uni/gi;
        const incoherentIndicators = /incohérent|décousu|illogique/gi;

        const coherentMatches = (analysis.match(coherentIndicators) || []).length;
        const incoherentMatches = (analysis.match(incoherentIndicators) || []).length;

        if (coherentMatches > incoherentMatches) return 'high';
        if (incoherentMatches > coherentMatches) return 'low';
        return 'medium';
    }

    assessFlow(analysis) {
        const flowIndicators = /fluide|transition|enchaînement|continuité/gi;
        const matches = (analysis.match(flowIndicators) || []).length;

        if (matches >= 2) return 'smooth';
        if (matches === 1) return 'adequate';
        return 'choppy';
    }

    extractTone(analysis) {
        const tones = {
            formal: /formel|officiel|professionnel/gi,
            casual: /décontracté|familier|informel/gi,
            neutral: /neutre|objectif|impartial/gi,
            positive: /positif|optimiste|encourageant/gi,
            negative: /négatif|pessimiste|critique/gi
        };

        for (const [tone, pattern] of Object.entries(tones)) {
            if (pattern.test(analysis)) {
                return tone;
            }
        }

        return 'neutral';
    }

    extractEmotion(analysis) {
        const emotions = {
            joy: /joie|bonheur|satisfaction|enthousiasme/gi,
            sadness: /tristesse|mélancolie|déception/gi,
            anger: /colère|irritation|frustration/gi,
            fear: /peur|inquiétude|anxiété/gi,
            surprise: /surprise|étonnement/gi,
            neutral: /calme|posé|mesuré|équilibré/gi
        };

        for (const [emotion, pattern] of Object.entries(emotions)) {
            if (pattern.test(analysis)) {
                return emotion;
            }
        }

        return 'neutral';
    }

    assessObjectivity(analysis) {
        const subjectiveIndicators = /je pense|à mon avis|personnel|subjectif/gi;
        const objectiveIndicators = /fait|données|objectif|impartial/gi;

        const subjectiveMatches = (analysis.match(subjectiveIndicators) || []).length;
        const objectiveMatches = (analysis.match(objectiveIndicators) || []).length;

        if (objectiveMatches > subjectiveMatches) return 'high';
        if (subjectiveMatches > objectiveMatches) return 'low';
        return 'medium';
    }

    assessComplexity(analysis) {
        const complexIndicators = /complexe|technique|avancé|spécialisé/gi;
        const simpleIndicators = /simple|basique|élémentaire|accessible/gi;

        const complexMatches = (analysis.match(complexIndicators) || []).length;
        const simpleMatches = (analysis.match(simpleIndicators) || []).length;

        if (complexMatches > simpleMatches) return 'high';
        if (simpleMatches > complexMatches) return 'low';
        return 'medium';
    }

    extractTerminology(analysis) {
        // Extraction basique de terminologie technique
        const technicalPattern = /\b[A-Z]{2,}|\b\w+[A-Z]\w+|\b\w*technique?\w*|\b\w*spécialisé\w*/gi;
        const matches = analysis.match(technicalPattern) || [];

        return [...new Set(matches)].slice(0, 5); // Dédoublonner et limiter
    }

    assessAccuracy(analysis) {
        const accurateIndicators = /précis|exact|correct|fiable/gi;
        const inaccurateIndicators = /imprécis|inexact|erreur|approximatif/gi;

        const accurateMatches = (analysis.match(accurateIndicators) || []).length;
        const inaccurateMatches = (analysis.match(inaccurateIndicators) || []).length;

        if (accurateMatches > inaccurateMatches) return 'high';
        if (inaccurateMatches > accurateMatches) return 'low';
        return 'medium';
    }

    getSupportedTypes() {
        return this.supportedTypes;
    }
}

module.exports = new AnalysisService();

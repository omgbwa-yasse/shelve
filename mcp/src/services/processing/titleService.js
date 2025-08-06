const ollamaService = require('../ai/ollamaService');
const promptService = require('../ai/promptService');
const logger = require('../../utils/logger');

class TitleService {
    constructor() {
        this.defaultMaxLength = 100;
        this.maxLength = 200;
    }

    async reformulate(title, content, model = null, options = {}) {
        try {
            if (!title && !content) {
                throw new Error('Titre ou contenu requis pour la reformulation');
            }

            const {
                style = 'formal',
                language = 'fr',
                maxLength = this.defaultMaxLength,
                type = 'reformulation' // 'reformulation', 'archival', 'generation'
            } = options;

            // Valider la longueur maximale
            const targetMaxLength = Math.min(maxLength, this.maxLength);

            // Obtenir le modèle approprié
            const targetModel = model || ollamaService.getModelForTask('title');

            // Obtenir le prompt pour la reformulation avec le type spécifié
            const prompt = await promptService.getTitlePrompt(title, content, {
                style,
                language,
                maxLength: targetMaxLength,
                type
            });

            logger.debug('Reformulation de titre', {
                hasTitle: !!title,
                hasContent: !!content,
                model: targetModel,
                style,
                type,
                maxLength: targetMaxLength
            });

            // Adapter la température selon le type
            let temperature = 0.4; // Défaut
            if (type === 'archival') {
                temperature = 0.2; // Plus déterministe pour les normes archivistiques
            } else if (type === 'generation') {
                temperature = 0.3; // Légèrement plus créatif pour la génération
            }

            // Générer le nouveau titre
            const newTitle = await ollamaService.generateCompletion(targetModel, prompt, {
                temperature,
                max_tokens: Math.max(targetMaxLength / 2, 50)
            });

            // Nettoyer et valider le titre
            const cleanedTitle = this.cleanTitle(newTitle, targetMaxLength, type);

            logger.info('Titre reformulé avec succès', {
                originalTitle: title,
                newTitle: cleanedTitle,
                model: targetModel,
                style,
                type
            });

            return cleanedTitle;

        } catch (error) {
            logger.error('Erreur reformulation titre:', {
                message: error.message,
                hasTitle: !!title,
                hasContent: !!content,
                model
            });
            throw new Error(`Impossible de reformuler le titre: ${error.message}`);
        }
    }

    cleanTitle(title, maxLength, type = 'reformulation') {
        if (!title) return '';

        // Supprimer les guillemets en début/fin
        let cleaned = title.trim().replace(/^["']|["']$/g, '');

        // Supprimer les préfixes courants
        cleaned = cleaned.replace(/^(Titre|Title|Nouveau titre|Titre reformulé|TITRE)\s*[:\-]?\s*/i, '');

        // Spécificités pour les titres archivistiques
        if (type === 'archival' || type === 'generation') {
            // Préserver la ponctuation archivistique
            cleaned = this.normalizeArchivalPunctuation(cleaned);
            
            // Vérifier la structure archivistique
            cleaned = this.validateArchivalStructure(cleaned);
        }

        // Nettoyer les espaces multiples
        cleaned = cleaned.replace(/\s+/g, ' ').trim();

        // Tronquer si nécessaire
        if (cleaned.length > maxLength) {
            cleaned = cleaned.substring(0, maxLength);
            
            // Pour les titres archivistiques, essayer de préserver la structure
            if (type === 'archival' || type === 'generation') {
                cleaned = this.truncateArchivalTitle(cleaned, maxLength);
            } else {
                // Chercher le dernier espace pour éviter de couper un mot
                const lastSpace = cleaned.lastIndexOf(' ');
                if (lastSpace > maxLength * 0.8) { // Au moins 80% du texte
                    cleaned = cleaned.substring(0, lastSpace);
                }
            }
            
            // Supprimer la ponctuation finale si incomplète
            cleaned = cleaned.replace(/[,;:\-\.]+$/, '');
        }

        // Capitaliser la première lettre
        if (cleaned.length > 0) {
            cleaned = cleaned.charAt(0).toUpperCase() + cleaned.slice(1);
        }

        return cleaned;
    }

    normalizeArchivalPunctuation(title) {
        // Normaliser les point-tirets
        title = title.replace(/\s*[-–—]\s*/g, '. — ');
        
        // Normaliser les espaces autour de la ponctuation
        title = title.replace(/\s*:\s*/g, ' : ');
        title = title.replace(/\s*;\s*/g, ' ; ');
        title = title.replace(/\s*,\s*/g, ', ');
        
        // Corriger les doubles espaces
        title = title.replace(/\s+/g, ' ');
        
        return title;
    }

    validateArchivalStructure(title) {
        // Vérifier si le titre commence par un objet suivi d'un point-tiret
        if (!/^[^.]+\.\s*—/.test(title)) {
            // Si pas de structure archivistique détectée, essayer de l'améliorer
            const parts = title.split(/[:.]/);
            if (parts.length >= 2) {
                return `${parts[0].trim()}. — ${parts.slice(1).join(' : ').trim()}`;
            }
        }
        
        return title;
    }

    truncateArchivalTitle(title, maxLength) {
        // Essayer de préserver la structure : Objet. — Action : typologie. Dates
        
        // Trouver les parties principales
        const mainSeparator = title.indexOf('. — ');
        if (mainSeparator === -1) {
            // Pas de structure archivistique, troncature normale
            const lastSpace = title.lastIndexOf(' ');
            return lastSpace > maxLength * 0.8 ? title.substring(0, lastSpace) : title;
        }

        const objectPart = title.substring(0, mainSeparator + 4); // Include ". — "
        const remainingPart = title.substring(mainSeparator + 4);
        
        const availableLength = maxLength - objectPart.length;
        
        if (availableLength > 20) { // Assez de place pour le reste
            const truncatedRemaining = remainingPart.substring(0, availableLength);
            const lastSpace = truncatedRemaining.lastIndexOf(' ');
            
            if (lastSpace > availableLength * 0.7) {
                return objectPart + truncatedRemaining.substring(0, lastSpace);
            }
            return objectPart + truncatedRemaining;
        } else {
            // Pas assez de place, garder seulement l'objet
            return objectPart.replace(/\.\s*—\s*$/, '');
        }
    }    async generateFromContent(content, model = null, options = {}) {
        try {
            if (!content || content.trim().length === 0) {
                throw new Error('Contenu vide fourni pour la génération de titre');
            }

            // Utiliser la reformulation avec seulement le contenu
            return await this.reformulate(null, content, model, {
                ...options,
                style: options.style || 'formal'
            });

        } catch (error) {
            logger.error('Erreur génération titre depuis contenu:', error.message);
            throw error;
        }
    }

    async generateMultiple(title, content, count = 3, model = null, options = {}) {
        try {
            const titles = [];
            const styles = ['formal', 'casual', 'technical'];

            for (let i = 0; i < count; i++) {
                const style = styles[i % styles.length];
                const newTitle = await this.reformulate(title, content, model, {
                    ...options,
                    style
                });
                titles.push({
                    title: newTitle,
                    style,
                    index: i + 1
                });
            }

            return titles;

        } catch (error) {
            logger.error('Erreur génération titres multiples:', error.message);
            throw error;
        }
    }

    validateTitle(title, options = {}) {
        const {
            minLength = 5,
            maxLength = this.maxLength,
            allowEmoji = false,
            allowSpecialChars = true
        } = options;

        const errors = [];

        if (!title || title.trim().length === 0) {
            errors.push('Le titre ne peut pas être vide');
        }

        if (title.length < minLength) {
            errors.push(`Le titre doit contenir au moins ${minLength} caractères`);
        }

        if (title.length > maxLength) {
            errors.push(`Le titre ne peut pas dépasser ${maxLength} caractères`);
        }

        if (!allowEmoji && /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/u.test(title)) {
            errors.push('Les émojis ne sont pas autorisés dans le titre');
        }

        if (!allowSpecialChars && /[<>{}[\]\\|`~]/.test(title)) {
            errors.push('Certains caractères spéciaux ne sont pas autorisés');
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }
}

module.exports = new TitleService();

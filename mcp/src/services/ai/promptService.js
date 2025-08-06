const fs = require('fs').promises;
const path = require('path');
const logger = require('../../utils/logger');

class PromptService {
    constructor() {
        this.templatesDir = path.join(__dirname, '../../../templates');
        this.templateCache = new Map();
        this.maxCacheSize = 50;
    }

    /**
     * Charger un template depuis le cache ou le fichier
     */
    async loadTemplate(templatePath) {
        // Vérifier le cache
        if (this.templateCache.has(templatePath)) {
            return this.templateCache.get(templatePath);
        }

        try {
            const fullPath = path.join(this.templatesDir, templatePath);
            const template = await fs.readFile(fullPath, 'utf8');
            
            // Ajouter au cache
            if (this.templateCache.size >= this.maxCacheSize) {
                // Supprimer le plus ancien
                const firstKey = this.templateCache.keys().next().value;
                this.templateCache.delete(firstKey);
            }
            
            this.templateCache.set(templatePath, template);
            
            logger.debug(`Template chargé: ${templatePath}`);
            return template;

        } catch (error) {
            logger.error(`Erreur chargement template ${templatePath}:`, error.message);
            throw new Error(`Template non trouvé: ${templatePath}`);
        }
    }

    /**
     * Remplacer les variables dans un template
     */
    renderTemplate(template, variables = {}) {
        let rendered = template;

        // Remplacements simples {{variable}}
        for (const [key, value] of Object.entries(variables)) {
            const regex = new RegExp(`{{${key}}}`, 'g');
            rendered = rendered.replace(regex, value || '');
        }

        // Remplacements conditionnels {{#if condition}}
        rendered = this.processConditionals(rendered, variables);

        return rendered;
    }

    /**
     * Traiter les conditions dans les templates
     */
    processConditionals(template, variables) {
        // Pattern pour {{#if condition}}...{{/if}}
        const ifPattern = /{{#if\s+(.+?)}}([\s\S]*?){{\/if}}/g;
        
        return template.replace(ifPattern, (match, condition, content) => {
            try {
                // Évaluer la condition de manière sécurisée
                const result = this.evaluateCondition(condition, variables);
                return result ? content : '';
            } catch (error) {
                logger.warn(`Erreur évaluation condition: ${condition}`, error.message);
                return '';
            }
        });
    }

    /**
     * Évaluer une condition de template
     */
    evaluateCondition(condition, variables) {
        // Conditions simples supportées
        if (condition in variables) {
            return !!variables[condition];
        }

        // Conditions avec égalité : variable === 'value'
        const equalityMatch = condition.match(/^(.+?)\s*===\s*['"](.+?)['"]$/);
        if (equalityMatch) {
            const [, variable, value] = equalityMatch;
            return variables[variable] === value;
        }

        return false;
    }

    /**
     * Obtenir un prompt pour résumé
     */
    async getSummaryPrompt(content, options = {}) {
        const {
            maxLength = 200,
            minLength = 50,
            type = 'basic',
            language = 'fr'
        } = options;

        const templatePath = `summary/${type}.txt`;
        const template = await this.loadTemplate(templatePath);

        return this.renderTemplate(template, {
            content,
            maxLength,
            minLength,
            language
        });
    }

    /**
     * Obtenir un prompt pour extraction de mots-clés
     */
    async getKeywordsPrompt(content, options = {}) {
        const {
            count = 10,
            language = 'fr',
            includeNER = false,
            minLength = 3,
            excludeCommon = true
        } = options;

        const templatePath = 'keywords/extraction.txt';
        const template = await this.loadTemplate(templatePath);

        return this.renderTemplate(template, {
            content,
            count,
            language,
            includeNER,
            minLength,
            excludeCommon
        });
    }

    /**
     * Obtenir un prompt pour reformulation de titre
     */
    async getTitlePrompt(title, content, options = {}) {
        const {
            style = 'formal',
            language = 'fr',
            maxLength = 100,
            type = 'reformulation' // 'reformulation', 'archival', 'generation'
        } = options;

        // Choisir le template selon le type demandé
        let templatePath;
        switch (type) {
            case 'archival':
                templatePath = 'title/archival.txt';
                break;
            case 'generation':
                templatePath = 'title/generation.txt';
                break;
            default:
                templatePath = 'title/reformulation.txt';
        }

        const template = await this.loadTemplate(templatePath);

        return this.renderTemplate(template, {
            title,
            content,
            style,
            language,
            maxLength
        });
    }

    /**
     * Obtenir un prompt pour analyse
     */
    async getAnalysisPrompt(content, type = 'general', options = {}) {
        const {
            language = 'fr',
            depth = 'shallow'
        } = options;

        const templatePath = 'analysis/content.txt';
        const template = await this.loadTemplate(templatePath);

        return this.renderTemplate(template, {
            content,
            type,
            language,
            depth
        });
    }

    /**
     * Vider le cache des templates
     */
    clearCache() {
        this.templateCache.clear();
        logger.info('Cache des templates vidé');
    }

    /**
     * Obtenir les statistiques du cache
     */
    getCacheStats() {
        return {
            size: this.templateCache.size,
            maxSize: this.maxCacheSize,
            keys: Array.from(this.templateCache.keys())
        };
    }

    /**
     * Valider qu'un template existe
     */
    async validateTemplate(templatePath) {
        try {
            const fullPath = path.join(this.templatesDir, templatePath);
            await fs.access(fullPath);
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Lister tous les templates disponibles
     */
    async listTemplates() {
        try {
            const categories = await fs.readdir(this.templatesDir);
            const templates = {};

            for (const category of categories) {
                const categoryPath = path.join(this.templatesDir, category);
                const stat = await fs.stat(categoryPath);
                
                if (stat.isDirectory()) {
                    const files = await fs.readdir(categoryPath);
                    templates[category] = files.filter(file => file.endsWith('.txt'));
                }
            }

            return templates;
        } catch (error) {
            logger.error('Erreur listage templates:', error.message);
            throw error;
        }
    }
}

module.exports = new PromptService();

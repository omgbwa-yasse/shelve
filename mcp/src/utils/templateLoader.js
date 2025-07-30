const fs = require('fs');
const path = require('path');
const { logger } = require('./logger');

/**
 * Charge les templates de prompts depuis le fichier JSON
 * @returns {Object} - Les templates chargés
 */
function loadTemplates() {
  try {
    const templatesPath = path.join(__dirname, '../../templates/prompts.json');
    const templatesContent = fs.readFileSync(templatesPath, 'utf8');
    return JSON.parse(templatesContent);
  } catch (error) {
    logger.error(`Erreur lors du chargement des templates: ${error.message}`);
    // Retourner des templates par défaut en cas d'erreur
    return {
      summarize: {
        template: 'Résume ce texte en 3 paragraphes: {{content}}',
        instructions: 'Crée un résumé informatif.'
      },
      title: {
        template: 'Reformule ce titre de manière concise: {{title}}',
        instructions: 'Reformule ce titre.'
      },
      keywords: {
        template: 'Extrais les mots-clés de ce texte: {{content}}',
        instructions: 'Liste les mots-clés.'
      },
      analyze: {
        template: 'Analyse ce document: {{content}}',
        instructions: 'Analyse ce texte.'
      }
    };
  }
}

/**
 * Construit un prompt à partir d'un template et des données
 * @param {string} templateName - Nom du template à utiliser
 * @param {Object} data - Données à injecter dans le template
 */
function buildPrompt(templateName, data) {
  const templates = loadTemplates();

  if (!templates[templateName]) {
    logger.error(`Template "${templateName}" non trouvé`);
    throw new Error(`Template "${templateName}" non trouvé`);
  }

  let promptTemplate = templates[templateName].template;

  // Remplacer toutes les variables dans le template
  Object.entries(data).forEach(([key, value]) => {
    const safeValue = value || ''; // Gérer les valeurs null ou undefined
    promptTemplate = promptTemplate.replace(new RegExp(`{{${key}}}`, 'g'), safeValue);
  });

  return promptTemplate;
}

module.exports = {
  loadTemplates,
  buildPrompt
};

// Interfaces for MCP services
// Ces interfaces définissent les contrats que les services doivent implémenter

/**
 * Interface pour le service d'IA
 * @interface
 */
const AiServiceInterface = {
  /**
   * Génère une réponse à partir d'un prompt via un modèle d'IA
   * @param {string} prompt - Le prompt à envoyer
   * @param {string} modelName - Le nom du modèle à utiliser
   * @param {Object} options - Options pour la génération
   * @returns {Promise<Object>} La réponse de l'API
   */
  generate: async (prompt, modelName, options) => {}
};

/**
 * Interface pour le service de gestion des records
 * @interface
 */
const RecordsServiceInterface = {
  /**
   * Formater le titre d'un record selon le format: objet, action administrative : typologie documentaire (date?)
   * @param {number} recordId - L'ID du record
   * @param {string} title - Le titre actuel du record
   * @param {Object} context - Informations contextuelles pour aider au formatage
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<Object>} Le résultat du formatage
   */
  formatRecordTitle: async (recordId, title, context, modelName) => {},

  /**
   * Génère un résumé pour un record
   * @param {number} recordId - L'ID du record
   * @param {Object} recordData - Les données du record
   * @param {number} maxLength - Longueur maximale du résumé
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<Object>} Le résultat de la génération du résumé
   */
  generateSummary: async (recordId, recordData, maxLength, modelName) => {},

  /**
   * Extrait des mots-clés catégorisés à partir d'un record
   * @param {number} recordId - L'ID du record
   * @param {Object} recordData - Les données du record
   * @param {string} modelName - Le nom du modèle à utiliser
   * @param {boolean} autoAssign - Indique si les termes doivent être automatiquement assignés
   * @returns {Promise<Object>} Les mots-clés extraits et catégorisés
   */
  extractCategorizedKeywords: async (recordId, recordData, modelName, autoAssign) => {}
};

/**
 * Interface pour le service de gestion des termes
 * @interface
 */
const TermsServiceInterface = {
  /**
   * Recherche des termes dans le thésaurus
   * @param {Array<string>} keywords - Liste de mots-clés à rechercher
   * @returns {Promise<Object>} Les termes correspondants du thésaurus
   */
  searchTermsInThesaurus: async (keywords) => {},

  /**
   * Recherche des termes par catégorie
   * @param {string} term - Terme à rechercher
   * @param {string} category - Catégorie dans laquelle rechercher
   * @param {number} limit - Nombre maximum de résultats
   * @returns {Promise<Array>} Les termes correspondants
   */
  searchTermsByCategory: async (term, category, limit) => {},

  /**
   * Assigne des termes à un record
   * @param {number} recordId - ID du record
   * @param {Object} categorizedTerms - Termes catégorisés à assigner
   * @returns {Promise<Object>} Résultat de l'opération d'assignation
   */
  assignTermsToRecord: async (recordId, categorizedTerms) => {}
};

module.exports = {
  AiServiceInterface,
  RecordsServiceInterface,
  TermsServiceInterface
};

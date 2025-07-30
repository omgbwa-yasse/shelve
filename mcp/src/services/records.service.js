// Service de traitement des records (formatage de titres, résumés, mots-clés)
const aiService = require('./ai.service');
const termsService = require('./terms.service');
const { config } = require('../config');
const { RecordsServiceInterface } = require('../schemas/validation');

/**
 * Service de traitement des records
 * @implements {RecordsServiceInterface}
 */
class RecordsService {
  constructor() {
    this.defaultModel = config.ollama.defaultModel;
  }

  /**
   * Formater le titre d'un record selon le format: objet, action administrative : typologie documentaire (date?)
   * @param {number} recordId - L'ID du record
   * @param {string} title - Le titre actuel du record
   * @param {Object} context - Informations contextuelles pour aider au formatage
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<string>} Le titre formaté
   */
  async formatRecordTitle(recordId, title, context = {}, modelName = this.defaultModel) {
    try {
      // Vérifier que le titre n'est pas vide
      if (!title || title.trim() === '') {
        throw new Error('Le titre à formater ne peut pas être vide');
      }

      // Construire le prompt pour le formatage du titre
      const prompt = `
Formatez le titre suivant selon le modèle "objet, action administrative : typologie documentaire (date)".

Titre original : "${title}"

${context && context.administrative_action ? `Action administrative suggérée : ${context.administrative_action}` : ''}
${context && context.document_type ? `Type de document suggéré : ${context.document_type}` : ''}
${(context && (context.date_start || context.date_end)) ? `Période : ${context.date_start || ''} - ${context.date_end || ''}` : ''}

Répondez uniquement avec le titre reformaté, sans guillemets ni commentaires.
`;

      const result = await aiService.generate(
        prompt,
        modelName,
        { temperature: 0.2 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec du formatage du titre');
      }

      // Nettoyer le titre formaté
      let formattedTitle = result.content.trim();
      if (formattedTitle.startsWith('"') && formattedTitle.endsWith('"')) {
        formattedTitle = formattedTitle.slice(1, -1).trim();
      }

      // Enregistrer le titre formaté dans Laravel si l'ID du record est fourni
      if (recordId) {
        await termsService.updateRecord(recordId, { name: formattedTitle });
      }

      return formattedTitle;
    } catch (error) {
      console.error(`Erreur lors du formatage du titre pour le record ${recordId}:`, error);
      throw error;
    }
  }

  /**
   * Générer un résumé pour un record
   * @param {number} recordId - L'ID du record
   * @param {Object} recordData - Les données du record
   * @param {number} maxLength - Longueur maximale du résumé en mots
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<string>} Le résumé généré
   */
  async generateSummary(recordId, recordData, maxLength = 150, modelName = this.defaultModel) {
    try {
      // Combiner les différentes parties du record pour générer un résumé cohérent
      const textToSummarize = [
        recordData.content,
        recordData.biographical_history,
        recordData.archival_history,
        recordData.description,
        recordData.note
      ].filter(Boolean).join('\n\n');

      // Vérifier qu'il y a du contenu à résumer
      if (!textToSummarize || textToSummarize.trim() === '') {
        throw new Error('Aucun contenu à résumer pour ce record');
      }

      // Construire le prompt pour la génération du résumé
      const prompt = `
Résumez le texte suivant en ${maxLength} mots maximum. Le résumé doit être concis mais complet,
en capturant les informations essentielles. Écrivez à la voix active et au temps présent.

Titre du document : "${recordData.name}"

Texte à résumer :
${textToSummarize}

Répondez uniquement avec le résumé, sans commentaires additionnels.
`;

      const result = await aiService.generate(
        prompt,
        modelName,
        { temperature: 0.3 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de génération du résumé');
      }

      const summary = result.content.trim();

      // Enregistrer le résumé dans Laravel si l'ID du record est fourni
      if (recordId) {
        await termsService.updateRecord(recordId, { description: summary });
      }

      return summary;
    } catch (error) {
      console.error(`Erreur lors de la génération du résumé pour le record ${recordId}:`, error);
      throw error;
    }
  }

  /**
   * Extrait des mots-clés catégorisés (géographiques, thématiques, typologiques) à partir d'un record
   * @param {number} recordId - L'ID du record
   * @param {Object} recordData - Les données du record
   * @param {string} modelName - Le nom du modèle à utiliser
   * @param {boolean} autoAssign - Si true, assigne automatiquement les termes correspondants dans le thésaurus
   * @returns {Promise<Object>} Les mots-clés catégorisés et les termes correspondants du thésaurus
   */
  async extractCategorizedKeywords(recordId, recordData, modelName = this.defaultModel, autoAssign = false) {
    try {
      // Combiner les différentes parties du record pour l'extraction de mots-clés
      const contentToAnalyze = [
        recordData.name,
        recordData.content,
        recordData.biographical_history,
        recordData.note
      ].filter(Boolean).join('\n\n');

      // Vérifier que le contenu n'est pas vide
      if (!contentToAnalyze || contentToAnalyze.trim() === '') {
        throw new Error('Le contenu à analyser ne peut pas être vide');
      }

      // Générer des mots-clés catégorisés à partir du contenu
      const prompt = `
Analysez le texte suivant et extrayez-en des mots-clés organisés en trois catégories :

1. GEOGRAPHIQUE : lieux, pays, régions, villes, noms géographiques
2. THEMATIQUE : sujets, concepts, disciplines, domaines d'activité
3. TYPOLOGIE : types de documents, formats, genres de textes

Pour chaque catégorie, identifiez jusqu'à 5 termes pertinents.
Répondez UNIQUEMENT au format suivant, sans commentaires additionnels :

GEOGRAPHIQUE:
- [terme1]
- [terme2]
- [terme3]

THEMATIQUE:
- [terme1]
- [terme2]
- [terme3]

TYPOLOGIE:
- [terme1]
- [terme2]
- [terme3]

Texte à analyser :
${contentToAnalyze}
`;

      const result = await aiService.generate(
        prompt,
        modelName,
        { temperature: 0.2 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de génération des mots-clés');
      }

      const response = result.content;

      // Traitement des catégories et des termes extraits
      const keywords = {
        geographic: [],
        thematic: [],
        typologic: []
      };

      // Extraction des termes par catégorie
      const geographicMatch = response.match(/GEOGRAPHIQUE:([^T]+)/i);
      const thematicMatch = response.match(/THEMATIQUE:([^T]+)/i);
      const typologicMatch = response.match(/TYPOLOGIE:([^G]+)/i);

      if (geographicMatch) {
        keywords.geographic = this._extractTermsFromText(geographicMatch[1]);
      }

      if (thematicMatch) {
        keywords.thematic = this._extractTermsFromText(thematicMatch[1]);
      }

      if (typologicMatch) {
        keywords.typologic = this._extractTermsFromText(typologicMatch[1]);
      }

      // Rechercher les correspondances dans le thésaurus
      const thesaurusMatches = await this._findThesaurusMatches(keywords);

      // Si autoAssign est activé, assigner automatiquement les termes correspondants
      if (autoAssign && recordId && thesaurusMatches.length > 0) {
        await this.assignTerms(recordId, thesaurusMatches);
      }

      return { keywords, thesaurusMatches };
    } catch (error) {
      console.error(`Erreur lors de l'extraction des mots-clés pour le record ${recordId}:`, error);
      throw error;
    }
  }

  /**
   * Recherche des correspondances dans le thésaurus pour un contenu donné
   * @param {number} recordId - L'ID du record
   * @param {string} content - Le contenu à analyser
   * @param {string} modelName - Le nom du modèle à utiliser
   * @param {number} maxTerms - Nombre maximum de termes à retourner
   * @returns {Promise<Array>} Les correspondances trouvées dans le thésaurus
   */
  async searchThesaurus(recordId, content, modelName = this.defaultModel, maxTerms = 10) {
    try {
      // Vérifier que le contenu n'est pas vide
      if (!content || content.trim() === '') {
        throw new Error('Le contenu à analyser ne peut pas être vide');
      }

      // Extraire les mots-clés potentiels du contenu
      const prompt = `
Extrayez du texte suivant une liste de ${maxTerms} mots-clés ou expressions qui pourraient servir
de descripteurs dans un thésaurus documentaire. Choisissez des termes précis et significatifs.

Texte à analyser :
${content}

Répondez uniquement avec la liste des mots-clés séparés par des virgules, sans numérotation ni commentaires.
`;

      const result = await aiService.generate(
        prompt,
        modelName,
        { temperature: 0.3 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de l\'extraction des termes');
      }

      // Extraire les termes de la réponse
      const extractedTerms = result.content
        .split(',')
        .map(term => term.trim())
        .filter(Boolean);

      // Rechercher les termes dans le thésaurus via l'API Laravel
      const thesaurusMatches = await termsService.searchThesaurus(extractedTerms);

      return thesaurusMatches;
    } catch (error) {
      console.error(`Erreur lors de la recherche dans le thésaurus pour le record ${recordId}:`, error);
      throw error;
    }
  }

  /**
   * Assigner des termes à un record
   * @param {number} recordId - L'ID du record
   * @param {Array} terms - Les termes à assigner
   * @returns {Promise<Object>} Résultat de l'assignation
   */
  async assignTerms(recordId, terms) {
    try {
      const result = await termsService.assignTerms(recordId, terms);
      return { assigned: result.assigned || [] };
    } catch (error) {
      console.error(`Erreur lors de l'assignation des termes au record ${recordId}:`, error);
      throw error;
    }
  }

  /**
   * Extraire les termes d'un texte formaté avec des tirets
   * @private
   * @param {string} text - Texte à traiter
   * @returns {Array} Liste des termes extraits
   */
  _extractTermsFromText(text) {
    return text
      .split('\n')
      .map(line => {
        // Nettoyer la ligne et extraire le terme
        const match = line.match(/[-*]\s*(.+)/);
        return match ? match[1].trim() : null;
      })
      .filter(Boolean);
  }

  /**
   * Trouver les correspondances dans le thésaurus pour les mots-clés extraits
   * @private
   * @param {Object} keywords - Les mots-clés par catégorie
   * @returns {Promise<Array>} Les correspondances trouvées dans le thésaurus
   */
  async _findThesaurusMatches(keywords) {
    // Combiner tous les mots-clés en une seule liste pour la recherche
    const allKeywords = [
      ...keywords.geographic.map(term => ({ term, category: 'geographic' })),
      ...keywords.thematic.map(term => ({ term, category: 'thematic' })),
      ...keywords.typologic.map(term => ({ term, category: 'typologic' }))
    ];

    // Rechercher chaque terme dans le thésaurus
    const matches = [];
    for (const { term, category } of allKeywords) {
      const termMatches = await termsService.searchThesaurus([term]);
      if (termMatches && termMatches.length > 0) {
        matches.push({
          ...termMatches[0],
          originalCategory: category
        });
      }
    }

    return matches;
  }

  /**
   * Vérifier l'état d'Ollama
   * @returns {Promise<Object>} État d'Ollama
   */
  async checkOllama() {
    return await aiService.checkStatus();
  }
}

module.exports = new RecordsService();

const ollamaService = require('./ollamaService');
const { logger } = require('../utils/logger');
const { buildPrompt } = require('../utils/templateLoader');
const { getDb } = require('../utils/database');

class RecordService {
  constructor() {
    this.modelSummary = ollamaService.defaultModel;
    this.modelKeywords = ollamaService.defaultModel;
    this.modelAnalysis = ollamaService.defaultModel;
  }

  /**
   * Initialise le service avec les paramètres spécifiques
   */
  async initialize() {
    try {
      await ollamaService.initialize();

      // Utiliser les mêmes modèles par défaut qu'ollamaService si non spécifiés
      this.modelSummary = ollamaService.defaultModel;
      this.modelKeywords = ollamaService.defaultModel;
      this.modelAnalysis = ollamaService.defaultModel;

      logger.info(`RecordService initialisé avec les modèles par défaut`);
    } catch (error) {
      logger.error(`Erreur lors de l'initialisation de RecordService: ${error.message}`);
    }
  }

  /**
   * Récupère un record complet depuis la base de données avec toutes les relations pertinentes
   * @param {number} recordId - ID du record à récupérer
   */
  async getRecordById(recordId) {
    try {
      const db = getDb();
      const record = await db('records')
        .select(
          'records.*',
          'record_levels.name as level_name',
          'record_statuses.name as status_name',
          'record_supports.name as support_name'
        )
        .leftJoin('record_levels', 'records.level_id', 'record_levels.id')
        .leftJoin('record_statuses', 'records.status_id', 'record_statuses.id')
        .leftJoin('record_supports', 'records.support_id', 'record_supports.id')
        .where('records.id', recordId)
        .first();

      if (!record) {
        throw new Error(`Record avec ID ${recordId} non trouvé`);
      }

      return record;
    } catch (error) {
      logger.error(`Erreur lors de la récupération du record ${recordId}: ${error.message}`);
      throw error;
    }
  }

  /**
   * Reformule le titre d'un record
   * @param {number} recordId - ID du record
   */
  async reformatTitle(recordId) {
    try {
      const record = await this.getRecordById(recordId);

      // Préparer les données pour le template - uniquement le champ name
      const templateData = {
        name: record.name
      };

      // Construire le prompt pour la reformulation du titre
      const prompt = buildPrompt('recordTitle', templateData);

      // Envoyer la requête à Ollama
      const newTitle = await ollamaService.generateCompletion(prompt, this.modelSummary);

      return {
        originalTitle: record.name,
        newTitle: newTitle.trim(),
        recordId: recordId
      };
    } catch (error) {
      logger.error(`Erreur lors de la reformulation du titre pour le record ${recordId}: ${error.message}`);
      throw error;
    }
  }

  /**
   * Génère un résumé pour un record
   * @param {number} recordId - ID du record
   */
  async generateSummary(recordId) {
    try {
      const record = await this.getRecordById(recordId);

      // Préparer les données pour le template - uniquement le champ content
      const templateData = {
        content: this._truncateContent(record.content, 6000)
      };

      // Construire le prompt pour la génération de résumé
      const prompt = buildPrompt('recordSummary', templateData);

      // Envoyer la requête à Ollama
      const summary = await ollamaService.generateCompletion(prompt, this.modelSummary);

      return {
        recordId: recordId,
        recordName: record.name,
        summary: summary.trim()
      };
    } catch (error) {
      logger.error(`Erreur lors de la génération du résumé pour le record ${recordId}: ${error.message}`);
      throw error;
    }
  }

  /**
   * Extrait des mots-clés d'un record
   * @param {number} recordId - ID du record
   */
  async extractKeywords(recordId) {
    try {
      const record = await this.getRecordById(recordId);

      // Préparer les données pour le template
      const templateData = {
        name: record.name,
        level: record.level_name || '',
        content: this._truncateContent(record.content, 3000)
      };

      // Construire le prompt pour l'extraction de mots-clés
      const prompt = buildPrompt('recordKeywords', templateData);

      // Envoyer la requête à Ollama
      const keywordsText = await ollamaService.generateCompletion(prompt, this.modelKeywords);

      // Transformer la chaîne de mots-clés en tableau
      const keywords = keywordsText
        .split(',')
        .map(keyword => keyword.trim())
        .filter(keyword => keyword.length > 0);

      return {
        recordId: recordId,
        recordName: record.name,
        keywords: keywords
      };
    } catch (error) {
      logger.error(`Erreur lors de l'extraction des mots-clés pour le record ${recordId}: ${error.message}`);
      throw error;
    }
  }

  /**
   * Analyse le contenu d'un record
   * @param {number} recordId - ID du record
   */
  async analyzeContent(recordId) {
    try {
      const record = await this.getRecordById(recordId);

      // Préparer les données pour le template
      const templateData = {
        name: record.name,
        level: record.level_name || '',
        date: this._formatRecordDate(record),
        content: this._truncateContent(record.content, 4000),
        biographical_history: this._truncateContent(record.biographical_history, 500),
        archival_history: this._truncateContent(record.archival_history, 500)
      };

      // Construire le prompt pour l'analyse
      const prompt = buildPrompt('recordAnalysis', templateData);

      // Envoyer la requête à Ollama
      const analysis = await ollamaService.generateCompletion(prompt, this.modelAnalysis);

      return {
        recordId: recordId,
        recordName: record.name,
        analysis: analysis.trim()
      };
    } catch (error) {
      logger.error(`Erreur lors de l'analyse du contenu pour le record ${recordId}: ${error.message}`);
      throw error;
    }
  }

  /**
   * Formate la date du record selon le format approprié
   * @private
   */
  _formatRecordDate(record) {
    if (record.date_exact) {
      return record.date_exact;
    } else if (record.date_start && record.date_end) {
      return `${record.date_start} - ${record.date_end}`;
    } else if (record.date_start) {
      return `À partir de ${record.date_start}`;
    } else if (record.date_end) {
      return `Jusqu'à ${record.date_end}`;
    }
    return '';
  }

  /**
   * Tronque le contenu pour éviter les dépassements de tokens
   * @private
   */
  _truncateContent(content, maxLength = 1500) {
    if (!content) return '';

    if (content.length <= maxLength) {
      return content;
    }

    return content.substring(0, maxLength) + '...';
  }
}

// Exporter une instance unique
const recordService = new RecordService();

module.exports = recordService;

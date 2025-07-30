const axios = require('axios');
const { logger } = require('../utils/logger');
const { getSettings } = require('../utils/database');

class OllamaService {
  constructor() {
    this.baseUrl = process.env.OLLAMA_BASE_URL || 'http://localhost:11434';
    this.timeout = process.env.OLLAMA_TIMEOUT || 120000;
    this.defaultModel = 'gemma3:4b'; // Sera écrasé par la valeur de la base de données
  }

  /**
   * Initialise le service avec les paramètres de la base de données
   */
  async initialize() {
    try {
      // Récupérer les paramètres Ollama depuis la base de données
      const ollamaBaseUrl = await getSettings('ollama_base_url');
      const defaultModel = await getSettings('ai_default_model');
      const timeout = await getSettings('ai_request_timeout');

      if (ollamaBaseUrl) {
        this.baseUrl = JSON.parse(ollamaBaseUrl.default_value);
      }

      if (defaultModel) {
        this.defaultModel = JSON.parse(defaultModel.default_value);
      }

      if (timeout) {
        this.timeout = JSON.parse(timeout.default_value) * 1000; // Convertir en millisecondes
      }

      logger.info(`OllamaService initialisé avec le modèle par défaut: ${this.defaultModel}`);
      logger.info(`URL de base Ollama: ${this.baseUrl}`);
    } catch (error) {
      logger.error(`Erreur lors de l'initialisation d'OllamaService: ${error.message}`);
      // Utiliser les valeurs par défaut
    }
  }

  /**
   * Crée l'instance Axios avec la configuration appropriée
   */
  createAxiosInstance() {
    return axios.create({
      baseURL: this.baseUrl,
      timeout: this.timeout,
      headers: {
        'Content-Type': 'application/json'
      }
    });
  }

  /**
   * Liste tous les modèles disponibles
   */
  async listModels() {
    try {
      const client = this.createAxiosInstance();
      const response = await client.get('/api/tags');
      return response.data.models || [];
    } catch (error) {
      logger.error(`Erreur lors de la récupération des modèles: ${error.message}`);
      throw new Error(`Impossible de récupérer les modèles: ${error.message}`);
    }
  }

  /**
   * Envoie une requête de complétion au modèle
   * @param {string} prompt - Le texte d'entrée pour le modèle
   * @param {string} model - Le modèle à utiliser (optionnel)
   * @param {Object} options - Options supplémentaires pour la requête
   */
  async generateCompletion(prompt, model = null, options = {}) {
    const modelToUse = model || this.defaultModel;

    try {
      const client = this.createAxiosInstance();
      const response = await client.post('/api/generate', {
        model: modelToUse,
        prompt,
        stream: false,
        options: {
          temperature: options.temperature || 0.7,
          top_p: options.top_p || 0.9,
          top_k: options.top_k || 40,
          repeat_penalty: options.repeat_penalty || 1.1,
          max_tokens: options.max_tokens || 2048
        }
      });

      return response.data.response;
    } catch (error) {
      logger.error(`Erreur lors de la génération avec le modèle ${modelToUse}: ${error.message}`);
      throw new Error(`Échec de la génération: ${error.message}`);
    }
  }

  /**
   * Récupère le modèle par défaut
   */
  getDefaultModel() {
    return this.defaultModel;
  }
}

// Exporter une instance unique
const ollamaService = new OllamaService();

module.exports = ollamaService;

// Service d'interaction avec les modèles d'IA
const axios = require('axios');
const config = require('../config');
const { AiServiceInterface } = require('../schemas/validation');

/**
 * Service d'interaction avec les modèles d'IA
 * @implements {AiServiceInterface}
 */
class AiService {
  constructor() {
    this.baseUrl = config.ollama.baseUrl;
    this.defaultModel = config.ollama.defaultModel;
  }

  /**
   * Génère une réponse à partir d'un prompt via Ollama
   * @param {string} prompt - Le prompt à envoyer
   * @param {string} modelName - Le nom du modèle à utiliser
   * @param {Object} options - Options pour la génération
   * @returns {Promise<Object>} La réponse de l'API
   */
  async generate(prompt, modelName = this.defaultModel, options = {}) {
    try {
      const defaultOptions = {
        temperature: options.temperature || config.ollama.options.defaultTemperature,
        top_p: options.top_p || 0.9,
        top_k: options.top_k || 40,
      };

      const response = await axios.post(`${this.baseUrl}/api/generate`, {
        model: modelName,
        prompt,
        stream: false,
        options: defaultOptions
      });

      if (!response.data || !response.data.response) {
        throw new Error('Réponse invalide d\'Ollama');
      }

      return {
        success: true,
        content: response.data.response,
        stats: {
          totalDuration: response.data.total_duration,
          evalCount: response.data.eval_count,
        }
      };
    } catch (error) {
      console.error('Erreur lors de la génération via Ollama:', error.message);
      return {
        success: false,
        error: error.message,
        content: null
      };
    }
  }

  /**
   * Vérifie la disponibilité d'Ollama et récupère la liste des modèles disponibles
   * @returns {Promise<Object>} État d'Ollama et liste des modèles
   */
  async checkStatus() {
    try {
      // Vérifier que l'API Ollama répond
      const response = await axios.get(`${this.baseUrl}/api/tags`, {
        timeout: 5000
      });

      if (!response.data || !response.data.models) {
        return {
          available: false,
          models: []
        };
      }

      // Extraire les noms des modèles disponibles
      const models = response.data.models.map(model => model.name);

      return {
        available: true,
        models
      };
    } catch (error) {
      console.error('Erreur lors de la vérification d\'Ollama:', error.message);
      return {
        available: false,
        error: error.message
      };
    }
  }
}

module.exports = new AiService();

// Service pour interagir avec l'API Ollama
const axios = require('axios');
const config = require('../config');

class OllamaService {
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
   * Vérifie la disponibilité d'Ollama et retourne les modèles disponibles
   * @returns {Promise<Object>} Les informations sur les modèles disponibles
   */
  async checkStatus() {
    try {
      const response = await axios.get(`${this.baseUrl}/api/tags`);
      return {
        status: 'ok',
        models: response.data.models || [],
        count: response.data.models?.length || 0
      };
    } catch (error) {
      console.error('Erreur lors de la vérification d\'Ollama:', error.message);
      return {
        status: 'error',
        message: `Impossible de se connecter à Ollama: ${error.message}`,
        details: error.response?.data || null
      };
    }
  }
}

module.exports = new OllamaService();

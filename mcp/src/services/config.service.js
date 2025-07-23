// Service de configuration qui récupère les paramètres depuis la base de données Laravel
const axios = require('axios');
const config = require('../config');

/**
 * Service de gestion de la configuration dynamique
 */
class ConfigService {
  constructor() {
    this.cache = new Map();
    this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
    this.laravelApiUrl = config.laravel.apiUrl;
    this.apiToken = config.laravel.apiToken;
  }

  /**
   * Récupère un paramètre depuis la base de données Laravel
   * @param {string} settingName - Nom du paramètre
   * @param {any} defaultValue - Valeur par défaut si le paramètre n'existe pas
   * @returns {Promise<any>} Valeur du paramètre
   */
  async getSetting(settingName, defaultValue = null) {
    try {
      // Vérifier le cache
      const cacheKey = `setting_${settingName}`;
      const cached = this.cache.get(cacheKey);
      
      if (cached && (Date.now() - cached.timestamp) < this.cacheTimeout) {
        return cached.value;
      }

      // Faire la requête à l'API Laravel
      const response = await axios.get(`${this.laravelApiUrl}/settings/${settingName}`, {
        headers: {
          'Authorization': `Bearer ${this.apiToken}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        timeout: config.laravel.timeout
      });

      let value = defaultValue;
      if (response.data && response.data.value !== undefined) {
        value = response.data.value;
      }

      // Mettre en cache
      this.cache.set(cacheKey, {
        value,
        timestamp: Date.now()
      });

      return value;
    } catch (error) {
      console.warn(`Erreur lors de la récupération du paramètre ${settingName}:`, error.message);
      return defaultValue;
    }
  }

  /**
   * Récupère plusieurs paramètres en une seule requête
   * @param {string[]} settingNames - Liste des noms de paramètres
   * @returns {Promise<Object>} Objet avec les paramètres
   */
  async getSettings(settingNames) {
    try {
      const response = await axios.post(`${this.laravelApiUrl}/settings/batch`, {
        settings: settingNames
      }, {
        headers: {
          'Authorization': `Bearer ${this.apiToken}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        timeout: config.laravel.timeout
      });

      const settings = {};
      if (response.data && response.data.settings) {
        for (const [name, value] of Object.entries(response.data.settings)) {
          settings[name] = value;
          // Mettre en cache
          this.cache.set(`setting_${name}`, {
            value,
            timestamp: Date.now()
          });
        }
      }

      return settings;
    } catch (error) {
      console.warn('Erreur lors de la récupération des paramètres:', error.message);
      return {};
    }
  }

  /**
   * Vide le cache des paramètres
   */
  clearCache() {
    this.cache.clear();
  }

  /**
   * Récupère la configuration complète des providers d'IA
   * @returns {Promise<Object>} Configuration des providers
   */
  async getAiProvidersConfig() {
    const settingNames = [
      'ai_default_provider',
      'ai_default_model',
      'ai_request_timeout',
      'ollama_base_url',
      'ollama_enabled',
      'lmstudio_base_url',
      'lmstudio_enabled',
      'lmstudio_api_key',
      'anythingllm_base_url',
      'anythingllm_enabled',
      'anythingllm_api_key',
      'openai_enabled',
      'openai_api_key',
      'openai_organization',
      'model_summary',
      'model_keywords',
      'model_analysis'
    ];

    const settings = await this.getSettings(settingNames);

    return {
      defaultProvider: settings.ai_default_provider || 'ollama',
      defaultModel: settings.ai_default_model || 'llama3',
      requestTimeout: (settings.ai_request_timeout || 120) * 1000,
      providers: {
        ollama: {
          enabled: settings.ollama_enabled !== false,
          baseUrl: settings.ollama_base_url || 'http://localhost:11434',
          type: 'ollama'
        },
        lmstudio: {
          enabled: settings.lmstudio_enabled === true,
          baseUrl: settings.lmstudio_base_url || 'http://localhost:1234',
          apiKey: settings.lmstudio_api_key || '',
          type: 'openai-compatible'
        },
        anythingllm: {
          enabled: settings.anythingllm_enabled === true,
          baseUrl: settings.anythingllm_base_url || 'http://localhost:3001',
          apiKey: settings.anythingllm_api_key || '',
          type: 'openai-compatible'
        },
        openai: {
          enabled: settings.openai_enabled === true,
          baseUrl: 'https://api.openai.com/v1',
          apiKey: settings.openai_api_key || '',
          organization: settings.openai_organization || '',
          type: 'openai'
        }
      },
      models: {
        summary: settings.model_summary || 'llama3',
        keywords: settings.model_keywords || 'llama3',
        analysis: settings.model_analysis || 'llama3'
      }
    };
  }
}

module.exports = new ConfigService();

// Service d'IA multi-providers compatible avec l'API OpenAI
const axios = require('axios');
const configService = require('./config.service');
const { AiServiceInterface } = require('../schemas/validation');

/**
 * Service d'interaction avec plusieurs providers d'IA
 * Supporte: Ollama, LM Studio, AnythingLLM, OpenAI
 * @implements {AiServiceInterface}
 */
class MultiProviderAiService {
  constructor() {
    this.providersConfig = null;
    this.configCache = null;
    this.configCacheTime = 0;
    this.configCacheDuration = 5 * 60 * 1000; // 5 minutes
  }

  /**
   * Récupère la configuration des providers (avec cache)
   * @returns {Promise<Object>} Configuration des providers
   */
  async getProvidersConfig() {
    const now = Date.now();
    if (this.configCache && (now - this.configCacheTime) < this.configCacheDuration) {
      return this.configCache;
    }

    this.configCache = await configService.getAiProvidersConfig();
    this.configCacheTime = now;
    return this.configCache;
  }

  /**
   * Obtient le provider par défaut ou un provider spécifique
   * @param {string} providerName - Nom du provider (optionnel)
   * @returns {Promise<Object>} Configuration du provider
   */
  async getProvider(providerName = null) {
    const config = await this.getProvidersConfig();

    const targetProvider = providerName || config.defaultProvider;
    const provider = config.providers[targetProvider];

    if (!provider) {
      throw new Error(`Provider ${targetProvider} non trouvé`);
    }

    if (!provider.enabled) {
      throw new Error(`Provider ${targetProvider} est désactivé`);
    }

    return {
      name: targetProvider,
      ...provider,
      requestTimeout: config.requestTimeout
    };
  }

  /**
   * Prépare les headers pour une requête selon le type de provider
   * @param {Object} provider - Configuration du provider
   * @returns {Object} Headers pour la requête
   */
  prepareHeaders(provider) {
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };

    if (provider.type === 'openai' || provider.type === 'openai-compatible') {
      if (provider.apiKey) {
        headers['Authorization'] = `Bearer ${provider.apiKey}`;
      }
      if (provider.organization) {
        headers['OpenAI-Organization'] = provider.organization;
      }
    }

    return headers;
  }

  /**
   * Prépare le payload pour une requête selon le type de provider
   * @param {Object} provider - Configuration du provider
   * @param {string} prompt - Le prompt à envoyer
   * @param {string} model - Le modèle à utiliser
   * @param {Object} options - Options pour la génération
   * @returns {Object} Payload pour la requête
   */
  preparePayload(provider, prompt, model, options = {}) {
    const defaultOptions = {
      temperature: options.temperature || 0.3,
      top_p: options.top_p || 0.9,
      max_tokens: options.max_tokens || 2000,
    };

    if (provider.type === 'ollama') {
      return {
        model,
        prompt,
        stream: false,
        options: {
          temperature: defaultOptions.temperature,
          top_p: defaultOptions.top_p,
          top_k: options.top_k || 40,
        }
      };
    } else {
      // Format OpenAI-compatible
      return {
        model,
        messages: [
          {
            role: 'user',
            content: prompt
          }
        ],
        temperature: defaultOptions.temperature,
        top_p: defaultOptions.top_p,
        max_tokens: defaultOptions.max_tokens,
        stream: false
      };
    }
  }

  /**
   * Obtient l'URL pour la génération selon le type de provider
   * @param {Object} provider - Configuration du provider
   * @returns {string} URL pour la génération
   */
  getGenerateUrl(provider) {
    if (provider.type === 'ollama') {
      return `${provider.baseUrl}/api/generate`;
    } else {
      // Format OpenAI-compatible
      return `${provider.baseUrl}/v1/chat/completions`;
    }
  }

  /**
   * Extrait la réponse selon le type de provider
   * @param {Object} provider - Configuration du provider
   * @param {Object} responseData - Données de la réponse
   * @returns {Object} Réponse formatée
   */
  extractResponse(provider, responseData) {
    if (provider.type === 'ollama') {
      return {
        content: responseData.response || '',
        stats: {
          totalDuration: responseData.total_duration,
          evalCount: responseData.eval_count,
          promptEvalCount: responseData.prompt_eval_count,
        }
      };
    } else {
      // Format OpenAI-compatible
      const choice = responseData.choices && responseData.choices[0];
      return {
        content: choice?.message?.content || '',
        stats: {
          promptTokens: responseData.usage?.prompt_tokens || 0,
          completionTokens: responseData.usage?.completion_tokens || 0,
          totalTokens: responseData.usage?.total_tokens || 0,
        }
      };
    }
  }

  /**
   * Génère une réponse à partir d'un prompt
   * @param {string} prompt - Le prompt à envoyer
   * @param {string} modelName - Le nom du modèle à utiliser (optionnel)
   * @param {Object} options - Options pour la génération
   * @param {string} providerName - Nom du provider à utiliser (optionnel)
   * @returns {Promise<Object>} La réponse de l'API
   */
  async generate(prompt, modelName = null, options = {}, providerName = null) {
    try {
      const provider = await this.getProvider(providerName);
      const config = await this.getProvidersConfig();

      const model = modelName || config.defaultModel;
      const headers = this.prepareHeaders(provider);
      const payload = this.preparePayload(provider, prompt, model, options);
      const url = this.getGenerateUrl(provider);

      console.log(`Génération avec ${provider.name} - Modèle: ${model}`);

      const response = await axios.post(url, payload, {
        headers,
        timeout: provider.requestTimeout
      });

      if (!response.data) {
        throw new Error(`Réponse invalide du provider ${provider.name}`);
      }

      const extractedResponse = this.extractResponse(provider, response.data);

      return {
        success: true,
        provider: provider.name,
        model,
        content: extractedResponse.content,
        stats: extractedResponse.stats
      };
    } catch (error) {
      console.error(`Erreur lors de la génération via ${providerName || 'provider par défaut'}:`, error.message);
      return {
        success: false,
        error: error.message,
        provider: providerName,
        content: null
      };
    }
  }

  /**
   * Génère un résumé en utilisant le modèle configuré pour les résumés
   * @param {string} text - Le texte à résumer
   * @param {Object} options - Options pour la génération
   * @param {string} providerName - Nom du provider à utiliser (optionnel)
   * @returns {Promise<Object>} Le résumé généré
   */
  async generateSummary(text, options = {}, providerName = null) {
    const config = await this.getProvidersConfig();
    const prompt = `Veuillez créer un résumé concis et informatif du texte suivant. Le résumé doit capturer les points clés et les informations essentielles en 2-3 phrases maximum.

Texte à résumer:
${text}

Résumé:`;

    return this.generate(prompt, config.models.summary, {
      ...options,
      temperature: 0.2
    }, providerName);
  }

  /**
   * Extrait des mots-clés d'un texte
   * @param {string} text - Le texte à analyser
   * @param {number} maxKeywords - Nombre maximum de mots-clés
   * @param {string} providerName - Nom du provider à utiliser (optionnel)
   * @returns {Promise<Object>} Les mots-clés extraits
   */
  async extractKeywords(text, maxKeywords = 10, providerName = null) {
    const config = await this.getProvidersConfig();
    const prompt = `Analysez le texte suivant et extrayez ${maxKeywords} mots-clés ou expressions clés les plus pertinents. Répondez uniquement avec les mots-clés séparés par des virgules, sans numérotation ni formatage supplémentaire.

Texte à analyser:
${text}

Mots-clés:`;

    return this.generate(prompt, config.models.keywords, {
      temperature: 0.2,
      max_tokens: 200
    }, providerName);
  }

  /**
   * Vérifie la disponibilité des providers et récupère leurs modèles
   * @returns {Promise<Object>} État des providers et liste des modèles
   */
  async checkProvidersStatus() {
    const config = await this.getProvidersConfig();
    const results = {};

    for (const [name, providerConfig] of Object.entries(config.providers)) {
      if (!providerConfig.enabled) {
        results[name] = {
          available: false,
          reason: 'Désactivé',
          models: []
        };
        continue;
      }

      try {
        let modelsUrl;
        let headers = this.prepareHeaders({ ...providerConfig, name });

        if (providerConfig.type === 'ollama') {
          modelsUrl = `${providerConfig.baseUrl}/api/tags`;
        } else {
          modelsUrl = `${providerConfig.baseUrl}/v1/models`;
        }

        const response = await axios.get(modelsUrl, {
          headers,
          timeout: 5000
        });

        let models = [];
        if (providerConfig.type === 'ollama') {
          models = response.data.models?.map(model => model.name) || [];
        } else {
          models = response.data.data?.map(model => model.id) || [];
        }

        results[name] = {
          available: true,
          models,
          type: providerConfig.type
        };
      } catch (error) {
        results[name] = {
          available: false,
          error: error.message,
          models: []
        };
      }
    }

    return {
      defaultProvider: config.defaultProvider,
      providers: results
    };
  }

  /**
   * Vide le cache de configuration
   */
  clearConfigCache() {
    this.configCache = null;
    this.configCacheTime = 0;
    configService.clearCache();
  }
}

module.exports = new MultiProviderAiService();

module.exports = {
    // Configuration Ollama
    baseURL: process.env.OLLAMA_BASE_URL || 'http://localhost:11434',
    timeout: parseInt(process.env.OLLAMA_TIMEOUT) || 120000,

    // Modèles par défaut
    defaultModel: process.env.OLLAMA_DEFAULT_MODEL || 'gemma3:4b',
    models: {
        title: process.env.OLLAMA_TITLE_MODEL || 'gemma3:4b',
    },

    // Options par défaut pour les requêtes
    defaultOptions: {
        temperature: 0.7,
        top_p: 0.9,
        top_k: 40,
        num_predict: 1000
    },

    // Limites
    maxTokens: parseInt(process.env.OLLAMA_MAX_TOKENS) || 2000,
    maxConcurrentRequests: parseInt(process.env.OLLAMA_MAX_CONCURRENT) || 5,

    // Retry configuration
    retryAttempts: parseInt(process.env.OLLAMA_RETRY_ATTEMPTS) || 3,
    retryDelay: parseInt(process.env.OLLAMA_RETRY_DELAY) || 1000
};

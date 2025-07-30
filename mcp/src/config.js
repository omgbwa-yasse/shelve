// Configuration centralisée pour le serveur MCP
const dotenv = require('dotenv');

// Charger les variables d'environnement
dotenv.config({ path: '../.env' });

const config = {
  // Configuration serveur
  port: process.env.MCP_PORT || 3000,

  // Configuration Ollama
  ollama: {
    baseUrl: process.env.OLLAMA_BASE_URL || 'http://localhost:11434',
    defaultModel: process.env.OLLAMA_DEFAULT_MODEL || 'gemma3:4b',
    timeout: parseInt(process.env.OLLAMA_TIMEOUT) || 30000,
    maxRetries: parseInt(process.env.OLLAMA_MAX_RETRIES) || 3
  },

  // Configuration Laravel
  laravel: {
    apiUrl: process.env.LARAVEL_API_URL || 'http://localhost/shelves/api',
    apiToken: process.env.LARAVEL_API_TOKEN,
    timeout: parseInt(process.env.LARAVEL_TIMEOUT) || 10000
  },

  // Configuration sécurité
  security: {
    allowedOrigins: process.env.ALLOWED_ORIGINS?.split(',') || ['http://localhost', 'http://localhost:8000'],
    maxRequestSize: process.env.MAX_REQUEST_SIZE || '10mb',
    rateLimitWindow: parseInt(process.env.RATE_LIMIT_WINDOW) || 15 * 60 * 1000, // 15 minutes
    rateLimitMax: parseInt(process.env.RATE_LIMIT_MAX) || 100 // requests per window
  },

  // Modèles par défaut
  defaultModels: {
    summary: 'gemma3:4b',
    keywords: 'gemma3:4b',
    analysis: 'gemma3:4b'
  },

  // Paramètres IA
  ai: {
    temperature: {
      default: 0.7,
      precise: 0.2,
      creative: 0.9
    },
    maxTokens: parseInt(process.env.MAX_TOKENS) || 2048,
    maxTermsPerCategory: parseInt(process.env.MAX_TERMS_PER_CATEGORY) || 3
  }
};

// Validation de la configuration
function validateConfig() {
  const errors = [];

  if (!config.laravel.apiToken) {
    console.warn('⚠️  LARAVEL_API_TOKEN non configuré - certaines fonctionnalités seront limitées');
  }

  if (config.port < 1 || config.port > 65535) {
    errors.push('Port invalide');
  }

  if (errors.length > 0) {
    throw new Error(`Configuration invalide: ${errors.join(', ')}`);
  }

  return true;
}

module.exports = { config, validateConfig };

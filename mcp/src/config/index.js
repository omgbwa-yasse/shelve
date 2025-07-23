// Configuration centralisée pour le serveur MCP
const dotenv = require('dotenv');
const path = require('path');

// Charger les variables d'environnement
dotenv.config({ path: path.join(__dirname, '../../../.env') });

// Configuration centralisée
module.exports = {
  // Configuration du serveur
  server: {
    port: process.env.MCP_PORT || 3000,
    env: process.env.NODE_ENV || 'development',
  },

  // Configuration Ollama (fallback, remplacé par les paramètres DB)
  ollama: {
    baseUrl: process.env.OLLAMA_BASE_URL || 'http://localhost:11434',
    defaultModel: process.env.OLLAMA_DEFAULT_MODEL || 'llama3',
    timeout: parseInt(process.env.OLLAMA_TIMEOUT || '120000'),
    options: {
      defaultTemperature: 0.3,
      summaryTemperature: 0.2,
      analysisTemperature: 0.4,
      keywordsTemperature: 0.2,
    }
  },

  // Configuration API Laravel
  laravel: {
    apiUrl: process.env.LARAVEL_API_URL || 'http://localhost/shelves/api',
    apiToken: process.env.LARAVEL_API_TOKEN,
    timeout: parseInt(process.env.LARAVEL_API_TIMEOUT || '30000')
  },

  // Configuration par défaut des providers IA (utilisée si la DB n'est pas accessible)
  aiProviders: {
    defaultProvider: 'ollama',
    requestTimeout: 120000,
    providers: {
      ollama: {
        enabled: true,
        baseUrl: process.env.OLLAMA_BASE_URL || 'http://localhost:11434',
        type: 'ollama'
      },
      lmstudio: {
        enabled: false,
        baseUrl: process.env.LMSTUDIO_BASE_URL || 'http://localhost:1234',
        apiKey: process.env.LMSTUDIO_API_KEY || '',
        type: 'openai-compatible'
      },
      anythingllm: {
        enabled: false,
        baseUrl: process.env.ANYTHINGLLM_BASE_URL || 'http://localhost:3001',
        apiKey: process.env.ANYTHINGLLM_API_KEY || '',
        type: 'openai-compatible'
      },
      openai: {
        enabled: false,
        baseUrl: 'https://api.openai.com/v1',
        apiKey: process.env.OPENAI_API_KEY || '',
        organization: process.env.OPENAI_ORGANIZATION || '',
        type: 'openai'
      }
    },
    models: {
      summary: process.env.MODEL_SUMMARY || 'llama3',
      keywords: process.env.MODEL_KEYWORDS || 'llama3',
      analysis: process.env.MODEL_ANALYSIS || 'llama3'
    }
  }
};

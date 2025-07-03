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

  // Configuration Ollama
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
  }
};

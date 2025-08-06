const axios = require('axios');
const logger = require('../../utils/logger');
const config = require('../../config/ollama');

class OllamaService {
    constructor() {
        this.baseURL = config.baseURL;
        this.timeout = config.timeout;
        this.defaultModel = config.defaultModel;
        this.retryAttempts = config.retryAttempts;
        this.retryDelay = config.retryDelay;
        
        this.client = axios.create({
            baseURL: this.baseURL,
            timeout: this.timeout,
            headers: {
                'Content-Type': 'application/json'
            }
        });

        // Intercepteur pour les retry
        this.setupRetryInterceptor();
    }

    setupRetryInterceptor() {
        this.client.interceptors.response.use(
            (response) => response,
            async (error) => {
                const { config: axiosConfig } = error;
                
                if (!axiosConfig || !axiosConfig.retry) {
                    axiosConfig.retry = 0;
                }

                if (axiosConfig.retry < this.retryAttempts) {
                    axiosConfig.retry++;
                    logger.warn(`Tentative ${axiosConfig.retry}/${this.retryAttempts} pour la requête Ollama`);
                    
                    await new Promise(resolve => setTimeout(resolve, this.retryDelay * axiosConfig.retry));
                    return this.client(axiosConfig);
                }

                return Promise.reject(error);
            }
        );
    }

    async generateCompletion(model, prompt, options = {}) {
        try {
            const requestOptions = {
                ...config.defaultOptions,
                ...options
            };

            logger.debug('Génération Ollama', {
                model: model || this.defaultModel,
                promptLength: prompt.length,
                options: requestOptions
            });

            const response = await this.client.post('/api/generate', {
                model: model || this.defaultModel,
                prompt,
                stream: false,
                options: requestOptions
            });
            
            const result = response.data.response;
            
            logger.debug('Réponse Ollama reçue', {
                responseLength: result.length,
                model: model || this.defaultModel
            });

            return result;
        } catch (error) {
            logger.error('Erreur Ollama generateCompletion:', {
                message: error.message,
                model: model || this.defaultModel,
                status: error.response?.status,
                data: error.response?.data
            });
            throw new Error(`Erreur lors de la génération: ${error.message}`);
        }
    }

    async listModels() {
        try {
            logger.debug('Récupération de la liste des modèles Ollama');
            
            const response = await this.client.get('/api/tags');
            const models = response.data.models || [];
            
            logger.debug(`${models.length} modèles trouvés`);
            
            return models.map(model => ({
                name: model.name,
                size: model.size,
                digest: model.digest,
                modified_at: model.modified_at
            }));
        } catch (error) {
            logger.error('Erreur récupération modèles:', {
                message: error.message,
                status: error.response?.status
            });
            throw new Error(`Impossible de récupérer les modèles: ${error.message}`);
        }
    }

    async checkHealth() {
        try {
            const response = await this.client.get('/api/version', { timeout: 5000 });
            logger.debug('Health check Ollama réussi', { version: response.data.version });
            return {
                status: 'healthy',
                version: response.data.version,
                timestamp: new Date().toISOString()
            };
        } catch (error) {
            logger.warn('Health check Ollama échoué:', error.message);
            return {
                status: 'unhealthy',
                error: error.message,
                timestamp: new Date().toISOString()
            };
        }
    }

    async pullModel(modelName) {
        try {
            logger.info(`Téléchargement du modèle: ${modelName}`);
            
            const response = await this.client.post('/api/pull', {
                name: modelName,
                stream: false
            });
            
            logger.info(`Modèle ${modelName} téléchargé avec succès`);
            return response.data;
        } catch (error) {
            logger.error(`Erreur téléchargement modèle ${modelName}:`, error.message);
            throw new Error(`Impossible de télécharger le modèle ${modelName}: ${error.message}`);
        }
    }

    getModelForTask(task) {
        return config.models[task] || this.defaultModel;
    }
}

module.exports = new OllamaService();

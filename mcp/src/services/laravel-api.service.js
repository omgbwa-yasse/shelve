// Service pour interagir avec l'API Laravel
const axios = require('axios');
const config = require('../config');

class LaravelApiService {
  constructor() {
    this.apiUrl = config.laravel.apiUrl;
    this.apiToken = config.laravel.apiToken;
    this.timeout = config.laravel.timeout;

    // Créer une instance axios configurée
    this.api = axios.create({
      baseURL: this.apiUrl,
      timeout: this.timeout,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    // Ajouter le token d'authentification s'il existe
    if (this.apiToken) {
      this.api.defaults.headers.common['Authorization'] = `Bearer ${this.apiToken}`;
    }
  }

  /**
   * Recherche des termes dans le thésaurus Laravel
   * @param {Array<string>} keywords - Liste de mots-clés à rechercher
   * @returns {Promise<Object>} Les termes correspondants du thésaurus
   */
  async searchTermsInThesaurus(keywords) {
    try {
      if (!this.apiToken) {
        return { success: false, error: 'Token API Laravel manquant' };
      }

      if (!keywords || keywords.length === 0) {
        return { success: true, terms: [] };
      }

      const response = await this.api.post('/terms/search', { keywords });

      return {
        success: true,
        terms: response.data.terms || []
      };
    } catch (error) {
      console.error('Erreur lors de la recherche dans le thésaurus:', error.message);
      return {
        success: false,
        error: error.message,
        terms: []
      };
    }
  }

  /**
   * Recherche des termes par catégorie dans le thésaurus
   * @param {string} category - La catégorie des termes (geographic, thematic, typology)
   * @param {Array<string>} terms - Liste de termes à rechercher
   * @returns {Promise<Array>} Les résultats de la recherche
   */
  async searchTermsByCategory(category, terms) {
    if (!terms || terms.length === 0) return [];

    const searchResults = [];

    for (const term of terms) {
      try {
        const response = await this.api.get('/thesaurus/search', {
          params: {
            q: term,
            type: category,
            limit: 2
          }
        });

        if (response.data && response.data.data && response.data.data.length > 0) {
          // Ajouter uniquement le premier résultat qui correspond le mieux
          searchResults.push({
            ...response.data.data[0],
            matched_from: term
          });
        }
      } catch (error) {
        console.error(`Erreur lors de la recherche du terme "${term}" dans le thésaurus:`, error);
      }
    }

    return searchResults;
  }

  /**
   * Assigne des termes à un record
   * @param {number} recordId - ID du record
   * @param {Object} categorizedTerms - Termes à assigner par catégorie
   * @returns {Promise<Object>} Résultat de l'opération
   */
  async assignTermsToRecord(recordId, categorizedTerms) {
    try {
      // Préparer les termes pour l'assignation
      const flatTerms = [
        ...categorizedTerms.geographic,
        ...categorizedTerms.thematic,
        ...categorizedTerms.typologic || categorizedTerms.typology
      ].filter(term => term && term.id);

      if (flatTerms.length === 0) {
        return {
          success: true,
          message: "Aucun terme à assigner",
          assigned: []
        };
      }

      // Appeler l'API Laravel pour assigner les termes
      const response = await this.api.post(`/records/${recordId}/assign-terms`, { terms: flatTerms });

      return {
        success: true,
        message: `${response.data.count || flatTerms.length} termes assignés avec succès`,
        assigned: response.data.assigned || flatTerms
      };
    } catch (error) {
      console.error('Erreur lors de l\'assignation des termes:', error);
      return {
        success: false,
        error: error.message,
        message: `Erreur lors de l'assignation des termes: ${error.message}`,
        assigned: []
      };
    }
  }

  /**
   * Récupère les détails d'un bordereau de transfert (slip)
   * @param {string|number} slipId - L'identifiant du bordereau
   * @returns {Promise<Object>} Les détails du bordereau
   */
  async getSlipDetails(slipId) {
    try {
      if (!this.apiToken) {
        console.warn('Aucun token d\'API Laravel fourni pour getSlipDetails');
        return { success: false, error: 'Token d\'API manquant' };
      }

      const response = await axios.get(`${this.apiUrl}/slips/${slipId}`, {
        headers: { Authorization: `Bearer ${this.apiToken}` }
      });

      return {
        success: true,
        slip: response.data
      };
    } catch (error) {
      console.error('Erreur lors de la récupération des détails du bordereau:', error.message);
      return {
        success: false,
        error: error.response?.data?.message || error.message,
        statusCode: error.response?.status
      };
    }
  }

  /**
   * Récupère les enregistrements associés à un bordereau de transfert
   * @param {string|number} slipId - L'identifiant du bordereau
   * @returns {Promise<Object>} Les enregistrements associés au bordereau
   */
  async getSlipRecords(slipId) {
    try {
      if (!this.apiToken) {
        console.warn('Aucun token d\'API Laravel fourni pour getSlipRecords');
        return { success: false, error: 'Token d\'API manquant' };
      }

      const response = await axios.get(`${this.apiUrl}/slips/${slipId}/records`, {
        headers: { Authorization: `Bearer ${this.apiToken}` }
      });

      return {
        success: true,
        records: response.data
      };
    } catch (error) {
      console.error('Erreur lors de la récupération des enregistrements du bordereau:', error.message);
      return {
        success: false,
        error: error.response?.data?.message || error.message,
        statusCode: error.response?.status
      };
    }
  }
}

module.exports = new LaravelApiService();

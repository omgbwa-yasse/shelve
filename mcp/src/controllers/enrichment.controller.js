// Contrôleur pour l'enrichissement des records via multiple providers IA
const multiProviderAiService = require('../services/multi-provider-ai.service');
const recordsService = require('../services/records.service');
const termsService = require('../services/terms.service');

/**
 * Contrôleur pour l'enrichissement des records
 */
class EnrichmentController {
  /**
   * Formate le titre d'un record
   * @param {Request} req - Requête Express
   * @param {Response} res - Réponse Express
   */
  async formatTitle(req, res) {
    try {
      const { id } = req.params;
      const { provider } = req.query;

      // Récupérer le record
      const record = await recordsService.getRecord(id);
      if (!record.success) {
        return res.status(404).json({
          success: false,
          error: 'Record non trouvé'
        });
      }

      const recordData = record.data;
      const currentTitle = recordData.name || recordData.code || '';

      if (!currentTitle) {
        return res.status(400).json({
          success: false,
          error: 'Aucun titre à formater'
        });
      }

      const prompt = `Reformulez ce titre d'archive de manière plus claire et professionnelle, en gardant l'essence du contenu. Le titre doit être concis et informatif.

Titre actuel: "${currentTitle}"

Titre reformulé:`;

      const response = await multiProviderAiService.generate(
        prompt,
        null, // Utiliser le modèle par défaut
        { temperature: 0.3, max_tokens: 100 },
        provider
      );

      if (!response.success) {
        return res.status(500).json({
          success: false,
          error: response.error
        });
      }

      const formattedTitle = response.content.trim().replace(/^["']|["']$/g, '');

      res.json({
        success: true,
        data: {
          original_title: currentTitle,
          formatted_title: formattedTitle,
          provider: response.provider,
          model: response.model,
          stats: response.stats
        }
      });
    } catch (error) {
      console.error('Erreur lors du formatage du titre:', error);
      res.status(500).json({
        success: false,
        error: 'Erreur interne du serveur'
      });
    }
  }

  /**
   * Génère un résumé d'un record
   * @param {Request} req - Requête Express
   * @param {Response} res - Réponse Express
   */
  async generateSummary(req, res) {
    try {
      const { id } = req.params;
      const { provider } = req.query;

      // Récupérer le record
      const record = await recordsService.getRecord(id);
      if (!record.success) {
        return res.status(404).json({
          success: false,
          error: 'Record non trouvé'
        });
      }

      const recordData = record.data;
      const content = recordData.content || recordData.description || '';

      if (!content || content.length < 50) {
        return res.status(400).json({
          success: false,
          error: 'Contenu insuffisant pour générer un résumé'
        });
      }

      const response = await multiProviderAiService.generateSummary(
        content,
        { max_tokens: 300 },
        provider
      );

      if (!response.success) {
        return res.status(500).json({
          success: false,
          error: response.error
        });
      }

      res.json({
        success: true,
        data: {
          original_content_length: content.length,
          summary: response.content.trim(),
          provider: response.provider,
          model: response.model,
          stats: response.stats
        }
      });
    } catch (error) {
      console.error('Erreur lors de la génération du résumé:', error);
      res.status(500).json({
        success: false,
        error: 'Erreur interne du serveur'
      });
    }
  }

  /**
   * Extrait des mots-clés d'un record
   * @param {Request} req - Requête Express
   * @param {Response} res - Réponse Express
   */
  async extractKeywords(req, res) {
    try {
      const { id } = req.params;
      const { provider, max_keywords = 10 } = req.query;

      // Récupérer le record
      const record = await recordsService.getRecord(id);
      if (!record.success) {
        return res.status(404).json({
          success: false,
          error: 'Record non trouvé'
        });
      }

      const recordData = record.data;
      const content = [
        recordData.name || '',
        recordData.content || '',
        recordData.description || ''
      ].filter(text => text.length > 0).join(' ');

      if (!content || content.length < 20) {
        return res.status(400).json({
          success: false,
          error: 'Contenu insuffisant pour extraire des mots-clés'
        });
      }

      const response = await multiProviderAiService.extractKeywords(
        content,
        parseInt(max_keywords),
        provider
      );

      if (!response.success) {
        return res.status(500).json({
          success: false,
          error: response.error
        });
      }

      // Parser les mots-clés de la réponse
      const keywordsText = response.content.trim();
      const keywords = keywordsText
        .split(',')
        .map(keyword => keyword.trim().replace(/^["']|["']$/g, ''))
        .filter(keyword => keyword.length > 0)
        .slice(0, parseInt(max_keywords));

      res.json({
        success: true,
        data: {
          content_length: content.length,
          keywords,
          keywords_count: keywords.length,
          raw_response: keywordsText,
          provider: response.provider,
          model: response.model,
          stats: response.stats
        }
      });
    } catch (error) {
      console.error('Erreur lors de l\'extraction des mots-clés:', error);
      res.status(500).json({
        success: false,
        error: 'Erreur interne du serveur'
      });
    }
  }

  /**
   * Extrait des mots-clés catégorisés d'un record
   * @param {Request} req - Requête Express
   * @param {Response} res - Réponse Express
   */
  async extractCategorizedKeywords(req, res) {
    try {
      const { id } = req.params;
      const { provider } = req.query;

      // Récupérer le record
      const record = await recordsService.getRecord(id);
      if (!record.success) {
        return res.status(404).json({
          success: false,
          error: 'Record non trouvé'
        });
      }

      const recordData = record.data;
      const content = [
        recordData.name || '',
        recordData.content || '',
        recordData.description || ''
      ].filter(text => text.length > 0).join(' ');

      if (!content || content.length < 20) {
        return res.status(400).json({
          success: false,
          error: 'Contenu insuffisant pour extraire des mots-clés'
        });
      }

      const prompt = `Analysez le texte suivant et extrayez des mots-clés organisés par catégories. Répondez au format JSON avec les catégories suivantes:
- "personnes": noms de personnes, organisations, institutions
- "lieux": noms de lieux, adresses, régions
- "dates": périodes, dates, événements temporels
- "sujets": thèmes, concepts, domaines d'activité
- "actions": activités, processus, événements

Texte à analyser:
${content}

Réponse JSON:`;

      const response = await multiProviderAiService.generate(
        prompt,
        null,
        { temperature: 0.2, max_tokens: 500 },
        provider
      );

      if (!response.success) {
        return res.status(500).json({
          success: false,
          error: response.error
        });
      }

      // Tenter de parser la réponse JSON
      let categorizedKeywords;
      try {
        // Nettoyer la réponse pour extraire le JSON
        const jsonMatch = response.content.match(/\{[\s\S]*\}/);
        const jsonString = jsonMatch ? jsonMatch[0] : response.content;
        categorizedKeywords = JSON.parse(jsonString);
      } catch (parseError) {
        // Fallback: créer une structure simple
        categorizedKeywords = {
          "general": response.content.split(',').map(k => k.trim()).filter(k => k.length > 0)
        };
      }

      res.json({
        success: true,
        data: {
          content_length: content.length,
          categorized_keywords: categorizedKeywords,
          raw_response: response.content,
          provider: response.provider,
          model: response.model,
          stats: response.stats
        }
      });
    } catch (error) {
      console.error('Erreur lors de l\'extraction des mots-clés catégorisés:', error);
      res.status(500).json({
        success: false,
        error: 'Erreur interne du serveur'
      });
    }
  }

  /**
   * Effectue un enrichissement complet d'un record
   * @param {Request} req - Requête Express
   * @param {Response} res - Réponse Express
   */
  async enrichRecord(req, res) {
    try {
      const { id } = req.params;
      const { provider, operations = ['summary', 'keywords', 'title'] } = req.query;

      // Récupérer le record
      const record = await recordsService.getRecord(id);
      if (!record.success) {
        return res.status(404).json({
          success: false,
          error: 'Record non trouvé'
        });
      }

      const enrichmentResults = {};
      const operationsList = Array.isArray(operations) ? operations : operations.split(',');

      // Enrichissement du titre
      if (operationsList.includes('title')) {
        req.params.id = id;
        req.query.provider = provider;
        const titleMock = {
          json: (data) => { enrichmentResults.title = data; },
          status: () => titleMock
        };
        await this.formatTitle(req, titleMock);
      }

      // Génération du résumé
      if (operationsList.includes('summary')) {
        const summaryMock = {
          json: (data) => { enrichmentResults.summary = data; },
          status: () => summaryMock
        };
        await this.generateSummary(req, summaryMock);
      }

      // Extraction des mots-clés
      if (operationsList.includes('keywords')) {
        const keywordsMock = {
          json: (data) => { enrichmentResults.keywords = data; },
          status: () => keywordsMock
        };
        await this.extractKeywords(req, keywordsMock);
      }

      res.json({
        success: true,
        data: {
          record_id: id,
          operations: operationsList,
          results: enrichmentResults,
          provider: provider || 'default'
        }
      });
    } catch (error) {
      console.error('Erreur lors de l\'enrichissement complet:', error);
      res.status(500).json({
        success: false,
        error: 'Erreur interne du serveur'
      });
    }
  }

  /**
   * Vérifie le statut des providers IA
   * @param {Request} req - Requête Express
   * @param {Response} res - Réponse Express
   */
  async checkStatus(req, res) {
    try {
      const status = await multiProviderAiService.checkProvidersStatus();

      res.json({
        success: true,
        data: status
      });
    } catch (error) {
      console.error('Erreur lors de la vérification du statut:', error);
      res.status(500).json({
        success: false,
        error: 'Erreur interne du serveur'
      });
    }
  }

  /**
   * Vide le cache de configuration des providers
   * @param {Request} req - Requête Express
   * @param {Response} res - Réponse Express
   */
  async clearCache(req, res) {
    try {
      multiProviderAiService.clearConfigCache();

      res.json({
        success: true,
        message: 'Cache vidé avec succès'
      });
    } catch (error) {
      console.error('Erreur lors du vidage du cache:', error);
      res.status(500).json({
        success: false,
        error: 'Erreur interne du serveur'
      });
    }
  }
}

module.exports = new EnrichmentController();

// Service pour l'analyse et le traitement des documents numériques (attachments)
const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');
const config = require('../config');
const aiService = require('./ai.service');
const recordsService = require('./records.service');

/**
 * Service d'analyse des documents numériques
 */
class AttachmentService {
  constructor() {
    this.laravelApiUrl = config.laravel.apiUrl;
    this.supportedFormats = ['pdf', 'txt', 'docx', 'doc', 'rtf', 'odt'];
  }

  /**
   * Analyser plusieurs documents et proposer une description et indexation
   * @param {Array} attachmentIds - IDs des documents à analyser
   * @param {Object} analysisOptions - Options d'analyse
   * @param {string} modelName - Nom du modèle IA à utiliser
   * @returns {Promise<Object>} Résultat de l'analyse
   */
  async analyzeMultipleDocuments(attachmentIds, analysisOptions = {}, modelName) {
    try {
      console.log(`Début de l'analyse de ${attachmentIds.length} documents`);

      // 1. Récupérer les métadonnées des documents
      const documentsMetadata = await this.getDocumentsMetadata(attachmentIds);

      // 2. Extraire le contenu de chaque document
      const documentsContent = await Promise.all(
        documentsMetadata.map(doc => this.extractDocumentContent(doc))
      );

      // 3. Analyser le contenu global
      const globalAnalysis = await this.performGlobalAnalysis(documentsContent, modelName);

      // 4. Générer la description du record
      const recordDescription = await this.generateRecordFromAnalysis(
        globalAnalysis,
        documentsMetadata,
        analysisOptions,
        modelName
      );

      // 5. Proposer l'indexation thésaurus
      const thesaurusIndexing = await this.generateThesaurusFromAnalysis(
        globalAnalysis,
        analysisOptions.thesaurusScope,
        modelName
      );

      return {
        suggestedRecord: recordDescription,
        thesaurusTerms: thesaurusIndexing,
        documentSummary: globalAnalysis.summary,
        confidence: globalAnalysis.confidence,
        processedDocuments: documentsContent.map(doc => ({
          id: doc.id,
          name: doc.name,
          extractedLength: doc.extractedText?.length || 0,
          processingStatus: doc.processingStatus
        }))
      };
    } catch (error) {
      console.error('Erreur dans analyzeMultipleDocuments:', error);
      throw error;
    }
  }

  /**
   * Analyser un seul document
   * @param {number} attachmentId - ID du document
   * @param {Object} extractionOptions - Options d'extraction
   * @param {string} modelName - Nom du modèle IA
   * @returns {Promise<Object>} Analyse du document
   */
  async analyzeSingleDocument(attachmentId, extractionOptions = {}, modelName) {
    try {
      // Récupérer les métadonnées du document
      const metadata = await this.getDocumentMetadata(attachmentId);

      // Extraire le contenu
      const content = await this.extractDocumentContent(metadata);

      // Analyser avec l'IA
      const analysis = await this.performSingleDocumentAnalysis(content, modelName);

      // Rechercher des termes du thésaurus
      const suggestedTerms = await recordsService.searchThesaurus(
        null,
        content.extractedText,
        modelName,
        extractionOptions.maxTerms || 10
      );

      return {
        extractedText: content.extractedText,
        summary: analysis.summary,
        keywords: analysis.keywords,
        suggestedTerms: suggestedTerms,
        metadata: {
          fileName: metadata.name,
          fileSize: metadata.size,
          fileType: metadata.type,
          processingDate: new Date().toISOString()
        }
      };
    } catch (error) {
      console.error('Erreur dans analyzeSingleDocument:', error);
      throw error;
    }
  }

  /**
   * Récupérer les métadonnées de plusieurs documents
   * @param {Array} attachmentIds - IDs des documents
   * @returns {Promise<Array>} Métadonnées des documents
   */
  async getDocumentsMetadata(attachmentIds) {
    try {
      const response = await axios.post(`${this.laravelApiUrl}/attachments/metadata`, {
        attachment_ids: attachmentIds
      }, {
        headers: {
          'Authorization': `Bearer ${config.laravel.apiToken}`,
          'Content-Type': 'application/json'
        }
      });

      return response.data.attachments || [];
    } catch (error) {
      console.error('Erreur lors de la récupération des métadonnées:', error);
      throw new Error('Impossible de récupérer les métadonnées des documents');
    }
  }

  /**
   * Récupérer les métadonnées d'un seul document
   * @param {number} attachmentId - ID du document
   * @returns {Promise<Object>} Métadonnées du document
   */
  async getDocumentMetadata(attachmentId) {
    try {
      const response = await axios.get(`${this.laravelApiUrl}/attachments/${attachmentId}/metadata`, {
        headers: {
          'Authorization': `Bearer ${config.laravel.apiToken}`
        }
      });

      return response.data.attachment;
    } catch (error) {
      console.error('Erreur lors de la récupération des métadonnées:', error);
      throw new Error('Impossible de récupérer les métadonnées du document');
    }
  }

  /**
   * Extraire le contenu textuel d'un document
   * @param {Object} documentMetadata - Métadonnées du document
   * @returns {Promise<Object>} Contenu extrait
   */
  async extractDocumentContent(documentMetadata) {
    try {
      const fileExtension = path.extname(documentMetadata.name).toLowerCase().substring(1);

      if (!this.supportedFormats.includes(fileExtension)) {
        return {
          id: documentMetadata.id,
          name: documentMetadata.name,
          extractedText: '',
          processingStatus: 'unsupported_format',
          error: `Format ${fileExtension} non supporté`
        };
      }

      // Appeler l'API Laravel pour extraire le contenu
      const response = await axios.post(`${this.laravelApiUrl}/attachments/extract-content`, {
        attachment_id: documentMetadata.id
      }, {
        headers: {
          'Authorization': `Bearer ${config.laravel.apiToken}`,
          'Content-Type': 'application/json'
        }
      });

      return {
        id: documentMetadata.id,
        name: documentMetadata.name,
        extractedText: response.data.content || '',
        processingStatus: 'success',
        metadata: documentMetadata
      };
    } catch (error) {
      console.error(`Erreur lors de l'extraction du contenu pour ${documentMetadata.name}:`, error);
      return {
        id: documentMetadata.id,
        name: documentMetadata.name,
        extractedText: '',
        processingStatus: 'extraction_failed',
        error: error.message
      };
    }
  }

  /**
   * Effectuer une analyse globale de plusieurs documents
   * @param {Array} documentsContent - Contenu des documents
   * @param {string} modelName - Nom du modèle IA
   * @returns {Promise<Object>} Analyse globale
   */
  async performGlobalAnalysis(documentsContent, modelName) {
    try {
      // Fusionner tout le contenu textuel
      const allText = documentsContent
        .filter(doc => doc.extractedText && doc.processingStatus === 'success')
        .map(doc => `Document: ${doc.name}\n${doc.extractedText}`)
        .join('\n\n---\n\n');

      if (!allText.trim()) {
        throw new Error('Aucun contenu textuel valide trouvé dans les documents');
      }

      const prompt = `Analysez l'ensemble de ces documents et fournissez une analyse structurée au format JSON:

DOCUMENTS À ANALYSER:
${allText}

Fournissez votre analyse sous cette structure JSON exacte:
{
  "summary": "Résumé global des documents (2-3 phrases)",
  "mainThemes": ["thème1", "thème2", "thème3"],
  "dateRange": {
    "start": "YYYY-MM-DD ou null",
    "end": "YYYY-MM-DD ou null",
    "note": "information sur les dates si pertinente"
  },
  "documentTypes": ["type1", "type2"],
  "keyEntities": {
    "persons": ["nom1", "nom2"],
    "organizations": ["org1", "org2"],
    "places": ["lieu1", "lieu2"],
    "concepts": ["concept1", "concept2"]
  },
  "archivalValue": {
    "level": "high|medium|low",
    "justification": "explication de la valeur archivistique"
  },
  "confidence": 0.85
}`;

      const aiResponse = await aiService.generate(prompt, modelName);

      try {
        const analysis = JSON.parse(aiResponse.content);
        return analysis;
      } catch (parseError) {
        console.warn('Réponse IA non-JSON, tentative d\'extraction:', aiResponse.content);
        // Fallback: créer une analyse basique
        return this.createFallbackAnalysis(allText, documentsContent);
      }
    } catch (error) {
      console.error('Erreur lors de l\'analyse globale:', error);
      throw error;
    }
  }

  /**
   * Générer une description de record à partir de l'analyse
   * @param {Object} globalAnalysis - Analyse globale
   * @param {Array} documentsMetadata - Métadonnées des documents
   * @param {Object} analysisOptions - Options d'analyse
   * @param {string} modelName - Nom du modèle IA
   * @returns {Promise<Object>} Description du record
   */
  async generateRecordFromAnalysis(globalAnalysis, documentsMetadata, analysisOptions, modelName) {
    try {
      const prompt = `Basé sur cette analyse documentaire, générez une description de record archivistique au format JSON:

ANALYSE DOCUMENTAIRE:
${JSON.stringify(globalAnalysis, null, 2)}

DOCUMENTS ANALYSÉS:
${documentsMetadata.map(doc => `- ${doc.name} (${doc.type}, ${doc.size} octets)`).join('\n')}

Générez une description complète au format JSON:
{
  "title": "Titre formaté selon les règles archivistiques",
  "description": "Description détaillée du contenu",
  "dateStart": "YYYY-MM-DD ou null",
  "dateEnd": "YYYY-MM-DD ou null",
  "scope": "Portée et contenu",
  "arrangement": "Classement et organisation",
  "accessConditions": "Conditions d'accès suggérées",
  "reproductionConditions": "Conditions de reproduction",
  "language": "Langue principale des documents",
  "relatedMaterials": "Matériaux connexes identifiés",
  "notes": "Notes générales",
  "suggestedLevel": "fonds|series|file|item",
  "suggestedSupport": "papier|numérique|mixte",
  "confidence": 0.85
}`;

      const aiResponse = await aiService.generate(prompt, modelName);

      try {
        const recordDescription = JSON.parse(aiResponse.content);
        return recordDescription;
      } catch (parseError) {
        console.warn('Réponse IA non-JSON pour record:', aiResponse.content);
        return this.createFallbackRecord(globalAnalysis, documentsMetadata);
      }
    } catch (error) {
      console.error('Erreur lors de la génération de record:', error);
      throw error;
    }
  }

  /**
   * Générer l'indexation thésaurus à partir de l'analyse
   * @param {Object} globalAnalysis - Analyse globale
   * @param {string} thesaurusScope - Portée du thésaurus
   * @param {string} modelName - Nom du modèle IA
   * @returns {Promise<Object>} Indexation thésaurus
   */
  async generateThesaurusFromAnalysis(globalAnalysis, thesaurusScope, modelName) {
    try {
      // Utiliser le service existant pour rechercher dans le thésaurus
      const conceptsText = [
        ...globalAnalysis.mainThemes,
        ...globalAnalysis.keyEntities.concepts,
        globalAnalysis.summary
      ].join(' ');

      const thesaurusMatches = await recordsService.searchThesaurus(
        null,
        conceptsText,
        modelName,
        15
      );

      // Générer des poids et contextes avec l'IA
      const prompt = `Basé sur cette analyse documentaire, assignez des poids et contextes aux termes du thésaurus trouvés:

ANALYSE:
${JSON.stringify(globalAnalysis, null, 2)}

TERMES TROUVÉS:
${thesaurusMatches.map(term => `- ${term.text} (ID: ${term.id})`).join('\n')}

Fournissez au format JSON:
{
  "weightedTerms": [
    {
      "termId": 123,
      "weight": 0.9,
      "context": "terme principal|terme secondaire|terme connexe",
      "justification": "pourquoi ce terme est pertinent"
    }
  ],
  "confidence": 0.85
}`;

      const aiResponse = await aiService.generate(prompt, modelName);

      try {
        const weightedIndexing = JSON.parse(aiResponse.content);
        return {
          suggestedTerms: thesaurusMatches,
          weightedTerms: weightedIndexing.weightedTerms,
          confidence: weightedIndexing.confidence
        };
      } catch (parseError) {
        // Fallback: utiliser des poids par défaut
        return {
          suggestedTerms: thesaurusMatches,
          weightedTerms: thesaurusMatches.map(term => ({
            termId: term.id,
            weight: 0.7,
            context: 'terme connexe',
            justification: 'Terme identifié automatiquement'
          })),
          confidence: 0.6
        };
      }
    } catch (error) {
      console.error('Erreur lors de la génération d\'indexation thésaurus:', error);
      throw error;
    }
  }

  /**
   * Analyser un seul document avec l'IA
   * @param {Object} content - Contenu du document
   * @param {string} modelName - Nom du modèle IA
   * @returns {Promise<Object>} Analyse du document
   */
  async performSingleDocumentAnalysis(content, modelName) {
    try {
      const prompt = `Analysez ce document et fournissez une analyse structurée au format JSON:

DOCUMENT: ${content.name}
CONTENU:
${content.extractedText}

Fournissez votre analyse au format JSON:
{
  "summary": "Résumé du document (2-3 phrases)",
  "keywords": ["mot-clé1", "mot-clé2", "mot-clé3"],
  "documentType": "type de document identifié",
  "mainSubjects": ["sujet1", "sujet2"],
  "dateReferences": ["référence date1", "référence date2"],
  "entities": {
    "persons": ["personne1", "personne2"],
    "organizations": ["org1", "org2"],
    "places": ["lieu1", "lieu2"]
  },
  "confidence": 0.85
}`;

      const aiResponse = await aiService.generate(prompt, modelName);

      try {
        return JSON.parse(aiResponse.content);
      } catch (parseError) {
        return {
          summary: "Analyse automatique du document",
          keywords: [],
          documentType: "document",
          mainSubjects: [],
          dateReferences: [],
          entities: { persons: [], organizations: [], places: [] },
          confidence: 0.3
        };
      }
    } catch (error) {
      console.error('Erreur lors de l\'analyse de document unique:', error);
      throw error;
    }
  }

  /**
   * Créer une analyse de fallback
   * @param {string} allText - Tout le texte
   * @param {Array} documentsContent - Contenu des documents
   * @returns {Object} Analyse basique
   */
  createFallbackAnalysis(allText, documentsContent) {
    return {
      summary: "Documents analysés automatiquement",
      mainThemes: ["documents", "analyse", "contenu"],
      dateRange: { start: null, end: null, note: "Dates non identifiées" },
      documentTypes: [...new Set(documentsContent.map(doc =>
        path.extname(doc.name).toLowerCase().substring(1)
      ))],
      keyEntities: {
        persons: [],
        organizations: [],
        places: [],
        concepts: []
      },
      archivalValue: {
        level: "medium",
        justification: "Valeur à déterminer manuellement"
      },
      confidence: 0.3
    };
  }

  /**
   * Créer un record de fallback
   * @param {Object} globalAnalysis - Analyse globale
   * @param {Array} documentsMetadata - Métadonnées
   * @returns {Object} Record basique
   */
  createFallbackRecord(globalAnalysis, documentsMetadata) {
    return {
      title: `Documents numériques - ${new Date().toLocaleDateString()}`,
      description: globalAnalysis.summary || "Collection de documents numériques",
      dateStart: null,
      dateEnd: null,
      scope: "Documents analysés automatiquement",
      arrangement: "Organisation chronologique",
      accessConditions: "Accès selon politique de l'institution",
      reproductionConditions: "Reproduction selon politique de l'institution",
      language: "français",
      relatedMaterials: "",
      notes: `Analyse automatique de ${documentsMetadata.length} documents`,
      suggestedLevel: "file",
      suggestedSupport: "numérique",
      confidence: 0.3
    };
  }

  /**
   * Générer un record complet avec indexation
   * @param {Array} attachmentIds - IDs des documents
   * @param {Object} recordOptions - Options pour le record
   * @param {Object} indexingOptions - Options pour l'indexation
   * @param {string} modelName - Nom du modèle IA
   * @returns {Promise<Object>} Record complet avec indexation
   */
  async generateCompleteRecordWithIndexing(attachmentIds, recordOptions, indexingOptions, modelName) {
    try {
      const startTime = Date.now();

      // Analyser les documents
      const analysis = await this.analyzeMultipleDocuments(attachmentIds, {
        ...recordOptions,
        thesaurusScope: indexingOptions.thesaurusScope
      }, modelName);

      const processingTime = Date.now() - startTime;

      return {
        record: analysis.suggestedRecord,
        indexing: analysis.thesaurusTerms,
        documentAnalysis: {
          summary: analysis.documentSummary,
          processedDocuments: analysis.processedDocuments
        },
        processingStats: {
          processingTimeMs: processingTime,
          documentsCount: attachmentIds.length,
          successfulExtractions: analysis.processedDocuments.filter(
            doc => doc.processingStatus === 'success'
          ).length
        },
        recommendations: {
          manualReview: analysis.confidence < 0.7,
          suggestedActions: this.generateRecommendations(analysis)
        }
      };
    } catch (error) {
      console.error('Erreur lors de la génération complète:', error);
      throw error;
    }
  }

  /**
   * Générer des recommandations basées sur l'analyse
   * @param {Object} analysis - Analyse complète
   * @returns {Array} Liste de recommandations
   */
  generateRecommendations(analysis) {
    const recommendations = [];

    if (analysis.confidence < 0.5) {
      recommendations.push("Révision manuelle recommandée - confiance faible");
    }

    if (analysis.processedDocuments.some(doc => doc.processingStatus !== 'success')) {
      recommendations.push("Vérifier les documents non traités");
    }

    if (analysis.thesaurusTerms.suggestedTerms.length === 0) {
      recommendations.push("Aucun terme de thésaurus trouvé - indexation manuelle nécessaire");
    }

    if (analysis.suggestedRecord.dateStart === null) {
      recommendations.push("Dates non identifiées - vérification manuelle requise");
    }

    return recommendations;
  }
}

module.exports = new AttachmentService();

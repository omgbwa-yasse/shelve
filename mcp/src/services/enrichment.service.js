// Service d'enrichissement des records
const ollamaService = require('./ollama.service');
const laravelApiService = require('./laravel-api.service');
const config = require('../config');

class EnrichmentService {
  constructor() {
    this.defaultModel = config.ollama.defaultModel;
  }

  /**
   * Extrait des mots-clés catégorisés (géographiques, thématiques, typologiques) à partir d'un texte
   * @param {string} content - Le contenu textuel à analyser
   * @param {string} modelName - Le nom du modèle à utiliser
   * @param {number} maxTermsPerCategory - Nombre maximum de termes par catégorie
   * @returns {Promise<Object>} Les mots-clés catégorisés et les termes correspondants du thésaurus
   */
  async extractCategorizedKeywords(content, modelName = this.defaultModel, maxTermsPerCategory = 3) {
    try {
      // Vérifier que le contenu n'est pas vide
      if (!content || content.trim() === '') {
        throw new Error('Le contenu à analyser ne peut pas être vide');
      }

      // Générer des mots-clés catégorisés à partir du contenu
      const prompt = `
Analysez le texte suivant et extrayez-en des mots-clés organisés en trois catégories :

1. GEOGRAPHIQUE : lieux, pays, régions, villes, noms géographiques
2. THEMATIQUE : sujets, concepts, disciplines, domaines d'activité
3. TYPOLOGIE : types de documents, formats, genres de textes

Pour chaque catégorie, identifiez jusqu'à ${maxTermsPerCategory} termes pertinents.
Répondez UNIQUEMENT au format suivant, sans commentaires additionnels :

GEOGRAPHIQUE:
- [terme1]
- [terme2]
- [terme3]

THEMATIQUE:
- [terme1]
- [terme2]
- [terme3]

TYPOLOGIE:
- [terme1]
- [terme2]
- [terme3]

Texte à analyser : "${content}"
`;

      const result = await ollamaService.generate(
        prompt,
        modelName,
        { temperature: 0.2 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de génération des mots-clés');
      }

      const response = result.content;

      // Traitement des catégories et des termes extraits
      const categoryResults = {};

      // Parsing du résultat pour extraire les catégories et les termes
      const categoryRegex = /\n*([A-Za-z][\w\s]+):\s*(.+)(?:\n|$)/g;
      let match;

      while ((match = categoryRegex.exec(response)) !== null) {
        const category = match[1].trim();
        const termsString = match[2].trim();
        const extractedTerms = termsString.split(',').map(term => term.trim());

        // Limiter le nombre de termes par catégorie
        const limitedTerms = extractedTerms.slice(0, maxTermsPerCategory);

        // Pour chaque terme extrait, vérifier s'il existe une correspondance exacte ou partielle dans le thésaurus
        const categoryTerms = [];

        for (const term of limitedTerms) {
          // Rechercher une correspondance exacte d'abord
          const exactMatch = this.thesaurusTerms.find(t =>
            t.name.toLowerCase() === term.toLowerCase()
          );

          if (exactMatch) {
            categoryTerms.push({
              term: term,
              thesaurusTerm: exactMatch.name,
              thesaurusId: exactMatch.id,
              matchType: 'exact'
            });
            continue;
          }

          // Si pas de correspondance exacte, rechercher une correspondance partielle
          const partialMatch = this.thesaurusTerms.find(t =>
            t.name.toLowerCase().includes(term.toLowerCase()) ||
            term.toLowerCase().includes(t.name.toLowerCase())
          );

          if (partialMatch) {
            categoryTerms.push({
              term: term,
              thesaurusTerm: partialMatch.name,
              thesaurusId: partialMatch.id,
              matchType: 'partial'
            });
          } else {
            // Aucune correspondance dans le thésaurus
            categoryTerms.push({
              term: term,
              thesaurusTerm: null,
              thesaurusId: null,
              matchType: 'none'
            });
          }
        }

        categoryResults[category] = categoryTerms;
      }

      console.log('Termes géographiques:', categoryResults.GEOGRAPHIQUE);
      console.log('Termes thématiques:', categoryResults.THEMATIQUE);
      console.log('Termes typologiques:', categoryResults.TYPOLOGIE);

      // Tous les termes combinés pour la recherche dans le thésaurus
      const allTerms = [...categoryResults.GEOGRAPHIQUE, ...categoryResults.THEMATIQUE, ...categoryResults.TYPOLOGIE];

      // Si nous avons un token d'API Laravel, rechercher ces termes dans le thésaurus
      let matchedTermsMap = {
        geographic: [],
        thematic: [],
        typologic: []
      };

      const termsResponse = await laravelApiService.searchTermsInThesaurus(allTerms);

      if (termsResponse.success && termsResponse.terms && termsResponse.terms.length > 0) {
        const matchedTerms = termsResponse.terms;

        // Catégoriser les termes trouvés selon nos catégories initiales
        matchedTermsMap.geographic = matchedTerms.filter(term =>
          categoryResults.GEOGRAPHIQUE.some(geoTerm =>
            term.name.toLowerCase().includes(geoTerm.term.toLowerCase())
          )
        );

        matchedTermsMap.thematic = matchedTerms.filter(term =>
          categoryResults.THEMATIQUE.some(themeTerm =>
            term.name.toLowerCase().includes(themeTerm.term.toLowerCase())
          )
        );

        matchedTermsMap.typologic = matchedTerms.filter(term =>
          categoryResults.TYPOLOGIE.some(typeTerm =>
            term.name.toLowerCase().includes(typeTerm.term.toLowerCase())
          )
        );
      }

      return {
        success: true,
        extractedKeywords: {
          geographic: categoryResults.GEOGRAPHIQUE,
          thematic: categoryResults.THEMATIQUE,
          typologic: categoryResults.TYPOLOGIE
        },
        matchedTerms: matchedTermsMap,
        allExtractedKeywords: allTerms
      };
    } catch (error) {
      console.error('Erreur lors de l\'extraction des mots-clés catégorisés:', error);
      return {
        success: false,
        error: error.message,
        extractedKeywords: {
          geographic: [],
          thematic: [],
          typologic: []
        },
        matchedTerms: {
          geographic: [],
          thematic: [],
          typologic: []
        },
        allExtractedKeywords: []
      };
    }
  }

  /**
   * Recherche de mots-clés dans le thésaurus
   * @param {string} content - Le contenu textuel à analyser
   * @param {string} modelName - Le nom du modèle à utiliser
   * @param {number} maxTerms - Nombre maximum de termes à extraire
   * @returns {Promise<Object>} Les mots-clés extraits et les termes correspondants
   */
  async searchThesaurusTerms(content, modelName = this.defaultModel, maxTerms = 5) {
    try {
      // Générer des mots-clés à partir du contenu
      const prompt = `
Extrayez jusqu'à ${maxTerms} mots-clés ou concepts importants du texte suivant.
Donnez uniquement les mots-clés, un par ligne, sans numérotation ni ponctuation.
Ne donnez que des termes précis qui pourraient se trouver dans un thésaurus documentaire.

Texte: "${content}"
`;

      const result = await ollamaService.generate(
        prompt,
        modelName,
        { temperature: 0.2 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de génération des mots-clés');
      }

      // Extraire les mots-clés générés
      const keywords = result.content
        .split('\n')
        .map(kw => kw.trim())
        .filter(kw => kw.length > 0);

      console.log('Mots-clés extraits:', keywords);

      // Rechercher ces termes dans le thésaurus
      const termsResponse = await laravelApiService.searchTermsInThesaurus(keywords);

      return {
        success: true,
        extractedKeywords: keywords,
        matchedTerms: termsResponse.success ? termsResponse.terms : []
      };
    } catch (error) {
      console.error('Erreur lors de l\'extraction des mots-clés:', error);
      return {
        success: false,
        error: error.message,
        extractedKeywords: [],
        matchedTerms: []
      };
    }
  }

  /**
   * Formatter un titre au format objet:action(typologie)
   * @param {string} title - Le titre à formater
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<Object>} Le titre formaté
   */
  async formatTitle(title, modelName = this.defaultModel) {
    try {
      const prompt = `
Reformatez le titre suivant au format "objet:action(typologie)" où:
- objet: représente le sujet principal du document
- action: représente l'activité ou l'action principale
- typologie: représente le type de document

Exemple:
"Procès verbal de la réunion du 25 janvier 2024" -> "Réunion:procès-verbal(administratif)"
"Correspondance avec le Ministère concernant les subventions" -> "Subventions:correspondance(administratif)"

Titre original: "${title}"

Retournez uniquement le titre reformaté, sans explications ni commentaires.
`;

      const result = await ollamaService.generate(
        prompt,
        modelName,
        { temperature: 0.3 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec du formatage du titre');
      }

      // Nettoyer le titre formaté
      const formattedTitle = result.content.trim();

      return {
        success: true,
        originalTitle: title,
        formattedTitle: formattedTitle
      };
    } catch (error) {
      console.error('Erreur lors du formatage du titre:', error);
      return {
        success: false,
        error: error.message,
        originalTitle: title,
        formattedTitle: null
      };
    }
  }

  /**
   * Crée un prompt pour enrichir la description d'un record
   * @param {Object} recordData - Les données du record
   * @returns {string} Le prompt pour l'enrichissement
   */
  _createEnrichmentPrompt(recordData) {
    return `Enrichissez la description suivante d'un document d'archives.
Améliorez la clarté et l'exhaustivité tout en conservant les informations factuelles.
Ajoutez des éléments de contexte historique pertinents si nécessaire.

${this._formatRecordDataForPrompt(recordData, true)}

Veuillez produire une description enrichie qui peut remplacer la description actuelle.`;
  }

  /**
   * Crée un prompt pour résumer la description d'un record
   * @param {Object} recordData - Les données du record
   * @returns {string} Le prompt pour le résumé
   */
  _createSummaryPrompt(recordData) {
    return `Résumez la description suivante d'un document d'archives en un paragraphe concis.
Conservez les informations les plus importantes et pertinentes.

${this._formatRecordDataForPrompt(recordData)}`;
  }

  /**
   * Crée un prompt pour analyser un record
   * @param {Object} recordData - Les données du record
   * @returns {string} Le prompt pour l'analyse
   */
  _createAnalysisPrompt(recordData) {
    return `Analysez ce document d'archives et fournissez:
1. Un résumé en une phrase
2. Les thèmes principaux
3. La pertinence historique
4. Des suggestions pour d'autres documents potentiellement liés

${this._formatRecordDataForPrompt(recordData)}`;
  }

  /**
   * Formate les données d'un record pour un prompt
   * @param {Object} recordData - Les données du record
   * @param {boolean} includeNotes - Si les notes doivent être incluses
   * @returns {string} Les données formatées
   */
  _formatRecordDataForPrompt(recordData, includeNotes = false) {
    let formattedData = `Titre: ${recordData.name}\n`;

    if (recordData.code) formattedData += `Code: ${recordData.code}\n`;
    if (recordData.date_start) formattedData += `Date de début: ${recordData.date_start}\n`;
    if (recordData.date_end) formattedData += `Date de fin: ${recordData.date_end}\n`;
    if (recordData.content) formattedData += `Description actuelle: ${recordData.content}\n`;
    if (recordData.biographical_history) formattedData += `Contexte biographique: ${recordData.biographical_history}\n`;
    if (recordData.archival_history) formattedData += `Historique archivistique: ${recordData.archival_history}\n`;

    if (includeNotes && recordData.note) {
      formattedData += `Notes: ${recordData.note}\n`;
    }

    return formattedData;
  }

  /**
   * Détermine la température appropriée pour un mode donné
   * @param {string} mode - Le mode d'enrichissement
   * @returns {number} La température à utiliser pour ce mode
   */
  _getTemperatureForMode(mode) {
    switch (mode) {
      case 'summarize': return 0.5;
      case 'analyze': return 0.6;
      default: return 0.7; // mode 'enrich' et autres
    }
  }

  /**
   * Enrichir, résumer ou analyser un record
   * @param {Object} recordData - Les données du record
   * @param {string} modelName - Le nom du modèle à utiliser
   * @param {string} mode - Le mode d'enrichissement (enrich, summarize, analyze)
   * @returns {Promise<Object>} Le contenu enrichi
   */
  async processRecord(recordData, modelName = this.defaultModel, mode = 'enrich') {
    try {
      // Sélectionner le prompt approprié en fonction du mode
      let prompt;

      switch (mode) {
        case 'enrich':
          prompt = this._createEnrichmentPrompt(recordData);
          break;
        case 'summarize':
          prompt = this._createSummaryPrompt(recordData);
          break;
        case 'analyze':
          prompt = this._createAnalysisPrompt(recordData);
          break;
        default:
          throw new Error(`Mode d'enrichissement non reconnu: ${mode}`);
      }

      // Déterminer la température en fonction du mode
      const temperature = this._getTemperatureForMode(mode);

      // Générer le contenu avec Ollama
      const result = await ollamaService.generate(
        prompt,
        modelName,
        { temperature }
      );

      if (!result.success) {
        throw new Error(result.error || `Échec du traitement en mode ${mode}`);
      }

      return {
        success: true,
        enrichedContent: result.content,
        mode,
        model: modelName,
        stats: result.stats
      };
    } catch (error) {
      console.error(`Erreur lors du traitement en mode ${mode}:`, error);
      return {
        success: false,
        error: error.message,
        mode,
        model: modelName
      };
    }
  }

  /**
   * Assigner des termes catégorisés à un record
   * @param {number} recordId - ID du record
   * @param {Object} matchedTerms - Termes trouvés par catégorie
   * @returns {Promise<Object>} Résultat de l'assignation
   */
  async assignTermsToRecord(recordId, matchedTerms) {
    return await laravelApiService.assignTermsToRecord(recordId, matchedTerms);
  }

  /**
   * Analyse un bordereau de transfert et suggère des métadonnées améliorées
   * @param {Object} slipData - Les données du bordereau de transfert
   * @param {Array} records - Les enregistrements associés au bordereau
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<Object>} Les suggestions d'amélioration pour le bordereau
   */
  async enhanceTransferSlip(slipData, records, modelName = this.defaultModel) {
    try {
      // Construire une représentation du bordereau et de ses enregistrements
      const recordsText = records.map(record => {
        return `- ${record.code}: ${record.name} (${record.date_start || ''} - ${record.date_end || ''})
          ${record.content ? `Description: ${record.content.substring(0, 100)}...` : ''}`;
      }).join('\n');

      const prompt = `
Vous êtes un expert en archivistique. Analysez ce bordereau de transfert d'archives et ses documents associés.
Fournissez des suggestions pour:
1. Améliorer la description du bordereau
2. Des mots-clés pertinents pour caractériser l'ensemble du versement
3. Des recommandations sur la classification archivistique adaptée
4. Des termes du thésaurus qui pourraient être associés à l'ensemble

Informations sur le bordereau:
Titre: ${slipData.name}
Code: ${slipData.code}
Description: ${slipData.description || ''}
Organisation d'origine: ${slipData.officer_organisation_name || 'Non spécifiée'}
Organisation destinataire: ${slipData.user_organisation_name || 'Non spécifiée'}

Documents contenus dans le bordereau:
${recordsText}

Répondez avec la structure suivante:
{"amélioration_description": "...", "mots_clés": ["...", "..."], "classification_recommandée": "...", "termes_thésaurus_suggérés": ["...", "..."]}
`;

      const result = await ollamaService.generate(
        prompt,
        modelName,
        { temperature: 0.3 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de l\'analyse du bordereau de transfert');
      }

      // Tenter de parser la réponse en JSON
      let parsedResponse;
      try {
        // Rechercher un objet JSON valide dans la réponse
        const jsonMatch = result.content.match(/\{[\s\S]*\}/);
        if (jsonMatch) {
          parsedResponse = JSON.parse(jsonMatch[0]);
        } else {
          throw new Error('Format de réponse non reconnu');
        }
      } catch (parseError) {
        console.error('Erreur lors du parsing de la réponse JSON:', parseError);

        // Analyse manuelle si le parsing JSON échoue
        const descriptionMatch = result.content.match(/amélioration_description["\s:]+([^"]+)/i);
        const keywordsMatch = result.content.match(/mots_clés["\s:]+\[(.*?)\]/is);
        const classificationMatch = result.content.match(/classification_recommandée["\s:]+([^"]+)/i);
        const termsMatch = result.content.match(/termes_thésaurus_suggérés["\s:]+\[(.*?)\]/is);

        parsedResponse = {
          amélioration_description: descriptionMatch ? descriptionMatch[1].trim() : '',
          mots_clés: keywordsMatch ? keywordsMatch[1].split(',').map(k => k.trim().replace(/"/g, '')) : [],
          classification_recommandée: classificationMatch ? classificationMatch[1].trim() : '',
          termes_thésaurus_suggérés: termsMatch ? termsMatch[1].split(',').map(t => t.trim().replace(/"/g, '')) : []
        };
      }

      return {
        success: true,
        originalSlip: {
          id: slipData.id,
          name: slipData.name,
          code: slipData.code,
          description: slipData.description
        },
        suggestions: parsedResponse,
        model: modelName
      };
    } catch (error) {
      console.error('Erreur lors de l\'enrichissement du bordereau de transfert:', error);
      return {
        success: false,
        error: error.message,
        originalSlip: {
          id: slipData.id,
          name: slipData.name,
          code: slipData.code
        }
      };
    }
  }

  /**
   * Valide la conformité d'un ensemble de documents pour le transfert
   * @param {Array} records - Les enregistrements à valider pour le transfert
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<Object>} Résultats de la validation des documents
   */
  async validateTransferRecords(records, modelName = this.defaultModel) {
    try {
      // Créer une liste structurée des enregistrements pour l'analyse
      const recordsList = records.map(record => {
        return {
          id: record.id,
          code: record.code,
          name: record.name,
          dateStart: record.date_start,
          dateEnd: record.date_end,
          content: record.content ? record.content.substring(0, 150) + "..." : "Non spécifié",
          level: record.level_name || "Non spécifié",
          completeness: this._calculateRecordCompleteness(record)
        };
      });

      const prompt = `
En tant qu'expert en archivistique et transfert d'archives, analysez ces documents destinés au transfert et identifiez:

1. Les documents qui semblent incomplets ou problématiques pour le transfert
2. Les incohérences ou anomalies potentielles dans les métadonnées (dates manquantes, descriptions incomplètes, etc.)
3. Des recommandations pour améliorer la qualité des métadonnées avant le transfert
4. Une évaluation globale de la cohérence de l'ensemble (Est-ce que ces documents forment un ensemble cohérent?)

Voici les documents à évaluer:
${JSON.stringify(recordsList, null, 2)}

Répondez au format JSON avec la structure suivante:
{
  "documents_problematiques": [
    {"id": "...", "code": "...", "problemes": ["...", "..."]},
    ...
  ],
  "coherence_globale": "évaluation de la cohérence de l'ensemble",
  "recommandations": ["...", "..."],
  "evaluation_generale": "score sur 10 et commentaire général"
}
`;

      const result = await ollamaService.generate(
        prompt,
        modelName,
        { temperature: 0.4 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de la validation des documents');
      }

      // Parser la réponse JSON
      let validationResults;
      try {
        // Extraire le JSON de la réponse
        const jsonMatch = result.content.match(/\{[\s\S]*\}/);
        if (jsonMatch) {
          validationResults = JSON.parse(jsonMatch[0]);
        } else {
          throw new Error('Format de réponse non reconnu');
        }
      } catch (parseError) {
        console.error('Erreur lors du parsing de la réponse:', parseError);

        // Structure par défaut en cas d'échec du parsing
        validationResults = {
          documents_problematiques: [],
          coherence_globale: "Impossible d'évaluer la cohérence (erreur de format)",
          recommandations: ["Vérifier manuellement les métadonnées des documents"],
          evaluation_generale: "Impossible de générer une évaluation complète"
        };
      }

      return {
        success: true,
        totalRecords: records.length,
        validationResults,
        model: modelName
      };
    } catch (error) {
      console.error('Erreur lors de la validation des documents pour le transfert:', error);
      return {
        success: false,
        error: error.message,
        totalRecords: records ? records.length : 0
      };
    }
  }

  /**
   * Calcule le niveau de complétude d'un enregistrement (métrique interne)
   * @param {Object} record - L'enregistrement à évaluer
   * @returns {number} Score de complétude entre 0 et 1
   * @private
   */
  _calculateRecordCompleteness(record) {
    // Liste des champs importants pour évaluer la complétude
    const importantFields = [
      'code', 'name', 'date_start', 'date_end', 'content',
      'level_id', 'support_id', 'activity_id'
    ];

    // Champs critiques qui doivent absolument être présents
    const criticalFields = ['code', 'name'];

    let score = 0;
    let fieldsChecked = 0;

    // Vérifier chaque champ
    for (const field of importantFields) {
      if (record[field]) {
        // Les champs critiques valent double
        score += criticalFields.includes(field) ? 2 : 1;
      }
      fieldsChecked += criticalFields.includes(field) ? 2 : 1;
    }

    // Si les champs de contenu sont substantiels (plus de 100 caractères), bonus
    if (record.content && record.content.length > 100) {
      score += 0.5;
      fieldsChecked += 0.5;
    }

    return fieldsChecked > 0 ? score / fieldsChecked : 0;
  }

  /**
   * Suggère un plan de classement pour un ensemble de records
   * @param {Array} records - Les enregistrements à classifier
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<Object>} Suggestions de classification hiérarchique
   */
  async suggestClassificationScheme(records, modelName = this.defaultModel) {
    try {
      // Préparer un résumé des records pour le prompt
      const recordSummaries = records.map(record => {
        return {
          id: record.id,
          code: record.code,
          name: record.name,
          content: record.content ?
            (record.content.length > 200 ?
              record.content.substring(0, 200) + "..." :
              record.content) :
            "Non spécifié",
          dates: `${record.date_start || ''} - ${record.date_end || ''}`.trim(),
          type: record.activity_name || "Non spécifié"
        };
      });

      const prompt = `
En tant qu'expert archiviste, proposez un plan de classement hiérarchique pour cet ensemble de documents.
Créez une structure organisée avec séries, sous-séries et catégories selon les normes archivistiques.

Examinez attentivement les documents suivants:
${JSON.stringify(recordSummaries, null, 2)}

Créez un plan de classement qui:
1. Regroupe les documents de façon logique et cohérente
2. Utilise une notation alphanumérique standard (ex: 1A, 1B, 2A, etc.)
3. Propose 2-3 niveaux de profondeur (séries, sous-séries, etc.)
4. Assigne chaque document à une catégorie appropriée

Répondez au format JSON avec la structure suivante:
{
  "plan_classement": [
    {
      "code": "A",
      "intitule": "Titre de la série",
      "description": "Description de la série",
      "sous_series": [
        {
          "code": "A1",
          "intitule": "Titre de la sous-série",
          "description": "Description de la sous-série",
          "documents_associes": [1, 2, 5] // IDs des documents associés
        },
        ...
      ]
    },
    ...
  ],
  "recommandations": ["recommandation 1", "recommandation 2", ...]
}
`;

      const result = await ollamaService.generate(
        prompt,
        modelName,
        { temperature: 0.4 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de la génération du plan de classement');
      }

      // Parser la réponse JSON
      let classificationScheme;
      try {
        // Extraire le JSON de la réponse
        const jsonMatch = result.content.match(/\{[\s\S]*\}/);
        if (jsonMatch) {
          classificationScheme = JSON.parse(jsonMatch[0]);
        } else {
          throw new Error('Format de réponse non reconnu');
        }
      } catch (parseError) {
        console.error('Erreur lors du parsing du plan de classement:', parseError);

        // Structure par défaut en cas d'échec du parsing
        classificationScheme = {
          plan_classement: [],
          recommandations: ["La génération automatique du plan a échoué, veuillez élaborer un plan manuellement"]
        };
      }

      return {
        success: true,
        totalRecords: records.length,
        classificationScheme,
        model: modelName
      };
    } catch (error) {
      console.error('Erreur lors de la génération du plan de classement:', error);
      return {
        success: false,
        error: error.message,
        totalRecords: records ? records.length : 0
      };
    }
  }

  /**
   * Génère un rapport détaillé sur un transfert d'archives
   * @param {Object} slipData - Les données du bordereau de transfert
   * @param {Array} records - Les enregistrements associés au bordereau
   * @param {Object} validationResults - Les résultats de validation (optionnel)
   * @param {string} modelName - Le nom du modèle à utiliser
   * @returns {Promise<Object>} Le rapport de transfert généré
   */
  async generateTransferReport(slipData, records, validationResults = null, modelName = this.defaultModel) {
    try {
      // Statistiques de base sur les documents
      const stats = {
        totalRecords: records.length,
        totalSize: records.reduce((acc, record) => acc + (parseFloat(record.width) || 0), 0),
        dateRange: this._calculateDateRange(records),
        recordsWithAttachments: records.filter(r => r.attachments && r.attachments.length > 0).length,
        typeDistribution: this._calculateTypeDistribution(records),
      };

      // Construire le contexte pour le prompt
      let validationContext = "";
      if (validationResults && validationResults.success) {
        validationContext = `
Résultats de la validation:
- Documents problématiques: ${validationResults.validationResults.documents_problematiques.length}
- Cohérence globale: ${validationResults.validationResults.coherence_globale}
- Évaluation générale: ${validationResults.validationResults.evaluation_generale}`;
      }

      const prompt = `
En tant qu'expert archiviste, générez un rapport détaillé et professionnel sur ce transfert d'archives.
Le rapport doit être structuré, informatif et précis, utilisable dans un contexte institutionnel.

Détails du transfert:
- Bordereau: ${slipData.code} - ${slipData.name}
- Description: ${slipData.description || 'Non spécifiée'}
- Organisation émettrice: ${slipData.officer_organisation_name || 'Non spécifiée'}
- Organisation destinataire: ${slipData.user_organisation_name || 'Non spécifiée'}
- Date du transfert: ${slipData.created_at || 'Non spécifiée'}
- État: ${slipData.is_received ? 'Reçu' : 'Non reçu'}, ${slipData.is_approved ? 'Approuvé' : 'Non approuvé'}, ${slipData.is_integrated ? 'Intégré' : 'Non intégré'}

Statistiques des documents:
- Nombre total de documents: ${stats.totalRecords}
- Volume total: ${stats.totalSize} ml (mètres linéaires)
- Période couverte: ${stats.dateRange}
- Documents avec pièces jointes: ${stats.recordsWithAttachments}
- Répartition par type: ${JSON.stringify(stats.typeDistribution)}
${validationContext}

Échantillon des documents (3 premiers):
${records.slice(0, 3).map(r => `- ${r.code}: ${r.name} (${r.date_start || ''} - ${r.date_end || ''})`).join('\n')}

Votre rapport doit inclure:
1. Un résumé exécutif du transfert
2. Une analyse détaillée du contenu et de son importance
3. Des observations sur la qualité et la complétude des métadonnées
4. Des recommandations pour le traitement et la conservation
5. Une conclusion sur la valeur archivistique de l'ensemble

Générez un rapport structuré en français soutenu, adapté à un contexte professionnel d'archivage.
`;

      const result = await ollamaService.generate(
        prompt,
        modelName,
        { temperature: 0.5 }
      );

      if (!result.success) {
        throw new Error(result.error || 'Échec de la génération du rapport de transfert');
      }

      return {
        success: true,
        slipData: {
          id: slipData.id,
          code: slipData.code,
          name: slipData.name
        },
        reportContent: result.content,
        statistics: stats,
        model: modelName,
        generatedAt: new Date().toISOString()
      };
    } catch (error) {
      console.error('Erreur lors de la génération du rapport de transfert:', error);
      return {
        success: false,
        error: error.message,
        slipData: {
          id: slipData.id,
          code: slipData.code,
          name: slipData.name
        }
      };
    }
  }

  /**
   * Calcule la période couverte par un ensemble de records
   * @param {Array} records - Les enregistrements à analyser
   * @returns {string} Période au format "YYYY-YYYY"
   * @private
   */
  _calculateDateRange(records) {
    let minYear = 9999;
    let maxYear = 0;

    records.forEach(record => {
      if (record.date_start) {
        const startYear = parseInt(record.date_start.substring(0, 4), 10);
        if (!isNaN(startYear) && startYear < minYear) {
          minYear = startYear;
        }
      }

      if (record.date_end) {
        const endYear = parseInt(record.date_end.substring(0, 4), 10);
        if (!isNaN(endYear) && endYear > maxYear) {
          maxYear = endYear;
        }
      }
    });

    if (minYear === 9999 || maxYear === 0) {
      return "Période indéterminée";
    }

    return `${minYear}-${maxYear}`;
  }

  /**
   * Calcule la distribution des types de documents
   * @param {Array} records - Les enregistrements à analyser
   * @returns {Object} Distribution des types
   * @private
   */
  _calculateTypeDistribution(records) {
    const distribution = {};

    records.forEach(record => {
      const type = record.activity_name || "Non spécifié";
      distribution[type] = (distribution[type] || 0) + 1;
    });

    return distribution;
  }
}

module.exports = new EnrichmentService();

// Serveur MCP pour l'enrichissement des descriptions de records via Ollama
const express = require('express');
const axios = require('axios');
const { z } = require('zod');
const dotenv = require('dotenv');

// Charger les variables d'environnement
dotenv.config({ path: '../.env' });

const app = express();
app.use(express.json());

// Configuration
const OLLAMA_BASE_URL = process.env.OLLAMA_BASE_URL || 'http://localhost:11434';
const PORT = process.env.MCP_PORT || 3000;
const DEFAULT_MODEL = process.env.OLLAMA_DEFAULT_MODEL || 'llama3';
const LARAVEL_API_URL = process.env.LARAVEL_API_URL || 'http://localhost/shelves/api';
const LARAVEL_API_TOKEN = process.env.LARAVEL_API_TOKEN;

// Schéma de validation pour la requête d'enrichissement
const EnrichRequestSchema = z.object({
  recordId: z.number().int().positive(),
  recordData: z.object({
    id: z.number().int().positive(),
    code: z.string().optional(),
    name: z.string(),
    content: z.string().optional(),
    biographical_history: z.string().optional(),
    archival_history: z.string().optional(),
    note: z.string().optional(),
    // Autres champs optionnels
    date_start: z.string().optional(),
    date_end: z.string().optional(),
  }),
  modelName: z.string().optional().default(DEFAULT_MODEL),
  mode: z.enum(['enrich', 'summarize', 'analyze', 'format_title', 'extract_keywords', 'categorized_keywords']).default('enrich'),
});

// Schéma pour la recherche de termes dans le thésaurus
const ThesaurusSearchSchema = z.object({
  recordId: z.number().int().positive(),
  content: z.string(),
  modelName: z.string().optional().default(DEFAULT_MODEL),
  maxTerms: z.number().int().positive().optional().default(5),
});

// Schéma pour l'extraction de mots-clés catégorisés
const CategorizedKeywordsSchema = z.object({
  recordId: z.number().int().positive(),
  recordData: z.object({
    id: z.number().int().positive(),
    name: z.string(),
    content: z.string().optional(),
    biographical_history: z.string().optional(),
    archival_history: z.string().optional(),
    note: z.string().optional(),
    date_start: z.string().optional(),
    date_end: z.string().optional(),
  }),
  modelName: z.string().optional().default(DEFAULT_MODEL),
  autoAssign: z.boolean().optional().default(false),
});

// Schéma pour l'assignation de termes à un record
const AssignTermsSchema = z.object({
  recordId: z.number().int().positive(),
  terms: z.array(z.object({
    id: z.number().int().positive(),
    name: z.string(),
    type: z.string()
  })),
});

// Middleware d'authentification API
app.use('/api', (req, res, next) => {
  const authHeader = req.headers.authorization;

  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'Token API manquant ou invalide' });
  }

  const token = authHeader.split(' ')[1];

  if (token !== LARAVEL_API_TOKEN) {
    return res.status(401).json({ error: 'Token API invalide' });
  }

  next();
});

// Recherche et catégorisation de mots-clés (géographiques, thématiques, typologiques)
async function extractCategorizedKeywords(content, modelName, maxTermsPerCategory = 3) {
  try {
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

    const ollamaResponse = await axios.post(`${OLLAMA_BASE_URL}/api/generate`, {
      model: modelName,
      prompt: prompt,
      stream: false,
      options: {
        temperature: 0.2, // Température basse pour des résultats plus précis
        top_p: 0.95,
        top_k: 40,
      }
    });

    if (!ollamaResponse.data || !ollamaResponse.data.response) {
      throw new Error('Réponse invalide d\'Ollama');
    }

    // Traiter la réponse pour extraire les mots-clés catégorisés
    const response = ollamaResponse.data.response;

    // Extraire les sections par catégorie
    const geoSection = response.match(/GEOGRAPHIQUE:[\s\S]*?(?=THEMATIQUE:|$)/i)?.[0] || '';
    const themeSection = response.match(/THEMATIQUE:[\s\S]*?(?=TYPOLOGIE:|$)/i)?.[0] || '';
    const typeSection = response.match(/TYPOLOGIE:[\s\S]*?(?=$)/i)?.[0] || '';

    // Extraire les termes de chaque section
    const extractTerms = (section) => {
      const terms = [];
      const lines = section.split('\n');
      for (const line of lines) {
        // Chercher les lignes qui commencent par un tiret ou une étoile
        const match = line.match(/^[-*]\s+(.+)$/);
        if (match && match[1]) {
          // Nettoyer le terme
          const term = match[1].trim().replace(/[\[\]]/g, '');
          if (term) terms.push(term);
        }
      }
      return terms;
    };

    const geoTerms = extractTerms(geoSection);
    const themeTerms = extractTerms(themeSection);
    const typeTerms = extractTerms(typeSection);

    console.log('Termes géographiques:', geoTerms);
    console.log('Termes thématiques:', themeTerms);
    console.log('Termes typologiques:', typeTerms);

    // Tous les termes combinés pour la recherche dans le thésaurus
    const allTerms = [...geoTerms, ...themeTerms, ...typeTerms];

    // Si nous avons un token d'API Laravel, rechercher ces termes dans le thésaurus
    let matchedTermsMap = {
      geographic: [],
      thematic: [],
      typologic: []
    };

    if (LARAVEL_API_TOKEN && allTerms.length > 0) {
      try {
        const termsResponse = await axios.post(
          `${LARAVEL_API_URL}/terms/search`,
          { keywords: allTerms },
          {
            headers: {
              'Authorization': `Bearer ${LARAVEL_API_TOKEN}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          }
        );

        if (termsResponse.data && termsResponse.data.terms) {
          const matchedTerms = termsResponse.data.terms;

          // Catégoriser les termes trouvés selon nos catégories initiales
          matchedTermsMap.geographic = matchedTerms.filter(term =>
            geoTerms.some(geoTerm =>
              term.name.toLowerCase().includes(geoTerm.toLowerCase())
            )
          );

          matchedTermsMap.thematic = matchedTerms.filter(term =>
            themeTerms.some(themeTerm =>
              term.name.toLowerCase().includes(themeTerm.toLowerCase())
            )
          );

          matchedTermsMap.typologic = matchedTerms.filter(term =>
            typeTerms.some(typeTerm =>
              term.name.toLowerCase().includes(typeTerm.toLowerCase())
            )
          );
        }
      } catch (error) {
        console.error('Erreur lors de la recherche dans le thésaurus:', error.message);
        // En cas d'erreur, on continue avec les termes extraits
      }
    }

    return {
      success: true,
      extractedKeywords: {
        geographic: geoTerms,
        thematic: themeTerms,
        typologic: typeTerms
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

// Associer des termes à un record via l'API Laravel
async function assignTermsToRecord(recordId, termIds) {
  if (!LARAVEL_API_TOKEN || !termIds || termIds.length === 0) {
    return {
      success: false,
      error: 'Pas de termes à assigner ou token API manquant'
    };
  }

  try {
    const response = await axios.post(
      `${LARAVEL_API_URL}/records/${recordId}/terms`,
      { termIds },
      {
        headers: {
          'Authorization': `Bearer ${LARAVEL_API_TOKEN}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    );

    if (response.status === 200 || response.status === 201) {
      return {
        success: true,
        assignedTerms: response.data.assignedTerms || termIds.length,
        message: response.data.message || 'Termes associés avec succès'
      };
    }

    return {
      success: false,
      error: response.data.error || 'Erreur lors de l\'association des termes'
    };
  } catch (error) {
    console.error('Erreur lors de l\'association des termes au record:', error.message);
    return {
      success: false,
      error: error.message
    };
  }
}

// Recherche de termes dans le thésaurus
async function searchThesaurusTerms(content, modelName, maxTerms = 5) {
  try {
    // Générer des mots-clés à partir du contenu
    const prompt = `
Extrayez jusqu'à ${maxTerms} mots-clés ou concepts importants du texte suivant.
Donnez uniquement les mots-clés, un par ligne, sans numérotation ni ponctuation.
Ne donnez que des termes précis qui pourraient se trouver dans un thésaurus documentaire.

Texte: "${content}"
`;

    const ollamaResponse = await axios.post(`${OLLAMA_BASE_URL}/api/generate`, {
      model: modelName,
      prompt: prompt,
      stream: false,
      options: {
        temperature: 0.2, // Température basse pour des résultats plus précis
        top_p: 0.9,
        top_k: 40,
      }
    });

    if (!ollamaResponse.data || !ollamaResponse.data.response) {
      throw new Error('Réponse invalide d\'Ollama');
    }

    // Extraire les mots-clés générés
    const keywords = ollamaResponse.data.response
      .split('\n')
      .map(kw => kw.trim())
      .filter(kw => kw.length > 0);

    console.log('Mots-clés extraits:', keywords);

    // Si nous avons un token d'API Laravel, rechercher ces termes dans le thésaurus
    if (LARAVEL_API_TOKEN) {
      try {
        const termsResponse = await axios.post(
          `${LARAVEL_API_URL}/terms/search`,
          { keywords },
          {
            headers: {
              'Authorization': `Bearer ${LARAVEL_API_TOKEN}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          }
        );

        if (termsResponse.data && termsResponse.data.terms) {
          return {
            extractedKeywords: keywords,
            matchedTerms: termsResponse.data.terms,
            success: true
          };
        }
      } catch (error) {
        console.error('Erreur lors de la recherche dans le thésaurus:', error.message);
        // En cas d'erreur, on retourne quand même les mots-clés extraits
      }
    }

    return {
      extractedKeywords: keywords,
      matchedTerms: [],
      success: true
    };

  } catch (error) {
    console.error('Erreur lors de l\'extraction des mots-clés:', error);
    return {
      extractedKeywords: [],
      matchedTerms: [],
      success: false,
      error: error.message
    };
  }
}

// Formater un titre au format objet:action(typologie)
async function formatRecordTitle(title, modelName) {
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

    const ollamaResponse = await axios.post(`${OLLAMA_BASE_URL}/api/generate`, {
      model: modelName,
      prompt: prompt,
      stream: false,
      options: {
        temperature: 0.3,
        top_p: 0.9,
        top_k: 40,
      }
    });

    if (!ollamaResponse.data || !ollamaResponse.data.response) {
      throw new Error('Réponse invalide d\'Ollama');
    }

    // Nettoyer le titre formaté
    const formattedTitle = ollamaResponse.data.response.trim();

    return {
      originalTitle: title,
      formattedTitle: formattedTitle,
      success: true
    };

  } catch (error) {
    console.error('Erreur lors du formatage du titre:', error);
    return {
      originalTitle: title,
      formattedTitle: null,
      success: false,
      error: error.message
    };
  }
}

// Fonction pour extraire les mots-clés catégorisés d'un record
async function extractCategorizedKeywordsFromRecord(recordData, modelName = DEFAULT_MODEL) {
  const combinedText = [
    recordData.name,
    recordData.content,
    recordData.biographical_history,
    recordData.archival_history,
    recordData.note
  ].filter(Boolean).join(' ');

  if (combinedText.trim().length === 0) {
    return {
      message: "Aucun contenu à analyser",
      keywords: {
        geographic: [],
        thematic: [],
        typology: []
      }
    };
  }

  const prompt = `
    Extrait du texte suivant des mots-clés pertinents, classés selon trois catégories:
    1. Géographique: lieux, pays, régions, villes, etc.
    2. Thématique: sujets, domaines, concepts, etc.
    3. Typologique: types de documents ou d'archives, formats, etc.

    Pour chaque catégorie, extrais maximum 5 mots-clés, les plus pertinents et représentatifs.
    Retourne uniquement le résultat au format JSON selon le modèle suivant:
    {
      "geographic": ["mot-clé1", "mot-clé2", ...],
      "thematic": ["mot-clé1", "mot-clé2", ...],
      "typology": ["mot-clé1", "mot-clé2", ...]
    }

    Texte à analyser:
    ${combinedText}
  `;

  try {
    const response = await axios.post(`${OLLAMA_BASE_URL}/api/generate`, {
      model: modelName,
      prompt: prompt,
      stream: false,
      options: {
        temperature: 0.3,
        top_p: 0.9,
        top_k: 40,
      }
    });

    // Extraire le JSON de la réponse
    const content = response.data.response.trim();
    let jsonMatch = content.match(/```json\s*([\s\S]*?)\s*```/) ||
                   content.match(/\{[\s\S]*?\}/);

    let jsonContent = jsonMatch ? jsonMatch[0].replace(/```json|```/g, '') : content;

    // Nettoyer le JSON si nécessaire
    if (jsonContent.startsWith('```') && jsonContent.endsWith('```')) {
      jsonContent = jsonContent.substring(3, jsonContent.length - 3);
    }

    const keywords = JSON.parse(jsonContent);

    return {
      message: "Mots-clés catégorisés extraits avec succès",
      keywords: {
        geographic: keywords.geographic || [],
        thematic: keywords.thematic || [],
        typology: keywords.typology || []
      }
    };
  } catch (error) {
    console.error('Erreur lors de l\'extraction des mots-clés catégorisés:', error);
    return {
      message: `Erreur lors de l'extraction des mots-clés catégorisés: ${error.message}`,
      keywords: {
        geographic: [],
        thematic: [],
        typology: []
      }
    };
  }
}

// Fonction pour rechercher des termes dans le thésaurus et les assigner automatiquement à un record
async function searchAndAssignTerms(recordId, keywords) {
  const results = {
    geographic: [],
    thematic: [],
    typology: []
  };

  // Fonction pour rechercher des termes par catégorie
  const searchTermsByCategory = async (category, terms) => {
    if (!terms || terms.length === 0) return [];

    const searchResults = [];

    for (const term of terms) {
      try {
        const response = await axios.get(
          `${LARAVEL_API_URL}/thesaurus/search`,
          {
            params: {
              q: term,
              type: category,
              limit: 2
            },
            headers: {
              'Authorization': `Bearer ${LARAVEL_API_TOKEN}`,
              'Accept': 'application/json'
            }
          }
        );

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
  };

  // Rechercher des termes pour chaque catégorie
  results.geographic = await searchTermsByCategory('geographic', keywords.geographic);
  results.thematic = await searchTermsByCategory('thematic', keywords.thematic);
  results.typology = await searchTermsByCategory('typology', keywords.typology);

  return results;
}

// Fonction pour assigner des termes catégorisés trouvés à un record
async function assignCategorizedTermsToRecord(recordId, terms) {
  // Préparer les termes pour l'assignation
  const flatTerms = [
    ...terms.geographic,
    ...terms.thematic,
    ...terms.typology
  ].filter(term => term && term.id);

  if (flatTerms.length === 0) {
    return {
      message: "Aucun terme à assigner",
      assigned: []
    };
  }

  try {
    // Appeler l'API Laravel pour assigner les termes
    const response = await axios.post(
      `${LARAVEL_API_URL}/records/${recordId}/assign-terms`,
      { terms: flatTerms },
      {
        headers: {
          'Authorization': `Bearer ${LARAVEL_API_TOKEN}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    );

    return {
      message: `${response.data.count || flatTerms.length} termes assignés avec succès`,
      assigned: response.data.assigned || flatTerms
    };
  } catch (error) {
    console.error('Erreur lors de l\'assignation des termes:', error);
    return {
      message: `Erreur lors de l'assignation des termes: ${error.message}`,
      assigned: []
    };
  }
}

// Route pour enrichir une description
app.post('/api/enrich', async (req, res) => {
  try {
    // Valider la requête
    const validatedData = EnrichRequestSchema.parse(req.body);
    const { recordId, recordData, modelName, mode } = validatedData;

    console.log(`Traitement demandé pour l'enregistrement #${recordId} avec le modèle ${modelName} en mode ${mode}`);

    // Traitement spécifique pour le formatage du titre
    if (mode === 'format_title') {
      const result = await formatRecordTitle(recordData.name, modelName);
      return res.json({
        success: result.success,
        recordId,
        originalTitle: result.originalTitle,
        formattedTitle: result.formattedTitle,
        mode,
        model: modelName
      });
    }

    // Traitement spécifique pour l'extraction de mots-clés et recherche thésaurus
    if (mode === 'extract_keywords') {
      // Concaténer toutes les informations disponibles pour une meilleure extraction
      const contentToAnalyze = [
        recordData.name,
        recordData.content,
        recordData.biographical_history,
        recordData.archival_history,
        recordData.note
      ].filter(Boolean).join("\n\n");

      const result = await searchThesaurusTerms(contentToAnalyze, modelName);
      return res.json({
        success: result.success,
        recordId,
        extractedKeywords: result.extractedKeywords,
        matchedTerms: result.matchedTerms,
        mode,
        model: modelName
      });
    }

    // Traitement spécifique pour l'extraction de mots-clés catégorisés
    if (mode === 'categorized_keywords') {
      // Concaténer toutes les informations disponibles pour une meilleure extraction
      const contentToAnalyze = [
        recordData.name,
        recordData.content,
        recordData.biographical_history,
        recordData.archival_history,
        recordData.note
      ].filter(Boolean).join("\n\n");

      const result = await extractCategorizedKeywords(contentToAnalyze, modelName);
      return res.json({
        success: result.success,
        recordId,
        extractedKeywords: result.extractedKeywords,
        matchedTerms: result.matchedTerms,
        allExtractedKeywords: result.allExtractedKeywords,
        mode,
        model: modelName
      });
    }

    // Construire le prompt en fonction du mode choisi
    let prompt = '';

    if (mode === 'enrich') {
      prompt = `Enrichissez la description suivante d'un document d'archives.
Améliorez la clarté et l'exhaustivité tout en conservant les informations factuelles.
Ajoutez des éléments de contexte historique pertinents si nécessaire.

Titre: ${recordData.name}
${recordData.code ? `Code: ${recordData.code}\n` : ''}
${recordData.date_start ? `Date de début: ${recordData.date_start}\n` : ''}
${recordData.date_end ? `Date de fin: ${recordData.date_end}\n` : ''}
${recordData.content ? `Description actuelle: ${recordData.content}\n` : ''}
${recordData.biographical_history ? `Contexte biographique: ${recordData.biographical_history}\n` : ''}
${recordData.archival_history ? `Historique archivistique: ${recordData.archival_history}\n` : ''}
${recordData.note ? `Notes: ${recordData.note}\n` : ''}

Veuillez produire une description enrichie qui peut remplacer la description actuelle.`;
    } else if (mode === 'summarize') {
      prompt = `Résumez la description suivante d'un document d'archives en un paragraphe concis.
Conservez les informations les plus importantes et pertinentes.

Titre: ${recordData.name}
${recordData.code ? `Code: ${recordData.code}\n` : ''}
${recordData.date_start ? `Date de début: ${recordData.date_start}\n` : ''}
${recordData.date_end ? `Date de fin: ${recordData.date_end}\n` : ''}
${recordData.content ? `Description actuelle: ${recordData.content}\n` : ''}
${recordData.biographical_history ? `Contexte biographique: ${recordData.biographical_history}\n` : ''}
${recordData.archival_history ? `Historique archivistique: ${recordData.archival_history}\n` : ''}`;
    } else if (mode === 'analyze') {
      prompt = `Analysez ce document d'archives et fournissez:
1. Un résumé en une phrase
2. Les thèmes principaux
3. La pertinence historique
4. Des suggestions pour d'autres documents potentiellement liés

Titre: ${recordData.name}
${recordData.code ? `Code: ${recordData.code}\n` : ''}
${recordData.date_start ? `Date de début: ${recordData.date_start}\n` : ''}
${recordData.date_end ? `Date de fin: ${recordData.date_end}\n` : ''}
${recordData.content ? `Description actuelle: ${recordData.content}\n` : ''}
${recordData.biographical_history ? `Contexte biographique: ${recordData.biographical_history}\n` : ''}
${recordData.archival_history ? `Historique archivistique: ${recordData.archival_history}\n` : ''}`;
    }

    // Appeler l'API Ollama
    const ollamaResponse = await axios.post(`${OLLAMA_BASE_URL}/api/generate`, {
      model: modelName,
      prompt: prompt,
      stream: false,
      options: {
        temperature: 0.7,
        top_p: 0.9,
        top_k: 40,
      }
    });

    // Vérifier la réponse
    if (ollamaResponse.data && ollamaResponse.data.response) {
      return res.json({
        success: true,
        recordId,
        enrichedContent: ollamaResponse.data.response,
        mode,
        model: modelName,
        stats: {
          totalDuration: ollamaResponse.data.total_duration,
          evalCount: ollamaResponse.data.eval_count,
        }
      });
    } else {
      throw new Error('Réponse invalide d\'Ollama');
    }

  } catch (error) {
    console.error('Erreur lors du traitement:', error);
    res.status(error.status || 500).json({
      success: false,
      error: error.message || 'Erreur serveur interne',
      details: error.response?.data || null
    });
  }
});

// Route pour rechercher des mots-clés dans le thésaurus
app.post('/api/thesaurus/search', async (req, res) => {
  try {
    const validation = ThesaurusSearchSchema.safeParse(req.body);

    if (!validation.success) {
      return res.status(400).json({
        error: 'Données invalides',
        details: validation.error.errors
      });
    }

    const { content, modelName, maxTerms } = validation.data;

    const result = await searchThesaurusTerms(content, modelName, maxTerms);

    return res.json({
      success: result.success,
      extractedKeywords: result.extractedKeywords,
      matchedTerms: result.matchedTerms,
      model: modelName
    });
  } catch (error) {
    console.error('Erreur lors de la recherche dans le thésaurus:', error);
    return res.status(500).json({ error: `Erreur serveur: ${error.message}` });
  }
});

// Route pour extraire des mots-clés catégorisés et optionnellement les assigner
app.post('/api/categorized-keywords', async (req, res) => {
  try {
    const validation = CategorizedKeywordsSchema.safeParse(req.body);

    if (!validation.success) {
      return res.status(400).json({
        error: 'Données invalides',
        details: validation.error.errors
      });
    }

    const { recordId, recordData, modelName, autoAssign } = validation.data;

    // Concaténer toutes les informations disponibles pour une meilleure extraction
    const contentToAnalyze = [
      recordData.name,
      recordData.content,
      recordData.biographical_history,
      recordData.archival_history,
      recordData.note
    ].filter(Boolean).join("\n\n");

    // Extraire les mots-clés catégorisés
    const extractionResult = await extractCategorizedKeywords(contentToAnalyze, modelName);

    let assignmentResult = { message: "Aucune assignation demandée" };
    let thesaurusResults = {};

    // Si l'assignation automatique est demandée
    if (autoAssign && extractionResult.success) {
      // Organiser les termes trouvés par catégorie pour les assigner
      const termsByCategory = {
        geographic: extractionResult.matchedTerms.geographic.map(term => ({id: term.id, name: term.name, type: 'geographic'})),
        thematic: extractionResult.matchedTerms.thematic.map(term => ({id: term.id, name: term.name, type: 'thematic'})),
        typologic: extractionResult.matchedTerms.typologic.map(term => ({id: term.id, name: term.name, type: 'typologic'}))
      };

      // Assigner les termes trouvés au record
      assignmentResult = await assignTermsToRecord(recordId, termsByCategory);
      thesaurusResults = extractionResult.matchedTerms;
    }

    return res.json({
      record_id: recordId,
      model: modelName,
      extraction: extractionResult,
      thesaurus_results: thesaurusResults,
      assignment: autoAssign ? assignmentResult : undefined
    });
  } catch (error) {
    console.error('Erreur lors de l\'extraction et assignation de mots-clés:', error);
    return res.status(500).json({ error: `Erreur serveur: ${error.message}` });
  }
});

// Route pour assigner des termes à un record
app.post('/api/assign-terms', async (req, res) => {
  try {
    const validation = AssignTermsSchema.safeParse(req.body);

    if (!validation.success) {
      return res.status(400).json({
        error: 'Données invalides',
        details: validation.error.errors
      });
    }

    const { recordId, terms } = validation.data;

    // Organiser les termes par catégorie
    const categorizedTerms = {
      geographic: terms.filter(t => t.type === 'geographic'),
      thematic: terms.filter(t => t.type === 'thematic'),
      typology: terms.filter(t => t.type === 'typology')
    };

    // Assigner les termes au record
    const assignmentResult = await assignTermsToRecord(recordId, categorizedTerms);

    return res.json({
      record_id: recordId,
      assignment: assignmentResult
    });
  } catch (error) {
    console.error('Erreur lors de l\'assignation des termes:', error);
    return res.status(500).json({ error: `Erreur serveur: ${error.message}` });
  }
});

// Route pour vérifier la santé du serveur
app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Route pour vérifier la disponibilité d'Ollama
app.get('/api/check-ollama', async (req, res) => {
  try {
    const response = await axios.get(`${OLLAMA_BASE_URL}/api/tags`);
    res.json({
      status: 'ok',
      models: response.data.models || [],
      count: response.data.models?.length || 0
    });
  } catch (error) {
    res.status(500).json({
      status: 'error',
      message: `Impossible de se connecter à Ollama: ${error.message}`,
      details: error.response?.data || null
    });
  }
});

// Démarrer le serveur
app.listen(PORT, () => {
  console.log(`Serveur MCP en cours d'exécution sur le port ${PORT}`);
  console.log(`URL Ollama configurée: ${OLLAMA_BASE_URL}`);
  console.log(`URL API Laravel configurée: ${LARAVEL_API_URL}`);
});

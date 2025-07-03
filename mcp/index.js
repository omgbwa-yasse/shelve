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
  mode: z.enum(['enrich', 'summarize', 'analyze', 'format_title', 'extract_keywords']).default('enrich'),
});

// Schéma pour la recherche de termes dans le thésaurus
const ThesaurusSearchSchema = z.object({
  recordId: z.number().int().positive(),
  content: z.string(),
  modelName: z.string().optional().default(DEFAULT_MODEL),
  maxTerms: z.number().int().positive().optional().default(5),
});

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

// Route dédiée à la recherche de termes dans le thésaurus
app.post('/api/thesaurus-search', async (req, res) => {
  try {
    // Valider la requête
    const validatedData = ThesaurusSearchSchema.parse(req.body);
    const { recordId, content, modelName, maxTerms } = validatedData;

    console.log(`Recherche dans le thésaurus pour l'enregistrement #${recordId} avec ${maxTerms} termes max`);

    const result = await searchThesaurusTerms(content, modelName, maxTerms);
    return res.json({
      success: result.success,
      recordId,
      extractedKeywords: result.extractedKeywords,
      matchedTerms: result.matchedTerms,
      error: result.error
    });
  } catch (error) {
    console.error('Erreur lors de la recherche thésaurus:', error);
    res.status(error.status || 500).json({
      success: false,
      error: error.message || 'Erreur serveur interne',
    });
  }
});

// Route pour formater un titre
app.post('/api/format-title', async (req, res) => {
  try {
    const { title, modelName = DEFAULT_MODEL } = req.body;

    if (!title) {
      return res.status(400).json({
        success: false,
        error: "Le titre est requis"
      });
    }

    console.log(`Formatage du titre: "${title}"`);

    const result = await formatRecordTitle(title, modelName);
    return res.json(result);
  } catch (error) {
    console.error('Erreur lors du formatage du titre:', error);
    res.status(error.status || 500).json({
      success: false,
      error: error.message || 'Erreur serveur interne',
    });
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

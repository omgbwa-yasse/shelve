// Schémas de validation Zod pour le serveur MCP
const { z } = require('zod');

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
  modelName: z.string().optional(),
  mode: z.enum(['enrich', 'summarize', 'analyze', 'format_title', 'extract_keywords', 'categorized_keywords']),
});

// Schéma pour la recherche de termes dans le thésaurus
const ThesaurusSearchSchema = z.object({
  recordId: z.number().int().positive(),
  content: z.string(),
  modelName: z.string().optional(),
  maxTerms: z.number().int().positive().optional(),
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
  modelName: z.string().optional(),
  autoAssign: z.boolean().optional(),
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

// Schéma pour le formatage de titre
const FormatTitleSchema = z.object({
  title: z.string(),
  modelName: z.string().optional()
});

module.exports = {
  EnrichRequestSchema,
  ThesaurusSearchSchema,
  CategorizedKeywordsSchema,
  AssignTermsSchema,
  FormatTitleSchema
};

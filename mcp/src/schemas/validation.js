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
    place: z.string().optional(),
    source: z.string().optional(),
    language: z.string().optional(),
    description: z.string().optional(),
    extent: z.string().optional(),
    level: z.string().optional(),
    parent_id: z.number().int().positive().optional(),
    parent_name: z.string().optional(),
    parent_code: z.string().optional(),
    parent_content: z.string().optional(),
    parent_note: z.string().optional(),
    parent_date_start: z.string().optional(),
    parent_date_end: z.string().optional(),
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

// Schéma pour l'enrichissement d'un bordereau de transfert
const TransferSlipSchema = z.object({
  slipId: z.number().int().positive(),
  modelName: z.string().optional()
});

// Schéma pour la validation des documents d'un bordereau
const ValidateRecordsSchema = z.object({
  slipId: z.number().int().positive(),
  modelName: z.string().optional()
});

// Schéma pour la suggestion de plan de classement
const ClassificationSchema = z.object({
  slipId: z.number().int().positive(),
  modelName: z.string().optional()
});

// Schéma pour la génération de rapport de transfert
const ReportSchema = z.object({
  slipId: z.number().int().positive(),
  modelName: z.string().optional(),
  includeValidation: z.boolean().optional().default(true)
});

// Schéma pour la structure d'un bordereau de transfert (slip)
const SlipSchema = z.object({
  id: z.number().int().positive(),
  code: z.string(),
  name: z.string(),
  description: z.string().optional(),
  officer_organisation_id: z.number().int().positive(),
  officer_organisation_name: z.string().optional(),
  officer_id: z.number().int().positive(),
  user_organisation_id: z.number().int().positive(),
  user_organisation_name: z.string().optional(),
  user_id: z.number().int().optional(),
  slip_status_id: z.number().int().positive(),
  is_received: z.boolean().optional(),
  received_date: z.string().optional().nullable(),
  received_by: z.number().int().optional().nullable(),
  is_approved: z.boolean().optional(),
  approved_date: z.string().optional().nullable(),
  approved_by: z.number().int().optional().nullable(),
  is_integrated: z.boolean().optional(),
  integrated_date: z.string().optional().nullable(),
  integrated_by: z.number().int().optional().nullable(),
  created_at: z.string().optional(),
  updated_at: z.string().optional()
});

// Schéma pour un enregistrement (record) de transfert
const TransferRecordSchema = z.object({
  id: z.number().int().positive(),
  slip_id: z.number().int().positive(),
  code: z.string(),
  name: z.string(),
  date_format: z.string().optional(),
  date_start: z.string().optional(),
  date_end: z.string().optional(),
  date_exact: z.string().optional().nullable(),
  content: z.string().optional(),
  level_id: z.number().int().positive().optional(),
  level_name: z.string().optional(),
  width: z.number().optional(),
  width_description: z.string().optional(),
  support_id: z.number().int().positive().optional(),
  support_name: z.string().optional(),
  activity_id: z.number().int().positive().optional(),
  activity_name: z.string().optional(),
  container_id: z.number().int().optional().nullable(),
  container_name: z.string().optional(),
  creator_id: z.number().int().positive().optional(),
  attachments: z.array(
    z.object({
      id: z.number().int().positive(),
      name: z.string(),
      path: z.string().optional(),
      size: z.number().optional(),
      mime_type: z.string().optional()
    })
  ).optional()
});

module.exports = {
  EnrichRequestSchema,
  ThesaurusSearchSchema,
  CategorizedKeywordsSchema,
  AssignTermsSchema,
  FormatTitleSchema,
  TransferSlipSchema,
  ValidateRecordsSchema,
  ClassificationSchema,
  ReportSchema,
  SlipSchema,
  TransferRecordSchema
};

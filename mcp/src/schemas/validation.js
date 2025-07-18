// Schémas de validation Zod pour le serveur MCP
const { z } = require('zod');
const { AiServiceInterface, RecordsServiceInterface, TermsServiceInterface } = require('./interfaces');

// Schéma pour la requête de formatage de titre
const FormatTitleSchema = z.object({
  recordId: z.number().int().positive(),
  title: z.string(),
  context: z.object({
    administrative_action: z.string().optional(),
    document_type: z.string().optional(),
    date_start: z.string().optional(),
    date_end: z.string().optional()
  }).optional(),
  modelName: z.string().optional()
});

// Schéma pour la génération d'un résumé
const SummarizeRequestSchema = z.object({
  recordId: z.number().int().positive(),
  recordData: z.object({
    id: z.number().int().positive(),
    name: z.string(),
    content: z.string().optional(),
    biographical_history: z.string().optional(),
    archival_history: z.string().optional(),
    note: z.string().optional(),
    description: z.string().optional()
  }),
  maxLength: z.number().int().positive().optional(),
  modelName: z.string().optional()
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

// Schéma pour le formatage de titre est déjà défini plus haut

// Fin des schémas de validation pour le MCP

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
  FormatTitleSchema,
  SummarizeRequestSchema,
  ThesaurusSearchSchema,
  CategorizedKeywordsSchema,
  AssignTermsSchema,
  // Exporter les interfaces
  AiServiceInterface,
  RecordsServiceInterface,
  TermsServiceInterface
};

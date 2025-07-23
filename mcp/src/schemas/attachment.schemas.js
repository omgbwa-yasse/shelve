// Schémas de validation pour les opérations sur les attachments
const { z } = require('zod');

// Schéma pour analyser plusieurs documents
const AnalyzeDocumentsSchema = z.object({
  body: z.object({
    attachmentIds: z.array(z.number().positive()).min(1).max(20),
    analysisOptions: z.object({
      includeMetadata: z.boolean().optional().default(true),
      extractFullText: z.boolean().optional().default(true),
      performSemanticAnalysis: z.boolean().optional().default(true),
      thesaurusScope: z.string().optional(),
      maxTerms: z.number().min(1).max(50).optional().default(10),
      recordTemplate: z.enum(['basic', 'detailed', 'full']).optional().default('detailed')
    }).optional().default({}),
    modelName: z.string().optional()
  })
});

// Schéma pour analyser un seul document
const AnalyzeSingleDocumentSchema = z.object({
  body: z.object({
    attachmentId: z.number().positive(),
    extractionOptions: z.object({
      extractFullText: z.boolean().optional().default(true),
      generateSummary: z.boolean().optional().default(true),
      extractKeywords: z.boolean().optional().default(true),
      maxTerms: z.number().min(1).max(20).optional().default(10),
      includeEntities: z.boolean().optional().default(true)
    }).optional().default({}),
    modelName: z.string().optional()
  })
});

// Schéma pour récupérer les métadonnées
const GetDocumentsMetadataSchema = z.object({
  body: z.object({
    attachmentIds: z.array(z.number().positive()).min(1).max(50)
  })
});

// Schéma pour suggérer une description de record
const SuggestRecordDescriptionSchema = z.object({
  body: z.object({
    attachmentIds: z.array(z.number().positive()).min(1).max(20),
    contextualInfo: z.object({
      activity: z.string().optional(),
      organisation: z.string().optional(),
      dateContext: z.string().optional(),
      administrativeContext: z.string().optional(),
      relatedRecords: z.array(z.number()).optional()
    }).optional().default({}),
    recordTemplate: z.enum(['basic', 'detailed', 'full']).optional().default('detailed'),
    modelName: z.string().optional()
  })
});

// Schéma pour suggérer une indexation thésaurus
const SuggestThesaurusIndexingSchema = z.object({
  body: z.object({
    attachmentIds: z.array(z.number().positive()).min(1).max(20),
    thesaurusScope: z.string().optional(),
    maxTerms: z.number().min(1).max(30).optional().default(15),
    weightingMethod: z.enum(['frequency', 'semantic', 'combined']).optional().default('combined'),
    modelName: z.string().optional()
  })
});

// Schéma pour générer un record complet
const GenerateCompleteRecordSchema = z.object({
  body: z.object({
    attachmentIds: z.array(z.number().positive()).min(1).max(20),
    recordOptions: z.object({
      template: z.enum(['basic', 'detailed', 'full']).optional().default('detailed'),
      includeArrangement: z.boolean().optional().default(true),
      includeAccessConditions: z.boolean().optional().default(true),
      suggestLevel: z.boolean().optional().default(true),
      contextualInfo: z.object({
        activity: z.string().optional(),
        organisation: z.string().optional(),
        dateContext: z.string().optional(),
        administrativeContext: z.string().optional()
      }).optional()
    }).optional().default({}),
    indexingOptions: z.object({
      thesaurusScope: z.string().optional(),
      maxTerms: z.number().min(1).max(30).optional().default(15),
      weightingMethod: z.enum(['frequency', 'semantic', 'combined']).optional().default('combined'),
      autoAssign: z.boolean().optional().default(false)
    }).optional().default({}),
    modelName: z.string().optional()
  })
});

module.exports = {
  AnalyzeDocumentsSchema,
  AnalyzeSingleDocumentSchema,
  GetDocumentsMetadataSchema,
  SuggestRecordDescriptionSchema,
  SuggestThesaurusIndexingSchema,
  GenerateCompleteRecordSchema
};

// Types Zod pour les modèles de données
const { z } = require('zod');

/**
 * Schéma complet d'un Record, basé sur le modèle Laravel Record
 * Correspond aux champs de la migration et du modèle Record.php
 */
const RecordSchema = z.object({
  id: z.number().int().positive(),
  // Zone d'identification
  code: z.string(),
  name: z.string(),
  date_format: z.string().nullable().optional(),
  date_start: z.string().nullable().optional(),
  date_end: z.string().nullable().optional(),
  date_exact: z.string().nullable().optional(),
  level_id: z.number().int().positive(),
  width: z.number().nullable().optional(),
  width_description: z.string().nullable().optional(),

  // Zone du contexte
  biographical_history: z.string().nullable().optional(),
  archival_history: z.string().nullable().optional(),
  acquisition_source: z.string().nullable().optional(),

  // Zone du contenu et structure
  content: z.string().nullable().optional(),
  appraisal: z.string().nullable().optional(),
  accrual: z.string().nullable().optional(),
  arrangement: z.string().nullable().optional(),

  // Zone du condition d'accès et utilisation
  access_conditions: z.string().nullable().optional(),
  reproduction_conditions: z.string().nullable().optional(),
  language_material: z.string().nullable().optional(),
  characteristic: z.string().nullable().optional(),
  finding_aids: z.string().nullable().optional(),

  // Zone du source complémentaires
  location_original: z.string().nullable().optional(),
  location_copy: z.string().nullable().optional(),
  related_unit: z.string().nullable().optional(),
  publication_note: z.string().nullable().optional(),

  // Zone de note
  note: z.string().nullable().optional(),

  // Zone de control area
  archivist_note: z.string().nullable().optional(),
  rule_convention: z.string().nullable().optional(),

  // Timestamps
  created_at: z.string().nullable().optional(),
  updated_at: z.string().nullable().optional(),

  // Clés étrangères
  status_id: z.number().int().positive(),
  support_id: z.number().int().positive(),
  activity_id: z.number().int().positive(),
  parent_id: z.number().int().nullable().optional(),
  container_id: z.number().int().nullable().optional(),
  user_id: z.number().int().positive(),
  organisation_id: z.number().int().nullable().optional()
});

/**
 * Version simplifiée du RecordSchema pour les requêtes d'API
 * Ne contient que les champs les plus couramment utilisés
 */
const RecordSimplifiedSchema = z.object({
  id: z.number().int().positive(),
  code: z.string(),
  name: z.string(),
  content: z.string().nullable().optional(),
  biographical_history: z.string().nullable().optional(),
  archival_history: z.string().nullable().optional(),
  note: z.string().nullable().optional(),
  date_start: z.string().nullable().optional(),
  date_end: z.string().nullable().optional()
});

/**
 * Schéma pour la relation entre records et termes du thésaurus
 * Basé sur la table pivot record_thesaurus_concept
 */
const RecordThesaurusConceptSchema = z.object({
  record_id: z.number().int().positive(),
  concept_id: z.number().int().positive(),
  weight: z.number().optional(), // Poids de la relation
  context: z.string().nullable().optional(), // Contexte de l'extraction
  extraction_note: z.string().nullable().optional() // Note sur l'extraction
});

/**
 * Schéma pour le concept du thésaurus
 * Simplifié par rapport au modèle Laravel complet
 */
const ThesaurusConceptSchema = z.object({
  id: z.number().int().positive(),
  name: z.string(),
  type: z.string().optional() // Type du terme (thématique, géographique, etc.)
});

module.exports = {
  RecordSchema,
  RecordSimplifiedSchema,
  RecordThesaurusConceptSchema,
  ThesaurusConceptSchema
};

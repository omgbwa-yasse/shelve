// Utilitaires et constantes pour le serveur MCP

// Constantes de validation
const SPECIAL_MODES = ['format_title', 'extract_keywords', 'categorized_keywords'];
const STANDARD_MODES = ['enrich', 'summarize', 'analyze'];
const ALL_MODES = [...SPECIAL_MODES, ...STANDARD_MODES];

// Utilitaires de validation
function validateRecordData(recordData) {
  if (!recordData) {
    throw new Error('Données du record manquantes');
  }

  if (!recordData.name || recordData.name.trim().length === 0) {
    throw new Error('Le nom du record est requis');
  }

  return true;
}

function validateContent(content) {
  if (!content || content.trim().length === 0) {
    throw new Error('Contenu à analyser manquant');
  }

  return true;
}

// Utilitaires de formatage
function combineRecordContent(recordData) {
  return [
    recordData.name,
    recordData.content,
    recordData.biographical_history,
    recordData.archival_history,
    recordData.note
  ].filter(Boolean).join("\n\n");
}

function sanitizeInput(input) {
  if (typeof input !== 'string') return input;
  return input.trim().replace(/[<>]/g, ''); // Nettoyage basique
}

// Utilitaires de response
function createSuccessResponse(data) {
  return {
    success: true,
    timestamp: new Date().toISOString(),
    ...data
  };
}

function createErrorResponse(error, status = 500) {
  return {
    success: false,
    error: error.message || error,
    timestamp: new Date().toISOString(),
    status
  };
}

// Export des constantes et fonctions
module.exports = {
  SPECIAL_MODES,
  STANDARD_MODES,
  ALL_MODES,
  validateRecordData,
  validateContent,
  combineRecordContent,
  sanitizeInput,
  createSuccessResponse,
  createErrorResponse
};

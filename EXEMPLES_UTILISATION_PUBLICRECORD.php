<?php

// Exemple d'utilisation des nouveaux accesseurs dans PublicRecord

// ========================================
// ACCÈS FACILE AUX CHAMPS ESSENTIELS
// ========================================

$publicRecord = PublicRecord::with('record')->first();

// Avant (accès compliqué) :
$title = $publicRecord->record->name ?? 'Titre non disponible';
$code = $publicRecord->record->code ?? '';
$content = $publicRecord->record->content ?? '';

// Maintenant (accès simplifié) :
$title = $publicRecord->title;          // "Titre du document"
$code = $publicRecord->code;            // "REF-2024-001"
$content = $publicRecord->content;      // "Contenu du document..."

// ========================================
// DONNÉES FORMATÉES
// ========================================

// Date formatée automatiquement
$dateRange = $publicRecord->formatted_date_range;  // "2020 - 2024" ou "Depuis 2020"

// Statut du document
$isAvailable = $publicRecord->is_available;  // true/false
$isExpired = $publicRecord->is_expired;      // true/false

// ========================================
// ACCÈS À TOUTES LES DONNÉES ESSENTIELLES
// ========================================

$essentialData = $publicRecord->essential_data;
// Retourne un array avec tous les champs essentiels :
// [
//     'id' => 1,
//     'title' => 'Titre du document',
//     'code' => 'REF-2024-001',
//     'content' => 'Contenu...',
//     'date_start' => '2020',
//     'date_end' => '2024',
//     'formatted_date_range' => '2020 - 2024',
//     'biographical_history' => '...',
//     'language_material' => 'Français',
//     'access_conditions' => '...',
//     'published_at' => Carbon instance,
//     'expires_at' => Carbon instance,
//     'publication_notes' => '...',
//     'is_expired' => false,
//     'is_available' => true,
//     'publisher_name' => 'Jean Dupont'
// ]

// ========================================
// UTILISATION DES SCOPES
// ========================================

// Récupérer seulement les documents disponibles (non expirés)
$availableRecords = PublicRecord::available()->get();

// Rechercher dans le contenu des documents
$searchResults = PublicRecord::searchContent('archives')->get();

// Combiner les scopes
$results = PublicRecord::available()
    ->searchContent('documents historiques')
    ->with(['publisher', 'record'])
    ->orderBy('published_at', 'desc')
    ->paginate(10);

// ========================================
// DANS LES VUES BLADE
// ========================================

// Dans les vues, vous pouvez maintenant utiliser directement :
// {{ $record->title }}
// {{ $record->code }}
// {{ $record->formatted_date_range }}
// {{ $record->is_available ? 'Disponible' : 'Non disponible' }}

// ========================================
// DANS LES API
// ========================================

// Transformation simplifiée pour l'API
$apiData = $records->map(function ($record) {
    return [
        'id' => $record->id,
        'title' => $record->title,
        'code' => $record->code,
        'content' => $record->content,
        'date_range' => $record->formatted_date_range,
        'status' => $record->is_available ? 'available' : 'unavailable',
        // ... autres champs
    ];
});

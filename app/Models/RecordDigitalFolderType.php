<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordDigitalFolderType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'color',
        'metadata_template_id',
        'code_prefix',
        'code_pattern',
        'default_access_level',
        'requires_approval',
        'mandatory_metadata',
        'allowed_document_types',
        'is_active',
        'is_system',
        'display_order',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'mandatory_metadata' => 'array',
        'allowed_document_types' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation avec le template de métadonnées
     */
    public function metadataTemplate()
    {
        return $this->belongsTo(MetadataTemplate::class);
    }

    /**
     * Relation avec les dossiers de ce type
     */
    public function folders()
    {
        return $this->hasMany(RecordDigitalFolder::class, 'type_id');
    }

    /**
     * Get the metadata profiles for this folder type.
     */
    public function metadataProfiles()
    {
        return $this->hasMany(RecordDigitalFolderMetadataProfile::class, 'folder_type_id');
    }

    /**
     * Get the metadata definitions through profiles.
     */
    public function metadataDefinitions()
    {
        return $this->belongsToMany(
            MetadataDefinition::class,
            'record_digital_folder_metadata_profiles',
            'folder_type_id',
            'metadata_definition_id'
        )->withPivot([
            'mandatory',
            'visible',
            'readonly',
            'default_value',
            'validation_rules',
            'sort_order',
        ])->withTimestamps()->orderByPivot('sort_order');
    }

    /**
     * Get mandatory metadata definitions for this folder type.
     */
    public function getMandatoryMetadataDefinitions()
    {
        return $this->metadataDefinitions()->wherePivot('mandatory', true)->get();
    }

    /**
     * Get visible metadata definitions for this folder type.
     */
    public function getVisibleMetadataDefinitions()
    {
        return $this->metadataDefinitions()->wherePivot('visible', true)->get();
    }

    /**
     * Scope pour les types actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les types système
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope pour les types personnalisés
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope pour ordonner par ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Générer un nouveau code pour un dossier de ce type
     */
    public function generateCode(): string
    {
        $pattern = $this->code_pattern;
        $year = date('Y');
        $prefix = $this->code_prefix ?? $this->code;

        // Trouver le dernier numéro de séquence
        $lastFolder = RecordDigitalFolder::where('type_id', $this->id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastFolder ? ($lastFolder->id + 1) : 1;
        $sequencePadded = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Remplacer les placeholders
        $code = str_replace([
            '{{PREFIX}}',
            '{{YEAR}}',
            '{{SEQ}}',
            '{{MONTH}}',
            '{{DAY}}',
        ], [
            $prefix,
            $year,
            $sequencePadded,
            date('m'),
            date('d'),
        ], $pattern);

        return $code;
    }

    /**
     * Vérifier si un type de document est autorisé
     */
    public function isDocumentTypeAllowed(string $documentTypeCode): bool
    {
        if (empty($this->allowed_document_types)) {
            return true; // Tous les types sont autorisés si non spécifié
        }

        // Si c'est une string JSON, la décoder
        $allowedTypes = is_array($this->allowed_document_types)
            ? $this->allowed_document_types
            : json_decode($this->allowed_document_types, true);

        // Les IDs sont stockés, il faut chercher le type par code et vérifier son ID
        $documentType = RecordDigitalDocumentType::where('code', $documentTypeCode)->first();
        if (!$documentType) {
            return false;
        }

        return in_array($documentType->id, $allowedTypes ?? []);
    }

    /**
     * Obtenir les types de documents autorisés
     */
    public function getAllowedDocumentTypes()
    {
        if (empty($this->allowed_document_types)) {
            return RecordDigitalDocumentType::active()->get();
        }

        return RecordDigitalDocumentType::whereIn('code', $this->allowed_document_types)
            ->active()
            ->get();
    }
}

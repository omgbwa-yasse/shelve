<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'parent_id',
        'reference_list_id',
        'is_container',
        'icon',
        'color',
        'code_prefix',
        'code_pattern',
        'allowed_mime_types',
        'allowed_extensions',
        'max_file_size',
        'requires_versioning',
        'requires_approval',
        'requires_signature',
        'default_access_level',
        'is_active',
        'display_order',
        'created_by',
        'updated_by',
        'legacy_type',
    ];

    protected $casts = [
        'is_container' => 'boolean',
        'allowed_mime_types' => 'array',
        'allowed_extensions' => 'array',
        'max_file_size' => 'integer',
        'requires_versioning' => 'boolean',
        'requires_approval' => 'boolean',
        'requires_signature' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Type parent (arborescence de types, ex. catégorie -> sous-type).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(RecordType::class, 'parent_id');
    }

    /**
     * Types enfants.
     */
    public function children(): HasMany
    {
        return $this->hasMany(RecordType::class, 'parent_id');
    }

    /**
     * Liste de référence (DomaineValeurs) sur laquelle ce type est ancré.
     */
    public function referenceList(): BelongsTo
    {
        return $this->belongsTo(ReferenceList::class, 'reference_list_id');
    }

    /**
     * Profils de métadonnées de ce type.
     */
    public function metadataProfiles(): HasMany
    {
        return $this->hasMany(RecordTypeMetadataProfile::class, 'record_type_id');
    }

    /**
     * Définitions de métadonnées via les profils (remplace les relations équivalentes
     * de RecordDigitalFolderType / RecordDigitalDocumentType).
     */
    public function metadataDefinitions()
    {
        return $this->belongsToMany(
            MetadataDefinition::class,
            'record_type_metadata_profiles',
            'record_type_id',
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
     * Définitions de métadonnées obligatoires pour ce type.
     */
    public function getMandatoryMetadataDefinitions()
    {
        return $this->metadataDefinitions()->wherePivot('mandatory', true)->get();
    }

    /**
     * Définitions de métadonnées visibles pour ce type.
     */
    public function getVisibleMetadataDefinitions()
    {
        return $this->metadataDefinitions()->wherePivot('visible', true)->get();
    }

    /**
     * Un type conteneur joue le rôle de "dossier" (Fonds, Série, Dossier, ...).
     */
    public function isContainer(): bool
    {
        return (bool) $this->is_container;
    }

    /**
     * Scope pour les types actifs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour ordonner par ordre d'affichage.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Générer un nouveau code pour une notice de ce type.
     */
    public function generateCode(): string
    {
        $pattern = $this->code_pattern;
        $year = date('Y');
        $prefix = $this->code_prefix ?? $this->code;

        $lastRecord = Record::withTrashed()
            ->where('type_id', $this->id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastRecord ? ($lastRecord->id + 1) : 1;
        $sequencePadded = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return str_replace([
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
        ], $pattern ?? '{{PREFIX}}-{{YEAR}}-{{SEQ}}');
    }

    /**
     * Vérifier si un type MIME est autorisé.
     */
    public function isMimeTypeAllowed(string $mimeType): bool
    {
        if (empty($this->allowed_mime_types)) {
            return true;
        }

        return in_array($mimeType, $this->allowed_mime_types);
    }

    /**
     * Vérifier si une extension est autorisée.
     */
    public function isExtensionAllowed(string $extension): bool
    {
        if (empty($this->allowed_extensions)) {
            return true;
        }

        $extension = str_starts_with($extension, '.') ? $extension : '.' . $extension;

        return in_array(strtolower($extension), array_map('strtolower', $this->allowed_extensions));
    }

    /**
     * Vérifier si une taille de fichier est autorisée.
     */
    public function isFileSizeAllowed(int $fileSize): bool
    {
        if ($this->max_file_size === null) {
            return true;
        }

        return $fileSize <= $this->max_file_size;
    }

    /**
     * Valider un fichier selon les règles de ce type.
     */
    public function validateFile(string $mimeType, string $extension, int $fileSize): array
    {
        $errors = [];

        if (!$this->isMimeTypeAllowed($mimeType)) {
            $errors[] = "Type MIME non autorisé : {$mimeType}. Types autorisés : " . implode(', ', $this->allowed_mime_types ?? []);
        }

        if (!$this->isExtensionAllowed($extension)) {
            $errors[] = "Extension non autorisée : {$extension}. Extensions autorisées : " . implode(', ', $this->allowed_extensions ?? []);
        }

        if (!$this->isFileSizeAllowed($fileSize)) {
            $errors[] = 'Fichier trop volumineux : maximum autorisé ' . $this->max_file_size . ' octets';
        }

        return $errors;
    }
}

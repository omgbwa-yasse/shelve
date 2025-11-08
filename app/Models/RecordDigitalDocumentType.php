<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordDigitalDocumentType extends Model
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
        'allowed_mime_types',
        'allowed_extensions',
        'max_file_size',
        'requires_signature',
        'requires_approval',
        'mandatory_metadata',
        'retention_years',
        'enable_versioning',
        'max_versions',
        'is_active',
        'is_system',
        'display_order',
    ];

    protected $casts = [
        'allowed_mime_types' => 'array',
        'allowed_extensions' => 'array',
        'max_file_size' => 'integer',
        'requires_signature' => 'boolean',
        'requires_approval' => 'boolean',
        'mandatory_metadata' => 'array',
        'retention_years' => 'integer',
        'enable_versioning' => 'boolean',
        'max_versions' => 'integer',
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
     * Relation avec les documents de ce type
     */
    public function documents()
    {
        return $this->hasMany(RecordDigitalDocument::class, 'type_id');
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
     * Générer un nouveau code pour un document de ce type
     */
    public function generateCode(): string
    {
        $pattern = $this->code_pattern;
        $year = date('Y');
        $prefix = $this->code_prefix ?? $this->code;

        // Trouver le dernier numéro de séquence
        $lastDocument = RecordDigitalDocument::where('type_id', $this->id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastDocument ? ($lastDocument->id + 1) : 1;
        $sequencePadded = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Remplacer les placeholders
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
        ], $pattern);
    }

    /**
     * Vérifier si un type MIME est autorisé
     */
    public function isMimeTypeAllowed(string $mimeType): bool
    {
        if (empty($this->allowed_mime_types)) {
            return true; // Tous les types MIME sont autorisés si non spécifié
        }

        return in_array($mimeType, $this->allowed_mime_types);
    }

    /**
     * Vérifier si une extension est autorisée
     */
    public function isExtensionAllowed(string $extension): bool
    {
        if (empty($this->allowed_extensions)) {
            return true; // Toutes les extensions sont autorisées si non spécifié
        }

        // Normaliser l'extension (avec ou sans point)
        $extension = str_starts_with($extension, '.') ? $extension : '.' . $extension;

        return in_array(strtolower($extension), array_map('strtolower', $this->allowed_extensions));
    }

    /**
     * Vérifier si une taille de fichier est autorisée
     */
    public function isFileSizeAllowed(int $fileSize): bool
    {
        if ($this->max_file_size === null) {
            return true; // Pas de limite si non spécifié
        }

        return $fileSize <= $this->max_file_size;
    }

    /**
     * Obtenir la taille maximale en format lisible
     */
    public function getMaxFileSizeHumanAttribute(): ?string
    {
        if ($this->max_file_size === null) {
            return null;
        }

        $bytes = $this->max_file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Valider un fichier selon les règles de ce type
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
            $errors[] = "Fichier trop volumineux : " . $this->formatBytes($fileSize) . ". Maximum autorisé : " . $this->max_file_size_human;
        }

        return $errors;
    }

    /**
     * Formater une taille en octets en format lisible
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

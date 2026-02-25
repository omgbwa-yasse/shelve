<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordDigitalDocumentMetadataProfile extends Model
{
    use HasFactory;

    protected $table = 'record_digital_document_metadata_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_type_id',
        'metadata_definition_id',
        'mandatory',
        'visible',
        'readonly',
        'default_value',
        'validation_rules',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mandatory' => 'boolean',
        'visible' => 'boolean',
        'readonly' => 'boolean',
        'validation_rules' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the document type that owns this profile.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalDocumentType::class, 'document_type_id');
    }

    /**
     * Get the metadata definition for this profile.
     */
    public function metadataDefinition(): BelongsTo
    {
        return $this->belongsTo(MetadataDefinition::class);
    }

    /**
     * Get the user who created this profile.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this profile.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include mandatory profiles.
     */
    public function scopeMandatory($query)
    {
        return $query->where('mandatory', true);
    }

    /**
     * Scope a query to only include visible profiles.
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

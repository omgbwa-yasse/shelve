<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Term extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'terms';

    protected $fillable = [
        'preferred_label',
        'scope_note',
        'language',
        'category',
        'status',
        'notation',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scopes
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('preferred_label', 'like', "%{$term}%");
    }
}

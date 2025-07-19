<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesaurusCollectionLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'language',
        'label_type', // 'prefLabel', 'altLabel', 'hiddenLabel'
        'label',
    ];

    /**
     * Relation avec la collection
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(ThesaurusCollection::class, 'collection_id');
    }
}

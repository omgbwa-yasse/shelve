<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookClassification extends Model
{
    protected $table = 'classifications';

    protected $fillable = [
        'name',
        'description',
        'parent_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BookClassification::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BookClassification::class, 'parent_id');
    }

    public function books()
    {
        return $this->belongsToMany(RecordBook::class, 'record_book_classification', 'classification_id', 'book_id')
            ->withPivot('ordre')
            ->orderByPivot('ordre');
    }
}

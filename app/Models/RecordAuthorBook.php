<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RecordAuthorBook extends Pivot
{
    protected $table = 'record_author_book';

    public $incrementing = true;

    protected $fillable = [
        'author_id',
        'book_id',
        'responsibility_type',
        'function',
        'role',
        'display_order',
        'notes',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(RecordBook::class, 'book_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(RecordAuthor::class, 'author_id');
    }
}

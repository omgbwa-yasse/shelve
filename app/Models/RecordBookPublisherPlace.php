<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordBookPublisherPlace extends Model
{
    protected $table = 'record_book_publisher_place';

    protected $fillable = [
        'book_id',
        'publisher_id',
        'publication_place',
        'display_order',
        'publisher_role',
    ];

    protected $casts = [
        'ordre' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(RecordBook::class, 'book_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(RecordBookPublisher::class, 'publisher_id');
    }
}

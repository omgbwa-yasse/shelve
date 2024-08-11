<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyShelf extends Model
{
    use HasFactory;

    protected $fillable = [
        'shelf_id',
        'dolly_id',
    ];

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}

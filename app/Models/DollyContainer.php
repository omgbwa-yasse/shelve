<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'container_id',
        'dolly_id',
    ];

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}

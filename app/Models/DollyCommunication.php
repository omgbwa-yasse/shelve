<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'communication_id',
        'dolly_id',
    ];

    public function communication()
    {
        return $this->belongsTo(Communication::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}

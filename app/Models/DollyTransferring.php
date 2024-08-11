<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyTransferring extends Model
{
    use HasFactory;

    protected $table = 'dolly_transferrings';

    protected $fillable = [
        'transferring_id',
        'dolly_id',
    ];

    public function transferring()
    {
        return $this->belongsTo(slip::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}

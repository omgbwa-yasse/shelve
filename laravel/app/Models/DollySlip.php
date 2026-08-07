<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollySlip extends Model
{
    use HasFactory;

    protected $table = 'dolly_slips';

    protected $fillable = [
        'slip_id',
        'dolly_id',
    ];

    public function slip()
    {
        return $this->belongsTo(slip::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}

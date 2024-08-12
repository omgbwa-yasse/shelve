<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollySlipRecord extends Model
{
    use HasFactory;

    protected $table = 'dolly_slip_records';

    protected $fillable = [
        'record_id',
        'slip_id',
        'dolly_id',
    ];

    public function slip()
    {
        return $this->belongsTo(slip::class);
    }


    public function record()
    {
        return $this->belongsTo(record::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }
}

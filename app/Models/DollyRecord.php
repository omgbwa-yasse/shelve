<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollyRecord extends Model
{
    use HasFactory;


    protected $table = 'dolly_records';

    protected $fillable = [
        'record_id',
        'dolly_id',
    ];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    public function dolly()
    {
        return $this->belongsTo(Dolly::class);
    }

}

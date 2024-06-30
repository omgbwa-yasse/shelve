<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class communicationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_id',
        'operator_id',
        'user_id',
        'is_original',
        'return_date',
        'return_effective',
    ];

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

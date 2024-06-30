<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'record_id',
        'start_time',
        'end_time',
        'notes',
        'status_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function status()
    {
        return $this->belongsTo(ReservationStatus::class, 'status_id');
    }
}

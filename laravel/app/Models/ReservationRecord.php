<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationRecord extends Model
{
    use HasFactory;


    protected $table = 'reservation_record';

    protected $fillable = [
        'reservation_id',
        'record_id',
        'is_original',
        'reservation_date',
        'communication_id',
        'operator_id',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function record()
    {
        return $this->belongsTo(RecordPhysical::class);
    }

    public function communication()
    {
        return $this->belongsTo(Communication::class);
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

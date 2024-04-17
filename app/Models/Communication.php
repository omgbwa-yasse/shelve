<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Record;
use App\Models\User;


class Communication extends Model
{
    use HasFactory;
    protected $fillable = ['record_id', 'operator', 'user', 'date_creation', 'return', 'return_effective'];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

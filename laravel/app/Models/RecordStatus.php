<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordStatus extends Model
{
    use HasFactory;

    protected $table = 'record_statuses';
    protected $fillable = [
        'name',
        'description',
    ];


    public function records()
    {
        return $this->hasMany(RecordPhysical::class);
    }
}

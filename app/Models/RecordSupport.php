<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Record;

class RecordSupport extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'observation'
    ];

    public function records()
    {
        return $this->hasMany(Record::class);
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Record;


class RecordLevel extends Model
{
    use HasFactory;

    protected $table = 'record_levels';

    protected $fillable = [
        'name', 'description', 'child_id'
    ];

    public function child()
    {
        return $this->belongsTo(RecordLevel::class, 'child_id');
    }

    public function parent()
    {
        return $this->hasOne(RecordLevel::class, 'id', 'child_id');
    }

}

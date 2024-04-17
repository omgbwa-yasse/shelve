<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Record;

class RecordLink extends Model
{
    use HasFactory;
    protected $fillable = ['record_id', 'parent_id'];

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function parent()
    {
        return $this->belongsTo(Record::class, 'parent_id');
    }
}

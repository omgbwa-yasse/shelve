<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecordLink extends Model
{
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

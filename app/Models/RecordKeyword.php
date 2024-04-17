<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecordKeyword extends Model
{
    protected $fillable = ['record_id', 'keyword_id'];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    public function keyword()
    {
        return $this->belongsTo(Keyword::class);
    }
}

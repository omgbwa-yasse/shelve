<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TermRecord extends Model
{
    protected $fillable = ['record_id', 'term_id'];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}

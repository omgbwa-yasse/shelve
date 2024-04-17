<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecordDocument extends Model
{
    protected $fillable = ['path', 'crypt', 'size', 'extension', 'record_id'];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }
}

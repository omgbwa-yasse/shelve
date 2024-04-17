<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetentionClassification extends Model
{
    protected $fillable = ['retention_id', 'classification_id'];

    public function retention()
    {
        return $this->belongsTo(Retention::class);
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }
}

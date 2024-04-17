<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Retention;
use App\Models\classification;


class RetentionClassification extends Model
{
    use HasFactory;
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

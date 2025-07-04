<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalAlignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_id',
        'external_uri',
        'external_label',
        'external_vocabulary',
        'match_type',
    ];

    // Le terme associé
    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}

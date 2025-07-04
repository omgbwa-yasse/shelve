<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonDescriptor extends Model
{
    use HasFactory;

    protected $fillable = [
        'descriptor_id',
        'non_descriptor_label',
        'relation_type',
        'hidden',
    ];

    // Le terme descripteur associé
    public function descriptor()
    {
        return $this->belongsTo(Term::class, 'descriptor_id');
    }
}

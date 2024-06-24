<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_id',
        'child_id',
        'relation_type_id',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function child()
    {
        return $this->belongsTo(Term::class, 'child_id');
    }

    public function relationType()
    {
        return $this->belongsTo(TermRelationType::class);
    }
}



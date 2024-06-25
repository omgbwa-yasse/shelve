<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermRelated extends Model
{
    use HasFactory;

    protected $table = 'term_related';

    protected $fillable = [
        'term_id',
        'term_related_id',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function relatedTerm()
    {
        return $this->belongsTo(Term::class, 'term_related_id');
    }

}

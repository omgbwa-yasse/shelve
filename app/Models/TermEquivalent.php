<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermEquivalent extends Model
{
    use HasFactory;
    protected $table = 'term_equivalents';
    protected $fillable = [
        'term1_id',
        'term2_id',
    ];

    public $timestamps = false;
    public function term1()
    {
        return $this->belongsTo(Term::class, 'term1_id');
    }

    public function term2()
    {
        return $this->belongsTo(Term::class, 'term2_id');
    }

}

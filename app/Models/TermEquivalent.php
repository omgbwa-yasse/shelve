<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


    class TermEquivalent extends Model
    {
        use HasFactory;
        protected $table = 'term_equivalent';


        protected $fillable = [
            'term_id',
            'term_used',
            'equivalent_type_id',
        ];


        public function term()
        {
            return $this->belongsTo(Term::class);
        }


        public function equivalentType()
        {
            return $this->belongsTo(TermEquivalentType::class, 'equivalent_type_id');
        }
}



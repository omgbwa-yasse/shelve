<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class TermEquivalentType extends Model
{

    use HasFactory;
    protected $table = 'term_equivalent_types';


    protected $fillable = [
        'code',
        'name',
        'description',
    ];


    public function termEquivalents()
    {
        return $this->hasMany(TermEquivalent::class, 'equivalent_type_id');
    }


}


<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $fillable = ['term', 'description', 'Language', 'specificity_level'];
}

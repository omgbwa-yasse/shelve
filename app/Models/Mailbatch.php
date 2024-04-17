<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mailbatch extends Model
{
    protected $fillable = ['reference', 'name', 'type_id'];
}

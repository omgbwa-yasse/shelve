<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Mailbatch extends Model
{
    use HasFactory;
    protected $fillable = ['reference', 'name', 'type_id'];
}

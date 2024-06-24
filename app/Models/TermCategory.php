<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TermCategory extends Model
{
    use HasFactory;

        protected $fillable = [
            'name',
            'description',
        ];


        public function terms()
        {
            return $this->hasMany(Term::class);
        }

}

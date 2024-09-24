<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Law extends Model
{
    use HasFactory;

    protected $table = 'laws';

    public $timestamps = true;

    protected $fillable = [
        'code',
        'name',
        'publish_date',
        'description',
    ];


    public function articles()
    {
        return $this->hasMany(LawArticle::class);
    }


}

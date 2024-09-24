<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawArticle extends Model
{
    use HasFactory;

    protected $table = 'law_articles';


    public $timestamps = true;

    protected $fillable = [
        'code',
        'name',
        'content',
        'law_id',
    ];


    public function law()
    {
        return $this->belongsTo(Law::class, 'law_id');
    }


    public function retentions()
    {
        return $this->belongsTo(RetentionLawArticle::class, 'law_article_id');
    }


}

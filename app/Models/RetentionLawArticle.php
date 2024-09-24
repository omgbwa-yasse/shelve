<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetentionLawArticle extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'retention_id',
        'law_article_id',
    ];


    public function lawArticle()
    {
        return $this->belongsTo(LawArticle::class, 'law_article_id');
    }


    public function retention()
    {
        return $this->belongsTo(Retention::class, 'retention_id');
    }


}

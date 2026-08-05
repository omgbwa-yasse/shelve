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
        // La colonne réelle est `description`, pas `content` : `$fillable` déclarait
        // un champ inexistant (même défaut que Role::display_name, corrigé plus tôt).
        // Trouvé lors du portage de D01 — sans vue ni contrôleur fonctionnel, ce
        // module n'avait jamais exercé ce chemin.
        'description',
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

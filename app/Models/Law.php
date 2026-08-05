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
        // `law_type_id` est NOT NULL en base mais était absent d'ici : toute création
        // via mass assignment (Law::create()) échouait silencieusement à assigner la
        // colonne, puis échouait en SQL. Trouvé lors du portage de D01 — le module
        // (contrôleur Blade vide, aucune vue) n'avait jamais été utilisé jusqu'ici.
        'law_type_id',
    ];

    public function articles()
    {
        return $this->hasMany(LawArticle::class);
    }

    public function lawType()
    {
        return $this->belongsTo(LawType::class);
    }
}

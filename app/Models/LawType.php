<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Type de loi (référentiel D01).
 *
 * La table `law_types` et la clé étrangère `laws.law_type_id` existaient déjà, mais
 * aucun modèle Eloquent ne les représentait. Créé lors du portage de D01, sur le
 * patron d'AuthorType.
 */
class LawType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function laws()
    {
        return $this->hasMany(Law::class);
    }
}

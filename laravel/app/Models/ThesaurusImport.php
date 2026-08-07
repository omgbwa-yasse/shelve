<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesaurusImport extends Model
{
    use HasFactory;

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indique si la clé primaire est un UUID.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Le type de la clé primaire.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type',
        'filename',
        'status',
        'total_items',
        'processed_items',
        'created_items',
        'updated_items',
        'error_items',
        'relationships_created',
        'message'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pivot `user_roles` (rattachement d'un agent à un rôle du système natif).
 *
 * Le contrôleur Blade `UserRoleController` référençait une classe inexistante
 * (`App\Models\UserRole` introuvable) : portage D09 — modèle créé à partir du
 * schéma (la table possède bien une colonne `id` auto-incrémentée).
 */
class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}

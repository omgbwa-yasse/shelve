<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Les étapes associées à ce modèle de workflow
     */
    public function steps()
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('order_index');
    }

    /**
     * L'utilisateur qui a créé ce template
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Les instances de workflow basées sur ce template
     */
    public function instances()
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    /**
     * Récupérer les templates actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Récupérer les templates par catégorie
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}

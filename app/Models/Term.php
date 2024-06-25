<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'language_id',
        'category_id',
        'type_id',
        'parent_id',
    ];

    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Term::class, 'parent_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function category()
    {
        return $this->belongsTo(TermCategory::class);
    }

    public function records()
    {
        return $this->belongsToMany(Record::class, 'record_term', 'term_id', 'record_id');
    }

    public function equivalentType()
    {
        return $this->belongsTo(TermEquivalentType::class);
    }

    public function equivalents()
    {
        return $this->hasMany(TermEquivalent::class, 'term_id');
    }

    public function type()
    {
        return $this->belongsTo(TermType::class, 'type_id');
    }

    public function translations()
    {
        return $this->hasMany(TermTranslation::class, 'term1_id')
                    ->orWhere('term2_id', $this->id);
    }
}

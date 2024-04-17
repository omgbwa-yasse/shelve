<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TermRelation extends Model
{
    protected $fillable = ['parent_id', 'child_id', 'category_id', 'relation_type_id'];

    public function parent()
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }

    public function child()
    {
        return $this->belongsTo(Term::class, 'child_id');
    }

    public function category()
    {
        return $this->belongsTo(TermCategory::class);
    }

    public function relationType()
    {
        return $this->belongsTo(RelationType::class);
    }
}

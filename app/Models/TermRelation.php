<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Term;
use App\Models\TermCategory;
use App\Models\RelationType;


class TermRelation extends Model
{
    use HasFactory;
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

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{

    protected $fillable = [
        'name',
        'description',
        'language_id',
        'category_id',
    ];

    public $timestamps = false;

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
        return $this->belongsToMany(Record::class, 'record_term', 'record_id','term_id');
    }

    public function relations()
    {
        return $this->hasMany(TermRelation::class);
    }


}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailTypology extends Model
{
    protected $fillable = ['name', 'description', 'typology_category_id'];

    public function typologyCategory()
    {
        return $this->belongsTo(TypologyCategory::class);
    }

    public function mails()
    {
        return $this->hasMany(Mail::class);
    }
}

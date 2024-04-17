<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TypologyCategory;
use App\Models\Mail;

class MailTypology extends Model
{
    use HasFactory;
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

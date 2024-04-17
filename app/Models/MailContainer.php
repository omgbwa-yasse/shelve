<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mail;
use App\Models\ContainerType;


class MailContainer extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference', 'name', 'type_id'
    ];

    public function containerType()
    {
        return $this->belongsTo(ContainerType::class);
    }

    public function mails()
    {
        return $this->belongsToMany(Mail::class);
    }
}

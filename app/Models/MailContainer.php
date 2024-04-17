<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailContainer extends Model
{
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

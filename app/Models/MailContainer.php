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
        'code',
        'name',
        'type_id',
        'user_id',
        'user_organisation_id'
    ];

    protected $table = 'mail_containers';

    public function containerType()
    {
        return $this->belongsTo(ContainerType::class, 'type_id');
    }

    public function mails()
    {
        return $this->belongsToMany(Mail::class);
    }
    public function mailArchivings()
    {
        return $this->hasMany(MailArchiving::class, 'container_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creatorOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'user_organisation_id');
    }


    public $timestamps = true;

}

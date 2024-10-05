<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $table = 'batches';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'organisation_holder_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_holder_id');
    }


    public function organisationHolder()
    {
        return $this->belongsTo(Organisation::class, 'organisation_holder_id');
    }

    public function transactions()
    {
        return $this->hasMany(BatchTransaction::class, 'batch_id');
    }


    public function mails()
    {
        return $this->belongsToMany(Mail::class, 'batch_mail')
                    ->withPivot('insert_date', 'remove_date')
                    ->withTimestamps();
    }



}


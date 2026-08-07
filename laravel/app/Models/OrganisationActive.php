<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationActive extends Model
{
    use HasFactory;

    protected $table = 'organisation_active';

    protected $fillable = [
        'organisation_id',
        'user_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }



}

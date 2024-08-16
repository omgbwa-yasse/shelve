<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOrganisation extends Model
{

    use HasFactory;

    protected $table = 'user_organisations';

    protected $fillable = [
        'user_id',
        'organisation_id',
        'role_id',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class);
    }




}

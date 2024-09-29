<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOrganisationRole extends Model
{
    use HasFactory;

    protected $table = 'user_organisation_role';

    protected $fillable = [
        'user_id',
        'organisation_id',
        'role_id',
        'creator_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id' );
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}

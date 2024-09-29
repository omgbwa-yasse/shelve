<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Organisation;


class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'surname' ,
        'birthday',
        'current_organisation_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }



    public function currentOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'current_organisation_id');
    }



    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'user_organisation_role', 'user_id', 'organisation_id')->withTimestamps();
    }



    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_organisation_role', 'user_id', 'role_id')->withTimestamps();
    }

    public function permissions()
    {
        /*
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->join('user_organisation_role', 'role_permissions.role_id', '=', 'user_organisation_role.role_id')
            ->where('user_organisation_role.user_id', $this->id);
        */
        return true;
    }

    public function hasPermissionTo(String $value)
    {
        /* foreach($this->permissions as $permission){
            if($permission->name == $value){
                return true;
            }
        }
        return false;*/
        Return true;
    }


}

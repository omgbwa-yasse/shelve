<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulletinBoard extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bulletin_boards';

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'bulletin_board_organisation')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'bulletin_board_user')
            ->withPivot('role', 'permissions', 'assigned_by')
            ->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function isUserAdmin($userId)
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user && in_array($user->pivot->role, ['super_admin', 'admin']);
    }

    public function isUserModerator($userId)
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user && in_array($user->pivot->role, ['super_admin', 'admin', 'moderator']);
    }

    public function hasDeletePermission($userId)
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user && ($user->pivot->permissions === 'delete' || $user->pivot->role === 'super_admin');
    }

    public function hasEditPermission($userId)
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user && (in_array($user->pivot->permissions, ['edit', 'delete']) || $user->pivot->role === 'super_admin');
    }

    public function hasWritePermission($userId)
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user && $user->pivot->permissions !== null;
    }
}

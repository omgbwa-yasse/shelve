<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BulletinBoard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'bulletin_board_attachment')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'bulletin_board_organisation')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function administrators()
    {
        return $this->belongsToMany(User::class, 'bulletin_board_user')
            ->withPivot(['role', 'permissions', 'assigned_by_id'])
            ->withTimestamps();
    }
}

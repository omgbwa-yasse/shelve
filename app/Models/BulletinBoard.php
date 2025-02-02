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
    use HasFactory, softDeletes;


    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];


    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }



    public function events()
    {
        return $this->hasMany(Event::class, 'events', 'bulletin_board_id');
    }


    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }


    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class)
            ->withPivot('access_level')
            ->withTimestamps();
    }


    public function administrators(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'permissions', 'assigned_by_id')
            ->withTimestamps();
    }


    public function scopeAccessibleByOrganisation($query, $organisationId)
    {
        return $query->whereHas('organisations', function($q) use ($organisationId) {
            $q->where('organisation_id', $organisationId);
        });
    }


    public function scopeWhereUserIsAdmin($query, $userId)
    {
        return $query->whereHas('administrators', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

}

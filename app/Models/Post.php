<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bulletin_board_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function bulletinBoard()
    {
        return $this->belongsTo(BulletinBoard::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'post_attachments')
            ->withPivot('created_by')
            ->withTimestamps();
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isActive()
    {
        return $this->start_date->isPast() &&
               ($this->end_date === null || $this->end_date->isFuture());
    }

    public function isExpired()
    {
        return $this->end_date !== null && $this->end_date->isPast();
    }

    public function isScheduled()
    {
        return $this->start_date->isFuture();
    }

    public function canBeEditedBy($user)
    {
        if ($user->id === $this->created_by) {
            return true;
        }

        $board = $this->bulletinBoard;
        return $board && $board->hasEditPermission($user->id);
    }

    public function canBeDeletedBy($user)
    {
        if ($user->id === $this->created_by) {
            return true;
        }

        $board = $this->bulletinBoard;
        return $board && $board->hasDeletePermission($user->id);
    }
}

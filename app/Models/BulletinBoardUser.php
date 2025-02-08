<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulletinBoardUser extends Model
{
    use HasFactory;

    protected $table = 'bulletin_board_user';

    protected $fillable = [
        'bulletin_board_id',
        'user_id',
        'role',
        'permissions',
        'assigned_by_id'
    ];

    public function bulletinBoard()
    {
        return $this->belongsTo(BulletinBoard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }
}

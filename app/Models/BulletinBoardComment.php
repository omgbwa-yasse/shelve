<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class BulletinBoardComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'bulletin_board_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bulletinBoard()
    {
        return $this->belongsTo(BulletinBoard::class);
    }
}

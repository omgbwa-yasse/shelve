<?php

// app/Models/Post.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'user_id',
        'bulletin_board_id'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    public function bulletinBoard()
    {
        return $this->belongsTo(BulletinBoard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

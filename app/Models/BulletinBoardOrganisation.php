<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulletinBoardOrganisation extends Model
{
    use HasFactory;

    protected $table = 'bulletin_board_organisation';

    protected $fillable = [
        'bulletin_board_id',
        'organisation_id',
        'user_id'
    ];

    public function bulletinBoard()
    {
        return $this->belongsTo(BulletinBoard::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

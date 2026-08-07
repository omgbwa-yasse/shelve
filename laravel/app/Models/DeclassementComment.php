<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeclassementComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'declassement_list_id',
        'user_id',
        'content',
    ];

    public function declassementList()
    {
        return $this->belongsTo(DeclassementList::class, 'declassement_list_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

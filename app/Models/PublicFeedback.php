<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_feedbacks';

    protected $fillable = [
        'user_id',
        'subject',
        'title',
        'content',
        'type',
        'priority',
        'status',
        'contact_name',
        'contact_email',
        'related_id',
        'related_type',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(PublicFeedbackComment::class, 'feedback_id');
    }

    public function related()
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicSearchLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_search_logs';

    protected $fillable = [
        'user_id',
        'search_term',
        'filters',
        'results_count',
    ];

    protected $casts = [
        'filters' => 'json',
        'results_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_id');
    }
}

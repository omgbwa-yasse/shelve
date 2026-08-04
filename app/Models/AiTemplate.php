<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'file_name',
        'file_path',
        'mime_type',
        'size',
        'description',
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAbsolutePathAttribute(): string
    {
        return storage_path('app/' . $this->file_path);
    }
}

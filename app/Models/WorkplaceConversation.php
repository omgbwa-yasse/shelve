<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkplaceConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workplace_id',
        'type',
        'name',
        'description',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(WorkplaceConversationParticipant::class, 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WorkplaceMessage::class, 'conversation_id');
    }

    public function participantsUsers()
    {
        return $this->belongsToMany(User::class, 'workplace_conversation_participants', 'conversation_id', 'user_id');
    }

    public function unreadCountFor(int $userId): int
    {
        $participant = WorkplaceConversationParticipant::where('conversation_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        if (!$participant) {
            return 0;
        }

        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->when($participant->last_read_at, fn ($q) => $q->where('created_at', '>', $participant->last_read_at))
            ->count();
    }

    public function isParticipant(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }
}

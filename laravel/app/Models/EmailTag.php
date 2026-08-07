<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmailTag extends Model
{
    use HasFactory, BelongsToOrganisation;

    protected $fillable = [
        'organisation_id',
        'name',
        'color',
        'created_by',
    ];

    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(EmailMessage::class, 'email_message_email_tag');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

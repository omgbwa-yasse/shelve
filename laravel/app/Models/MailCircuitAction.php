<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailCircuitAction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mail_circuit_id', 'mail_circuit_step_id', 'version', 'action',
        'from_status', 'to_status', 'actor_id', 'comment', 'metadata', 'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'version' => 'integer',
    ];

    public function circuit()
    {
        return $this->belongsTo(MailCircuit::class, 'mail_circuit_id');
    }

    public function step()
    {
        return $this->belongsTo(MailCircuitStep::class, 'mail_circuit_step_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

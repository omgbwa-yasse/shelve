<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeclassementRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'declassement_list_id',
        'record_physical_id',
        'added_by',
        'comment',
    ];

    public function declassementList()
    {
        return $this->belongsTo(DeclassementList::class, 'declassement_list_id');
    }

    public function record()
    {
        return $this->belongsTo(RecordPhysical::class, 'record_physical_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}

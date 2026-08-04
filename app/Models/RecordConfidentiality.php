<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordConfidentiality extends Model
{
    use HasFactory;

    protected $table = 'record_confidentialities';

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Notices utilisant ce niveau de confidentialité.
     */
    public function records()
    {
        return $this->hasMany(Record::class, 'confidentiality_id');
    }
}

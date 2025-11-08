<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RecordPhysical;

class RecordDocument extends Model
{
    use HasFactory;
    protected $fillable = ['path', 'crypt', 'size', 'extension', 'record_id'];

    public function record()
    {
        return $this->belongsTo(RecordPhysical::class);
    }
}

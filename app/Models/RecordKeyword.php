<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Keyword;
use App\Models\Record;

class RecordKeyword extends Model
{
    use HasFactory;
    protected $fillable = ['record_id', 'keyword_id'];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    public function keyword()
    {
        return $this->belongsTo(Keyword::class);
    }
}

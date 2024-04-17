<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Record;
use App\Models\Term;

class TermRecord extends Model
{
    use HasFactory;
    protected $fillable = ['record_id', 'term_id'];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Organisation;

class Office extends Model
{
    use HasFactory;
    protected $fillable = ['poste', 'description', 'organisation_id'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}

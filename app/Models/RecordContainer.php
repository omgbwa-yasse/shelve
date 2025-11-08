<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordContainer extends Model
{
    use HasFactory;

    protected $table = 'record_container';

    protected $primaryKey = ['record_id', 'container_id'];

    public $incrementing = false;


    protected $fillable = [
        'record_id',
        'container_id',
        'description',
        'creator_id',
    ];


    public function record()
    {
        return $this->belongsTo(RecordPhysical::class, 'record_id');
    }


    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id');
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlipRecordContainer extends Model
{
    use HasFactory;


    public $incrementing = false;


    protected $primaryKey = [
        'slip_record_id',
        'container_id'
    ];


    protected $keyType = 'array';


    protected $fillable = [
        'slip_record_id',
        'container_id',
        'description',
        'creator_id'
    ];




    public function slip()
    {
        return $this->hasOneThrough(
            Slip::class,
            SlipRecord::class,
            'id',            // Foreign key on SlipRecord table (local key on SlipRecordContainer)
            'id',            // Foreign key on Slip table
            'slip_record_id',// Local key on SlipRecordContainer table
            'slip_id'        // Local key on SlipRecord table
        );
    }


    public function slipRecord()
    {
        return $this->belongsTo(SlipRecord::class, 'slip_record_id');
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

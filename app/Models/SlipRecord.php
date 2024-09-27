<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Monolog\Level;

class SlipRecord extends Model
{
    use HasFactory;
//    use Searchable;

    protected $fillable = [
        'slip_id',
        'code',
        'name',
        'date_format',
        'date_start',
        'date_end',
        'date_exact',
        'content',
        'level_id',
        'width',
        'width_description',
        'support_id',
        'activity_id',
        'container_id',
        'creator_id',
    ];


    public $timestamps = true;


    public function slip()
    {
        return $this->belongsTo(Slip::class, 'slip_id');
    }

    public function level()
    {
        return $this->belongsTo(RecordLevel::class, 'level_id');
    }

    public function support()
    {
        return $this->belongsTo(RecordSupport::class,'support_id' );
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
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

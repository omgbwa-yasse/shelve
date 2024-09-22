<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Retention;
use App\Models\classification;


class RetentionActivity extends Model
{
    use HasFactory;

    protected $table = 'retention_activity';

    protected $fillable = ['retention_id', 'activity_id'];

    public function retention()
    {
        return $this->belongsTo(Retention::class, 'retention_id' );
    }

    public function classification()
    {
        return $this->belongsTo(activity::class, 'activity_id');
    }
}

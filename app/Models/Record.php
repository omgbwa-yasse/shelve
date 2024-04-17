<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Container;
use App\Models\Organisation;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Classification;
use App\Models\User;

class Record extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference', 'name', 'date_format', 'date_start', 'date_end', 'date_exact', 'description', 'level_id', 'status_id', 'support_id', 'classification_id', 'parent_id', 'container_id', 'transfer_id', 'user_id'
    ];

    public function status()
    {
        return $this->belongsTo(RecordStatus::class);
    }

    public function support()
    {
        return $this->belongsTo(RecordSupport::class);
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }

    public function parent()
    {
        return $this->belongsTo(Record::class);
    }

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

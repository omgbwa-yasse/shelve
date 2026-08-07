<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupPlanning extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_id',
        'frequence',
        'week_day',
        'month_day',
        'hour',
    ];



    public function backup()
    {
        return $this->belongsTo(Backup::class, 'backup_id');
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_time',
        'type',
        'description',
        'status',
        'user_id',
        'size',
        'backup_file',
        'path',
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];




    public function backupFiles()
    {
        return $this->hasMany(BackupFile::class);
    }




    public function backupPlannings()
    {
        return $this->hasMany(BackupPlanning::class);
    }



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}

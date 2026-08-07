<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_id',
        'path_original',
        'path_storage',
        'size',
        'hash',
    ];



    public function backup()
    {
        return $this->belongsTo(Backup::class, 'backup_id');
    }


}

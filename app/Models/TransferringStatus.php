<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transferring;

class TransferringStatus extends Model
{
    use HasFactory;

    protected $table = 'transferring_statuses';


    protected $fillable = [
        'name',
        'description',
    ];

    public function transferrings()
    {
        return $this->hasMany(Transferring::class);
    }

}

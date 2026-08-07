<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Container;


class Property extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'width', 'lengh', 'thinkness'
    ];

    public function containers()
    {
        return $this->hasMany(Container::class);
    }
}

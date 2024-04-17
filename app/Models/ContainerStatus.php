<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Container;

class ContainerStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'description'
    ];

    public function containers()
    {
        return $this->hasMany(Container::class);
    }
}

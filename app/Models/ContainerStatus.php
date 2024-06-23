<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Container;

class ContainerStatus extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'creator_id'];

    public function containers()
    {
        return $this->hasMany(Container::class, 'status_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}

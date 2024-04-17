<?

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class RetentionSort extends Model
{
    use HasFactory;
    protected $fillable = [
        'code', 'name', 'description'
    ];
}

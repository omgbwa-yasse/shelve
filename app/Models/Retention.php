namespace App\Models;

use App\Models\RetentionSort;
use Illuminate\Database\Eloquent\Model;

class Retention extends Model
{
    protected $fillable = [
        'duration', 'sort', 'reference', 'retention_sort_id'
    ];

    public function sort()
    {
        return $this->belongsTo(RetentionSort::class);
    }
}

<?

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class TransferStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'observation'
    ];
}

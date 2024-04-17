<?

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Organisation;
use App\Models\TransferStatus;

class Transfer extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference', 'name', 'date_creation', 'date_authorize', 'observation', 'organisation_id', 'transfer_status_id'
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function status()
    {
        return $this->belongsTo(TransferStatus::class);
    }
}

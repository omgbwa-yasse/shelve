namespace App\Models;

use App\Models\Organisation;
use App\Models\TransferStatus;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
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

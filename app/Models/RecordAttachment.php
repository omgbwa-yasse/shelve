<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pivot notice ↔ pièce jointe (table `record_physical_attachment`, FK `record_id`
 * repointée vers `records` en phase 4).
 *
 * Clé composite `(record_id, attachment_id)` sans colonne `id` : chaque ressource est
 * résolue sur ces deux clés (motif D04 SlipRecordAttachment). Le modèle est org-scopé
 * par héritage de sa notice parente dans le contrôleur.
 */
class RecordAttachment extends Model
{
    use HasFactory;

    protected $table = 'record_physical_attachment';

    protected $primaryKey = ['record_id', 'attachment_id'];

    protected $keyType = 'array';

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'record_id',
        'attachment_id',
    ];

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }
}

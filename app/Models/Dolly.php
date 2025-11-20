<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dolly extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'is_public',
        'created_by',
        'owner_organisation_id',
    ];

    public function mails()
    {
        return $this->belongsToMany(mail::class, 'dolly_mails', 'dolly_id', 'mail_id');
    }

    public function mailTransactions()
    {
        return $this->belongsToMany(MailTransaction::class, 'dolly_mail_transactions');
    }

    public function records()
    {
        return $this->belongsToMany(record::class, 'dolly_records', 'dolly_id', 'record_id');
    }

    public function communications()
    {
        return $this->belongsToMany(communication::class, 'dolly_communications', 'dolly_id', 'communication_id');
    }

    public function slips()
    {
        return $this->belongsToMany(Slip::class, 'dolly_slips', 'dolly_id', 'slip_id');
    }

    public function slipRecords()
    {
        return $this->belongsToMany(RecordPhysical::class, 'dolly_slip_records', 'dolly_id', 'record_id');
    }

    public function buildings()
    {
        return $this->belongsToMany(Building::class, 'dolly_buildings', 'dolly_id', 'building_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'dolly_rooms', 'dolly_id', 'room_id');
    }

    public function shelve()
    {
        return $this->belongsToMany(Shelf::class, 'dolly_shelves', 'dolly_id', 'shelf_id');
    }

    public function containers()
    {
        return $this->belongsToMany(Shelf::class, 'dolly_containers', 'dolly_id', 'container_id');
    }

    public function digitalFolders()
    {
        return $this->belongsToMany(RecordDigitalFolder::class, 'dolly_digital_folders', 'dolly_id', 'folder_id')
            ->withTimestamps();
    }

    public function digitalDocuments()
    {
        return $this->belongsToMany(RecordDigitalDocument::class, 'dolly_digital_documents', 'dolly_id', 'document_id')
            ->withTimestamps();
    }

    public function artifacts()
    {
        return $this->belongsToMany(RecordArtifact::class, 'dolly_artifacts', 'dolly_id', 'artifact_id')
            ->withTimestamps();
    }

    public function books()
    {
        return $this->belongsToMany(RecordBook::class, 'dolly_books', 'dolly_id', 'book_id')
            ->withTimestamps();
    }

    public function bookSeries()
    {
        return $this->belongsToMany(RecordBookPublisherSeries::class, 'dolly_book_series', 'dolly_id', 'series_id')
            ->withTimestamps();
    }

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ownerOrganisation(){
        return $this->belongsTo(Organisation::class, 'owner_organisation_id');
    }



    public static function categories()
    {
        $list = array(
            'mail',
            'communication',
            'building',
            'transferring',
            'room',
            'record',
            'slip',
            'slipRecord',
            'container',
            'shelf',
            'digital_folder',
            'digital_document',
            'artifact',
            'book',
            'book_series'
        );

        return collect($list);
    }

}

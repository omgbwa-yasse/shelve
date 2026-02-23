<?php

namespace App\Http\Controllers;
use App\Models\Dolly;
use App\Models\Container;
use App\Models\Activity;
use App\Models\floor;
use App\Models\shelf;
use App\Models\SlipStatus;
use App\Models\Room;
use App\Enums\CommunicationStatus;
use App\Models\MailPriority;
use App\Models\MailType;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\RecordLevel;
use Illuminate\Http\Request;

class DollyActionController extends Controller
{



    public function index(Request $request){
        if(isset($request->categ) && !empty($request->categ)){

            if ($request->categ == "mail") {
                switch ($request->action) {
                    case 'dates':  return $this->MailDate($request->id);
                    case 'new_date':  return $this->MailDateChange($request->id, $request->value);


                    case 'priority': return $this->MailPriority($request->id);
                    case 'new_priority': return $this->MailPriorityChange($request->id, $request->value);


                    case 'type':  return $this->MailType($request->id);
                    case 'new_type':  return $this->MailTypeChange($request->id, $request->value);


                    case 'archived': return $this->MailArchived($request->id);
                    case 'new_archived': return $this->MailArchivedChange($request->id, $request->value);

                    case 'clean' : return $this->mailDetach($request->id);
                    case 'delete' : return $this->mailDelete($request->id);
                }
             }

            if($request->categ == "record"){
                switch($request->action){
                    case 'dates' : return $this->RecordDate($request->id);
                    case 'new_date' : return $this->RecordDateChange($request->id, $request->value);

                    case 'level' : return $this->RecordLevel($request->id);
                    case 'new_level' : return $this->RecordLevelChange($request->id, $request->value);


                    case 'status' : return $this->RecordStatus($request->id);
                    case 'new_status' : return $this->RecordStatusChange($request->id, $request->value);

                    case 'container' : return $this->RecordContainer($request->id);
                    case 'new_container' : return $this->RecordContainerChange($request->id, $request->value);

                    case 'activity' : return $this->RecordActivity($request->id);
                    case 'new_activity' : return $this->RecordActivityChange($request->id, $request->value);

                    case 'clean' : return $this->recordDetach($request->id);
                    case 'delete' : return $this->recordDelete($request->id);


                }
            }

            if($request->categ == "communication"){
                switch($request->action){
                    case 'return_date' : return  $this->CommunicationReturn($request->id);
                    case 'new_return_date' : return  $this->CommunicationReturnchange($request->id, $request->value);

                    case 'return_date_effective' : return $this->CommunicationReturnEffective($request->id);
                    case 'new_return_date_effective' : return $this->CommunicationReturnEffectivechange($request->id, $request->value);

                    case 'status' : return $this->CommunicationStatus($request->id);
                    case 'new_status' : return $this->CommunicationStatuschange($request->id, $request->value);

                    case 'clean' : return $this->communicationDetach($request->id);
                    case 'delete' : return $this->communicationDelete($request->id);

                }
            }

            if($request->categ == "slip_record"){
                switch($request->action){
                    case 'container' : return $this->slipRecordContainer($request->id);
                    case 'new_container' : return $this->slipRecordContainerchange($request->id, $request->value);


                    case 'activity' : return $this->slipRecordActivity($request->id);
                    case 'new_activity' : return $this->slipRecordActivitychange($request->id, $request->value);


                    case 'support' : return $this->slipRecordSupport($request->id);
                    case 'new_support' : return $this->slipRecordSupportchange($request->id, $request->value);


                    case 'level' : return $this->slipRecordLevel($request->id);
                    case 'new_level' : return $this->slipRecordLevelchange($request->id, $request->value);


                    case 'dates' : return $this->slipRecordDate($request->id);
                    case 'new_dates' : return $this->slipRecordDatechange($request->id, $request->value);

                    case 'clean' : return $this->slipRecordDetach($request->id);

                    case 'delete' : return $this->slipRecordDelete($request->id);


                }
            }

            if($request->categ == "slip"){
                switch($request->action){
                    case 'status' : return $this->slipStatus($request->id);
                    case 'new_status' : return $this->slipStatuschange($request->id, $request->value);

                    case 'clean' : return $this->slipDetach($request->id);
                    case 'delete' : return $this->slipDelete($request->id);

                }
            }

            if($request->categ == "shelve"){
                switch($request->action){
                    case 'room' : return $this->shelfRoom($request->id);
                    case 'new_room' : return $this->shelfRoomchange($request->id, $request->value);
                    case 'clean' : return $this->shelfDetach($request->id);
                    case 'delete' : return $this->shelfDelete($request->id);

                }
            }

            if($request->categ == "container"){
                switch($request->action){
                    case 'shelf' : return $this->ContainerShelf($request->id);
                    case 'new_shelf' : return $this->ContainerShelfchange($request->id, $request->value);
                    case 'clean' :  return $this->containerDetach($request->id);
                    case 'delete' : return $this->containerDelete($request->id);
                }
            }

            if($request->categ == "room"){
                switch($request->action){
                    case 'floor' : return $this->RoomFloor($request->id);
                    case 'new_floor' : return $this->ContainerFloorchange($request->id, $request->value);
                    case 'clean' :  return $this->roomDetach($request->id);
                    case 'delete' : return $this->roomDelete($request->id);
                }
            }

            if($request->categ == "digital_folder"){
                switch($request->action){
                    case 'export_seda' : return $this->digitalFolderExportSeda($request->id);
                    case 'export_inventory' : return $this->digitalFolderExportInventory($request->id);
                    case 'clean' : return $this->digitalFolderDetach($request->id);
                    case 'delete' : return $this->digitalFolderDelete($request->id);
                }
            }

            if($request->categ == "digital_document"){
                switch($request->action){
                    case 'export_seda' : return $this->digitalDocumentExportSeda($request->id);
                    case 'export_inventory' : return $this->digitalDocumentExportInventory($request->id);
                    case 'clean' : return $this->digitalDocumentDetach($request->id);
                    case 'delete' : return $this->digitalDocumentDelete($request->id);
                }
            }

            if($request->categ == "artifact"){
                switch($request->action){
                    case 'export_inventory' : return $this->artifactExportInventory($request->id);
                    case 'clean' : return $this->artifactDetach($request->id);
                    case 'delete' : return $this->artifactDelete($request->id);
                }
            }



        }

}


    // Suppression des chariots

    public function mailDelete(int $id) {
        $this->mailDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('mails');
        foreach ($dolly->mails as $mail) {
            $mail->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }



    public function recordDelete(int $id) {
        $this->recordDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('records');
        foreach ($dolly->records as $record) {
            $record->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }


    public function communicationDelete(int $id) {
        $this->communicationDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('communications');
        foreach ($dolly->communications as $communication) {
            $communication->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }



    public function slipDelete(int $id) {
        $this->slipDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('slips');
        foreach ($dolly->slips as $slip) {
            $slip->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }



    public function containerDelete(int $id) {
        $this->containerDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('containers');
        foreach ($dolly->containers as $container) {
            $container->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }


    public function roomDelete(int $id) {
        $this->roomDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('rooms');
        foreach ($dolly->rooms as $room) {
            $room->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }


    public function shelfDelete(int $id) {
        $this->shelfDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('shelves');
        foreach ($dolly->shelves as $shelf) {
            $shelf->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }





    // Vider les chariot

    public function mailDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->mails as $mail){
            $dolly->mails()->detach($mail->id);
        }
    }


    public function recordDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->records as $record){
            $dolly->records()->detach($record->id);
        }
    }

    public function communicationDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->communications as $communication){
            $dolly->communications()->detach($communication->id);
        }
    }


    public function containerDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->containers as $container){
            $dolly->containers()->detach($container->id);
        }
    }


    public function shelfDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->shelve as $shelf){
            $dolly->shelve()->detach($shelf->id);
        }
    }

    public function slipRecordDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->slipRecords as $record){
            $dolly->slipRecords()->detach($record->id);
        }
    }

    public function slipDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->slips as $slip){
            $dolly->slips()->detach($slip->id);
        }
    }


    public function roomDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->rooms as $room){
            $dolly->rooms()->detach($room->id);
        }
    }




    // Mail

    public function MailDate(INT $id){
        $dolly = dolly::findOrFail($id);
        $levels = RecordLevel::all();
        return view('dollies.actions.mailDateForm', compact('dolly','levels'))->with('success', 'Dolly created successfully.');
    }


    public function MailDateChange(int $id, int $value)
    {
        $dolly = Dolly::findOrFail($id);
        foreach ($dolly->mails as $mail) {
            $mail->update(['date_exact' => $value]);
        }
        return view('dollies.show', ['dolly' => $dolly]);
    }


    public function MailPriority(INT $id){
        $dolly = dolly::findOrFail($id);
        $priorities = MailPriority::all();
        return view('dollies.actions.mailPriorityForm', compact('dolly','priorities'))->with('success', 'Dolly created successfully.');
    }
    public function MailPriorityChange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('mails');
        foreach ($dolly->mails as $mail) {
            $mail->update(['priority_id' => $value]);
        }
    }


    public function MailType(INT $id){
        $dolly = dolly::findOrFail($id);
        $types = MailType::all();
        return view('dollies.actions.mailTypeForm', compact('dolly','types'))->with('success', 'Dolly created successfully.');
    }

    public function MailTypeChange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('mails');
        foreach ($dolly->mails as $mail) {
            $mail->update(['type_id' => $value]);
        }
    }



    public function MailArchived(INT $id){
        $dolly = dolly::findOrFail($id);
        return view('dollies.actions.mailArchivedForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    public function MailArchivedChange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('mails');
        foreach ($dolly->mails as $mail) {
            $mail->update(['is_achived' => $value]);
        }
    }






    /**
     * Summary of xxxx
     * @return void
     */


     public function RecordLevel(INT $id){
        $dolly = dolly::findOrFail($id);
        $levels = RecordLevel::all();
        return view('dollies.actions.recordLevelForm', compact('dolly','levels'))->with('success', 'Dolly created successfully.');
    }


    public function RecordLevelChange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('records');
        foreach ($dolly->records as $record) {
            $record->update(['level_id' => $value]);
        }
    }


    public function RecordStatus(INT $id){
        $dolly = dolly::findOrFail($id);
        $statuses = RecordStatus::all();
        return view('dollies.actions.recordStatusForm', compact('dolly','statuses'))->with('success', 'Dolly created successfully.');
    }


    public function RecordSupport(INT $id){
        $dolly = Dolly::findOrFail($id);
        $supports = RecordSupport::all();
        return view('dollies.actions.recordSupportForm', compact('dolly','supports'))->with('success', 'Dolly created successfully.');
    }





    public function RecordStatusChange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('records');
        foreach ($dolly->records as $record) {
            $record->update(['status_id' => $value]);
        }
    }




     public function RecordDate(INT $id){
        $dolly = dolly::findOrFail($id);
        return view('dollies.actions.recordDateForm', compact('dolly'))->with('success', 'Dolly created successfully.');
    }


    public function recordDateChange(int $id, array $value) {
        $dolly = Dolly::findOrFail($id);
        $dolly->load('records');

        foreach ($dolly->records as $record) {
            if (!isset($value['date_exact']) || !isset($value['date_start'])) {
                $record->update(['date_exact' => $value['date_exact']]);
            } else {
                $record->update([
                    'date_start' => $value['date_start'],
                    'date_end' => $value['date_end'],
                ]);
            }
        }
    }


    public function RecordActivity(INT $id){
        $dolly = dolly::findOrFail($id);
        $activities = activity::all();
        return view('dollies.actions.recordActivityForm', compact('dolly','activities'))->with('success', 'Dolly created successfully.');
    }


    public function RecordActivityChange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('records');
        foreach ($dolly->records as $record) {
            $record->update(['activity_id' => $value]);
        }
    }


    public function RecordContainer(INT $id){
        $dolly = dolly::findOrFail($id);
        $containers = container::all();
        return view('dollies.actions.recordContainerForm', compact('dolly','containers'))->with('success', 'Dolly created successfully.');
    }



    public function RecordContainerChange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('records');
        foreach ($dolly->records as $record) {
            // Attach container via pivot if not already linked
            if (!$record->containers()->where('containers.id', $value)->exists()) {
                $record->containers()->attach($value, [
                    'description' => null,
                    'creator_id' => auth()->id(),
                ]);
            }
        }
    }















    /**
     * Summary of xxxx
     * @return void
     */

     Public function CommunicationReturn(INT $id){
        $dolly = dolly::findOrFail($id);
        return view('dollies.actions.CommunicationReturnForm', compact('dolly'))->with('success', 'Dolly created successfully.');
     }

     Public function CommunicationReturnchange(INT $id, STRING $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('communications');
        foreach ($dolly->communications as $communication) {
            $communication->update(['return_date' => $value]);
        }
     }


     Public function CommunicationReturnEffective(INT $id){
        $dolly = dolly::findOrFail($id);
        return view('dollies.actions.CommunicationReturnEffectiveForm', compact('dolly'))->with('success', 'Dolly created successfully.');
     }

     Public function CommunicationReturEffectivechange(INT $id, STRING $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('communications');
        foreach ($dolly->communications as $communication) {
            $communication->update(['return_effective' => $value]);
        }
     }


     Public function CommunicationStatus(INT $id){
        $dolly = dolly::findOrFail($id);
        $statuses = collect(CommunicationStatus::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label()
            ];
        });
        return view('dollies.actions.CommunicationStatusForm', compact('dolly','statuses'))->with('success', 'Dolly created successfully.');
     }

     Public function CommunicationStatuschange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('communications');
        foreach ($dolly->communications as $communication) {
            $communication->update(['status' => $value]);
        }
     }


    // SLIP


    Public function slipStatus(INT $id){
        $dolly = Dolly::findOrFail($id);
        $statuses = SlipStatus::all();
        return view('dollies.actions.slipStatusForm', compact('dolly','statuses'))->with('success', 'Dolly created successfully.');
     }

     Public function slipStatuschange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('slips');
        foreach ($dolly->slips as $slip) {
            $slip->update(['slip_status_id' => $value]);
        }
     }







     // SLIP RECORD



     Public function slipRecordContainer(INT $id){
        $dolly = dolly::findOrFail($id);
        $containers = container::all();
        return view('dollies.actions.slipRecordContainerForm', compact('dolly','containers'))->with('success', 'Dolly created successfully.');
     }

     Public function slipRecordContainerchange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('slipRecords');
        foreach ($dolly->slipRecords as $record) {
            // slipRecords still have container_id field? leaving unchanged if model structure differs
            $record->update(['container_id' => $value]);
        }
     }




     Public function slipRecordActivity(INT $id){
        $dolly = Dolly::findOrFail($id);
        $activities = Activity::all();
        return view('dollies.actions.slipRecordActivityForm', compact('dolly','activities'))->with('success', 'Dolly created successfully.');
     }


     Public function slipRecordActivitychange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('slipRecords');
        foreach ($dolly->slipRecords as $record) {
            $record->update(['activity_id' => $value]);
        }
     }




     Public function slipRecordSupport(INT $id){
        $dolly = Dolly::findOrFail($id);
        $supports = RecordSupport::all();
        return view('dollies.actions.slipRecordSupportForm', compact('dolly','supports'))->with('success', 'Dolly created successfully.');
     }

     Public function slipRecordSupportchange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('slipRecords');
        foreach ($dolly->slipRecords as $record) {
            $record->update(['support_id' => $value]);
        }
     }



     Public function slipRecordLevel(INT $id){
        $dolly = Dolly::findOrFail($id);
        $levels = RecordLevel::all();
        return view('dollies.actions.slipRecordLevelForm', compact('dolly','levels'))->with('success', 'Dolly created successfully.');
     }

     Public function slipRecordLevelchange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('slipRecords');
        foreach ($dolly->slipRecords as $record) {
            $record->update(['level_id' => $value]);
        }
     }


     Public function slipRecordDate(INT $id){
        $dolly = Dolly::findOrFail($id);
        return view('dollies.actions.slipRecordDateForm', compact('dolly'))->with('success', 'Dolly created successfully.');
     }

     public function slipRecordDateChange(int $id, string $value) {
        $dolly = Dolly::findOrFail($id);
        $dolly->load('slipRecords');

        foreach ($dolly->slipRecords as $record) {
            $record->update(['date_exact' => $value]);
        }
    }

















     /**
     * Summary of xxxx
     * @return void
     */



     Public function shelfRoom(INT $id){
        $dolly = Dolly::findOrFail($id);
        $rooms = Room::all();
        $rooms->load('floor');
        return view('dollies.actions.shelfRoomForm', compact('dolly','rooms'))->with('success', 'Dolly created successfully.');
     }

     Public function shelfRoomchange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('shelve');
        foreach ($dolly->shelve as $shelf) {
            $shelf->update(['room_id' => $value]);
        }
     }










    // Container


    Public function ContainerShelf(INT $id){
        $dolly = Dolly::findOrFail($id);
        $shelves = Shelf::all();
        $shelves->load('room');
        return view('dollies.actions.ContainerShelfForm', compact('dolly','shelves'))->with('success', 'Dolly created successfully.');
    }

    Public function ContainerShelfchange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('containers');
        foreach ($dolly->slipRecords as $record) {
            $record->update(['shelve_id' => $value]);
        }
    }









    Public function RoomFloor(INT $id){
        $dolly = Dolly::findOrFail($id);
        $floors = Floor::all();
        $floors->load('building');
        return view('dollies.actions.RoomFloorForm', compact('floors','dolly'))->with('success', 'Dolly created successfully.');
    }

    Public function RoomFloorChange(INT $id, INT $value){
        $dolly = Dolly::findOrFail($id);
        $dolly->load('rooms');
        foreach ($dolly->rooms as $room) {
            $room->update(['floor_id' => $value]);
        }
    }


    // ==================== DIGITAL FOLDERS ====================

    public function digitalFolderDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->digitalFolders as $folder){
            $dolly->digitalFolders()->detach($folder->id);
        }
    }

    public function digitalFolderDelete(int $id) {
        $this->digitalFolderDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('digitalFolders');
        foreach ($dolly->digitalFolders as $folder) {
            $folder->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Dossiers numériques supprimés du chariot.');
    }

    public function digitalFolderExportSeda(int $id) {
        $dolly = Dolly::findOrFail($id);
        $dolly->load('digitalFolders');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ArchiveTransfer xmlns="fr:gouv:culture:archivesdefrance:seda:v2.1"></ArchiveTransfer>');
        $xml->addChild('Date', date('c'));
        $xml->addChild('MessageIdentifier', 'DOLLY_' . $dolly->id . '_' . time());

        $dataObjectPackage = $xml->addChild('DataObjectPackage');

        foreach ($dolly->digitalFolders as $folder) {
            $descriptiveMetadata = $dataObjectPackage->addChild('DescriptiveMetadata');
            $archiveUnit = $descriptiveMetadata->addChild('ArchiveUnit');
            $archiveUnit->addAttribute('id', 'FOLDER_' . $folder->id);

            $content = $archiveUnit->addChild('Content');
            $content->addChild('DescriptionLevel', 'RecordGrp');
            $content->addChild('Title', htmlspecialchars($folder->name));
            $content->addChild('Description', htmlspecialchars($folder->description ?? ''));
            $content->addChild('OriginatingSystemId', $folder->code);
        }

        $filename = 'seda_digital_folders_' . $dolly->id . '_' . date('Y-m-d_His') . '.xml';

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function digitalFolderExportInventory(int $id) {
        $dolly = Dolly::findOrFail($id);
        $dolly->load('digitalFolders');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dollies.exports.digital_folders_inventory', [
            'dolly' => $dolly,
            'folders' => $dolly->digitalFolders
        ]);

        return $pdf->download('inventaire_dossiers_numeriques_' . $dolly->id . '_' . date('Y-m-d') . '.pdf');
    }

    // ==================== DIGITAL DOCUMENTS ====================

    public function digitalDocumentDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->digitalDocuments as $document){
            $dolly->digitalDocuments()->detach($document->id);
        }
    }

    public function digitalDocumentDelete(int $id) {
        $this->digitalDocumentDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('digitalDocuments');
        foreach ($dolly->digitalDocuments as $document) {
            $document->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Documents numériques supprimés du chariot.');
    }

    public function digitalDocumentExportSeda(int $id) {
        $dolly = Dolly::findOrFail($id);
        $dolly->load('digitalDocuments');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ArchiveTransfer xmlns="fr:gouv:culture:archivesdefrance:seda:v2.1"></ArchiveTransfer>');
        $xml->addChild('Date', date('c'));
        $xml->addChild('MessageIdentifier', 'DOLLY_DOC_' . $dolly->id . '_' . time());

        $dataObjectPackage = $xml->addChild('DataObjectPackage');

        foreach ($dolly->digitalDocuments as $document) {
            $descriptiveMetadata = $dataObjectPackage->addChild('DescriptiveMetadata');
            $archiveUnit = $descriptiveMetadata->addChild('ArchiveUnit');
            $archiveUnit->addAttribute('id', 'DOC_' . $document->id);

            $content = $archiveUnit->addChild('Content');
            $content->addChild('DescriptionLevel', 'Item');
            $content->addChild('Title', htmlspecialchars($document->name));
            $content->addChild('Description', htmlspecialchars($document->description ?? ''));
            $content->addChild('OriginatingSystemId', $document->code);
            if ($document->type) {
                $content->addChild('DocumentType', htmlspecialchars($document->type));
            }
        }

        $filename = 'seda_digital_documents_' . $dolly->id . '_' . date('Y-m-d_His') . '.xml';

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function digitalDocumentExportInventory(int $id) {
        $dolly = Dolly::findOrFail($id);
        $dolly->load('digitalDocuments');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dollies.exports.digital_documents_inventory', [
            'dolly' => $dolly,
            'documents' => $dolly->digitalDocuments
        ]);

        return $pdf->download('inventaire_documents_numeriques_' . $dolly->id . '_' . date('Y-m-d') . '.pdf');
    }

    // ==================== ARTIFACTS ====================

    public function artifactDetach(int $id) {
        $dolly = Dolly::findOrFail($id);
        foreach($dolly->artifacts as $artifact){
            $dolly->artifacts()->detach($artifact->id);
        }
    }

    public function artifactDelete(int $id) {
        $this->artifactDetach($id);
        $dolly = Dolly::findOrFail($id);
        $dolly->load('artifacts');
        foreach ($dolly->artifacts as $artifact) {
            $artifact->delete();
        }
        return redirect()->route('dolly.index')->with('success', 'Artefacts supprimés du chariot.');
    }

    public function artifactExportInventory(int $id) {
        $dolly = Dolly::findOrFail($id);
        $dolly->load('artifacts');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dollies.exports.artifacts_inventory', [
            'dolly' => $dolly,
            'artifacts' => $dolly->artifacts
        ]);

        return $pdf->download('inventaire_artefacts_' . $dolly->id . '_' . date('Y-m-d') . '.pdf');
    }

}



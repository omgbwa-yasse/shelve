<?php

namespace App\Http\Controllers;
use App\Models\Dolly;
use Illuminate\Http\Request;

class DollyActionController extends Controller
{
    public $id;
    public $value;

    public function __construct()
    {
        $this->id = isset($_GET['dolly_id']) ? $_GET['dolly_id'] : null;
        $this->value = isset($_GET['value']) ? $_GET['value'] : null;
    }


    public function index(){
        if(isset($_GET['categ']) && !empty($_GET['categ'])){

                if ($_GET['categ'] == "mail") {
                    switch ($_GET['sub']) {
                        case 'date':
                            return $this->MailDate($this->id);
                        case 'priority':
                            return $this->MailPriority($this->id);
                        case 'type':
                            return $this->MailType($this->id);
                        case 'archived':
                            return $this->MailArchived($this->id);
                        case 'new_date':
                            return $this->MailDateChange($this->id, $this->value);
                        case 'new_priority':
                            return $this->MailPriorityChange($this->id, $this->value);
                        case 'new_type':
                            return $this->MailTypeChange($this->id, $this->value);
                        case 'new_archived':
                            return $this->MailArchivedChange($this->id, $this->value);
                    }
             }

            if($_GET['categ'] == "record"){
                switch($_GET['sub']){
                    case 'date' : return $this->RecordDate($this->id);
                    case 'level' : return $this->RecordLevel($this->id);
                    case 'status' : return $this->RecordStatus($this->id);
                    case 'container' : return $this->RecordContainer($this->id);
                    case 'activity' : return $this->RecordActivity($this->id);
                    case 'new_date' : return $this->RecordDateChange($this->id, $this->value);
                    case 'new_level' : return $this->RecordLevelChange($this->id, $this->value);
                    case 'new_status' : return $this->RecordStatusChange($this->id, $this->value);
                    case 'new_container' : return $this->RecordContainerChange($this->id, $this->value);
                    case 'new_activity' : return $this->RecordActivityChange($this->id, $this->value);
                }
            }

            if($_GET['categ'] == "communication"){
                switch($_GET['sub']){
                    case 'date_return' : return  $this->CommunicationReturn($this->id);
                    case 'return_effective' : return $this->CommunicationReturEffective($this->id);
                    case 'status' : return $this->CommunicationStatus($this->id);
                    case 'new_date_return' : return  $this->CommunicationReturnchange($this->id, $this->value);
                    case 'new_return_effective' : return $this->CommunicationReturEffectivechange($this->id, $this->value);
                    case 'new_status' : return $this->CommunicationStatuschange($this->id, $this->value);
                }
            }

            if($_GET['categ'] == "slipRecord"){
                switch($_GET['sub']){
                    case 'container' : return $this->slipRecordContainer($this->id);
                    case 'activity' : return $this->slipRecordActivity($this->id);
                    case 'support' : return $this->slipRecordSupport($this->id);
                    case 'level' : return $this->slipRecordLevel($this->id);
                    case 'dates' : return $this->slipRecordDate($this->id);
                    case 'new_container' : return $this->slipRecordContainerchange($this->id, $this->value);
                    case 'new_activity' : return $this->slipRecordActivitychange($this->id, $this->value);
                    case 'new_support' : return $this->slipRecordSupportchange($this->id, $this->value);
                    case 'new_level' : return $this->slipRecordLevelchange($this->id, $this->value);
                    case 'new_dates' : return $this->slipRecordDatechange($this->id, $this->value);
                }
            }

            if($_GET['categ'] == "shelf"){
                switch($_GET['sub']){
                    case 'room' : return $this->shlefRoomc($this->id);
                    case 'new_room' : return $this->shlefRoomchange($this->id, $this->value);
                }
            }

            if($_GET['categ'] == "container"){
                switch($_GET['sub']){
                    case 'shelf' : return $this->ContainerShelf($this->id);
                    case 'new_shelf' : return $this->ContainerShelfchange($this->id, $this->value);
                }
            }

            if($_GET['categ'] == "room"){
                switch($_GET['sub']){
                    case 'floor' : return $this->RoomFloor($this->id);
                    case 'new_floor' : return $this->RoomFloorChange($this->id, $this->value);
                }
            }
        }

}



    public function MailDate(INT $dolly_id){
        return view('dollies.actions.mailDateForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }


    public function MailDateChange(int $dolly_id, $value)
    {
        $dolly = Dolly::findOrFail($dolly_id);
        $mails = $dolly->mails;

        foreach ($mails as $mail) {
            $mail->update(['date_exact' => $value]);
        }

        return view('dollies.show', ['dolly' => $dolly]);
    }





    public function MailPriority(INT $dolly_id){
        return view('dollies.actions.mailPriorityForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }
    public function MailPriorityChange(INT $dolly_id, STRING $value){

    }




    public function MailType(INT $dolly_id){
        return view('dollies.actions.mailTypeForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    public function MailTypeChange(INT $dolly_id, STRING $value){

    }




    public function MailArchived(INT $dolly_id){
        return view('dollies.actions.mailArchivedForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    public function MailArchivedChange(INT $dolly_id, STRING $value){

    }


    /**
     * Summary of xxxx
     * @return void
     */


     public function RecordDate(INT $dolly_id){
        return view('dollies.actions.recordDateForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    public function RecordDateChange(INT $dolly_id, STRING $value){
        echo "Date";
    }




    public function RecordLevel(INT $dolly_id){
        return view('dollies.actions.recordLevelForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    public function RecordLevelChange(INT $dolly_id, STRING $value){

    }




    public function RecordStatus(INT $dolly_id){
        return view('dollies.actions.recordStatusForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    public function RecordStatusChange(INT $dolly_id, STRING $value){

    }





    public function RecordContainer(INT $dolly_id){
        return view('dollies.actions.recordContainerForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    public function RecordContainerChange(INT $dolly_id, STRING $value){

    }




    public function RecordActivity(INT $dolly_id){
        return view('dollies.actions.recordActivityForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }
    public function RecordActivityChange(INT $dolly_id, STRING $value){

    }



    /**
     * Summary of xxxx
     * @return void
     */

     Public function CommunicationReturn(INT $dolly_id){
        return view('dollies.actions.recordActivityForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function CommunicationReturnchange(INT $dolly_id, STRING $value){

     }




     Public function CommunicationReturEffective(INT $dolly_id){
        return view('dollies.actions.CommunicationReturnEffectiveForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function CommunicationReturEffectivechange(INT $dolly_id, STRING $value){

     }





     Public function CommunicationStatus(INT $dolly_id){
        return view('dollies.actions.CommunicationStatusForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function CommunicationStatuschange(INT $dolly_id, STRING $value){

     }




     /**
     * Summary of xxxx
     * @return void
     */



     Public function slipRecordContainer(INT $dolly_id){
        return view('dollies.actions.slipRecordContainerForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function slipRecordContainerchange(INT $dolly_id, STRING $value){

     }




     Public function slipRecordActivity(INT $dolly_id){
        return view('dollies.actions.slipRecordActivityForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function slipRecordActivitychange(INT $dolly_id, STRING $value){

     }




     Public function slipRecordSupport(INT $dolly_id){
        return view('dollies.actions.slipRecordSupportForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function slipRecordSupportchange(INT $dolly_id, STRING $value){

     }



     Public function slipRecordLevel(INT $dolly_id){
        return view('dollies.actions.slipRecordLevelForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function slipRecordLevelchange(INT $dolly_id, STRING $value){

     }


     Public function slipRecordDate(INT $dolly_id){
        return view('dollies.actions.slipRecordDateForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function slipRecordDatechange(INT $dolly_id, STRING $value){

     }



     /**
     * Summary of xxxx
     * @return void
     */



     Public function shelfRoom(INT $dolly_id){
        return view('dollies.actions.shelfRoomForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
     }

     Public function shlefRoomchange(INT $dolly_id, STRING $value){

     }




    Public function ContainerShelf(INT $dolly_id){
        return view('dollies.actions.ContainerShelfForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    Public function ContainerShelfchange(INT $dolly_id, STRING $value){

    }




    Public function RoomFloor(INT $dolly_id){
        return view('dollies.actions.RoomFloorForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    Public function RoomFloorChange(INT $dolly_id, STRING $value){

    }



}


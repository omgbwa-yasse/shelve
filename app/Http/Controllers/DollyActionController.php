<?php

namespace App\Http\Controllers;
use App\Models\Dolly;
use Illuminate\Http\Request;

class DollyActionController extends Controller
{
    public function index(){
        if(isset($_GET['categ']) && !empty($_GET['categ'])){

            if($_GET['categ'] == "mail"){
                switch($_GET['sub']){
                    case 'date' : return $this->MailDate($_GET['id']);
                    case 'priority' : return $this->MailPriority($_GET['id']);
                    case 'type' : return $this->MailType($_GET['id']);
                    case 'archived' : return $this->MailArchived($_GET['id']);
                    case 'new_date' : return $this->MailDateChange($_GET['id'], $_GET['value']);
                    case 'new_priority' : return $this->MailPriorityChange($_GET['id'], $_GET['value']);
                    case 'new_type' : return $this->MailTypeChange($_GET['id'], $_GET['value']);
                    case 'new_archived' : return $this->MailArchivedChange($_GET['id'], $_GET['value']);
                }
            }

            if($_GET['categ'] == "record"){
                switch($_GET['sub']){
                    case 'date' : return $this->RecordDateChange($_GET['id']);
                    case 'level' : return $this->RecordLevelChange($_GET['id']);
                    case 'status' : return $this->RecordStatusChange($_GET['id']);
                    case 'container' : return $this->RecordContainerChange($_GET['id']);
                    case 'activity' : return $this->RecordActivityChange($_GET['id']);
                    case 'new_date' : return $this->RecordDateChange($_GET['id'], $_GET['value']);
                    case 'new_level' : return $this->RecordLevelChange($_GET['id'], $_GET['value']);
                    case 'new_status' : return $this->RecordStatusChange($_GET['id'], $_GET['value']);
                    case 'new_container' : return $this->RecordContainerChange($_GET['id'], $_GET['value']);
                    case 'new_activity' : return $this->RecordActivityChange($_GET['id'], $_GET['value']);
                }
            }

            if($_GET['categ'] == "communication"){
                switch($_GET['sub']){
                    case 'date_return' : return  $this->CommunicationReturnchange($_GET['id']);
                    case 'return_effective' : return $this->CommunicationReturEffectivechange($_GET['id']);
                    case 'status' : return $this->CommunicationStatuschange($_GET['id']);
                    case 'new_date_return' : return  $this->CommunicationReturnchange($_GET['id'], $_GET['value']);
                    case 'new_return_effective' : return $this->CommunicationReturEffectivechange($_GET['id'], $_GET['value']);
                    case 'new_status' : return $this->CommunicationStatuschange($_GET['id'], $_GET['value']);
                }
            }

            if($_GET['categ'] == "slipRecord"){
                switch($_GET['sub']){
                    case 'container' : return $this->slipRecordContainerchange($_GET['id']);
                    case 'activity' : return $this->slipRecordActivitychange($_GET['id']);
                    case 'support' : return $this->slipRecordSupportchange($_GET['id']);
                    case 'level' : return $this->slipRecordLevelchange($_GET['id']);
                    case 'dates' : return $this->slipRecordDatechange($_GET['id']);
                    case 'new_container' : return $this->slipRecordContainerchange($_GET['id'], $_GET['value']);
                    case 'new_activity' : return $this->slipRecordActivitychange($_GET['id'], $_GET['value']);
                    case 'new_support' : return $this->slipRecordSupportchange($_GET['id'], $_GET['value']);
                    case 'new_level' : return $this->slipRecordLevelchange($_GET['id'], $_GET['value']);
                    case 'new_dates' : return $this->slipRecordDatechange($_GET['id'], $_GET['value']);
                }
            }

            if($_GET['categ'] == "shelf"){
                switch($_GET['sub']){
                    case 'room' : return $this->shlefRoomchange($_GET['id']);
                    case 'new_room' : return $this->shlefRoomchange($_GET['id'], $_GET['value']);
                }
            }

            if($_GET['categ'] == "container"){
                switch($_GET['sub']){
                    case 'shelf' : return $this->ContainerShelfchange($_GET['id']);
                    case 'new_shelf' : return $this->ContainerShelfchange($_GET['id'], $_GET['value']);
                }
            }

            if($_GET['categ'] == "room"){
                switch($_GET['sub']){
                    case 'floor' : return $this->RoomFloorChange($_GET['id']);
                    case 'new_floor' : return $this->RoomFloorChange($_GET['id'], $_GET['value']);
                }
            }
        }

}



    public function MailDate(){
        return view('dollies.mails.dateForm')->with('success', 'Dolly created successfully.');
    }

    public function MailDateChange(INT $dolly_id, STRING $value){
        $dollies = dolly::findOrFail($dolly_id);
        $mails = $dollies->mails;
        foreach($mails as $mail){
            $mail->update([
                'date_exact' => $value,
            ]);
            return view('dollies.show', $dollies->id);
        }
    }






    public function MailPriority(){

    }
    public function MailPriorityChange(){

    }




    public function MailType(){

    }

    public function MailTypeChange(){

    }




    public function MailArchived(){

    }

    public function MailArchivedChange(){

    }


    /**
     * Summary of xxxx
     * @return void
     */


     public function RecordDate(){
            echo "Date";
    }

    public function RecordDateChange(){
        echo "Date";
    }




    public function RecordLevel(){

    }

    public function RecordLevelChange(){

    }




    public function RecordStatus(){

    }

    public function RecordStatusChange(){

    }





    public function RecordContainer(){

    }

    public function RecordContainerChange(){

    }




    public function RecordActivity(){

    }
    public function RecordActivityChange(){

    }



    /**
     * Summary of xxxx
     * @return void
     */

     Public function CommunicationReturn(){

     }

     Public function CommunicationReturnchange(){

     }




     Public function CommunicationReturEffective(){

     }

     Public function CommunicationReturEffectivechange(){

     }





     Public function CommunicationStatus(){

     }

     Public function CommunicationStatuschange(){

     }




     /**
     * Summary of xxxx
     * @return void
     */



     Public function slipRecordContainer(){

     }

     Public function slipRecordContainerchange(){

     }




     Public function slipRecordActivity(){

     }

     Public function slipRecordActivitychange(){

     }




     Public function slipRecordSupport(){

     }

     Public function slipRecordSupportchange(){

     }



     Public function slipRecordLevel(){

     }

     Public function slipRecordLevelchange(){

     }


     Public function slipRecordDate(){

     }

     Public function slipRecordDatechange(){

     }



     /**
     * Summary of xxxx
     * @return void
     */



     Public function shlefRoom(){

     }

     Public function shlefRoomchange(){

     }




    Public function ContainerShelf(){

    }

    Public function ContainerShelfchange(){

    }




    Public function RoomFloor(){

    }

    Public function RoomFloorChange(){

    }



}


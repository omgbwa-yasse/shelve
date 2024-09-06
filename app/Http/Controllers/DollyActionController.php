<?php

namespace App\Http\Controllers;
use App\Models\Dolly;
use App\Models\Container;
use App\Models\Activity;
use App\Models\MailArchiving;
use App\Models\floor;
use App\Models\shelf;
use App\Models\Room;
use App\Models\MailPriority;
use App\Models\MailType;
use App\Models\RecordStatus;
use App\Models\RecordLevel;
use Illuminate\Http\Request;

class DollyActionController extends Controller
{



    public function index(Request $request){
        if(isset($request->categ) && !empty($request->categ)){

                if ($request->categ == "mail") {
                    switch ($request->action) {
                        case 'dates':
                            return $this->MailDate($request->id);
                        case 'new_date':
                            return $this->MailDateChange($request->id, $request->value);


                        case 'priority':
                            return $this->MailPriority($request->id);
                        case 'new_priority':
                            return $this->MailPriorityChange($request->id, $request->value);


                        case 'type':
                            return $this->MailType($request->id);
                        case 'new_type':
                            return $this->MailTypeChange($request->id, $request->value);


                        case 'archived':
                            return $this->MailArchived($request->id);
                        case 'new_archived':
                            return $this->MailArchivedChange($request->id, $request->value);
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
                }
            }

            if($request->categ == "communication"){
                switch($request->action){
                    case 'date_return' : return  $this->CommunicationReturn($request->id);
                    case 'new_date_return' : return  $this->CommunicationReturnchange($request->id, $request->value);

                    case 'return_effective' : return $this->CommunicationReturEffective($request->id);
                    case 'new_return_effective' : return $this->CommunicationReturEffectivechange($request->id, $request->value);

                    case 'status' : return $this->CommunicationStatus($request->id);
                    case 'new_status' : return $this->CommunicationStatuschange($request->id, $request->value);
                }
            }

            if($request->categ == "slipRecord"){
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
                }
            }

            if($request->categ == "shelve"){
                switch($request->action){
                    case 'room' : return $this->shelfRoom($request->id);
                    case 'new_room' : return $this->shlefRoomchange($request->id, $request->value);
                }
            }

            if($request->categ == "container"){
                switch($request->action){
                    case 'shelf' : return $this->ContainerShelf($request->id);
                    case 'new_shelf' : return $this->ContainerShelfchange($request->id, $request->value);
                }
            }

            if($request->categ == "room"){
                switch($request->action){
                    case 'floor' : return $this->RoomFloor($request->id);
                    case 'new_floor' : return $this->ContainerFloorchange($request->id, $request->value);
                }
            }



        }

}



    public function MailDate(INT $id){
        $dolly = dolly::findOrFail($id);
        $levels = RecordLevel::all();
        return view('dollies.actions.mailDateForm', compact('dolly','levels'))->with('success', 'Dolly created successfully.');
    }


    public function MailDateChange(int $id, int $value)
    {
        $dolly = Dolly::findOrFail($id);
        $mails = $dolly->mails;

        foreach ($mails as $mail) {
            $mail->update(['date_exact' => $value]);
        }

        return view('dollies.show', ['dolly' => $dolly]);
    }





    public function MailPriority(INT $id){
        $dolly = dolly::findOrFail($id);
        $priorities = MailPriority::all();
        return view('dollies.actions.mailPriorityForm', compact('dolly','priorities'))->with('success', 'Dolly created successfully.');
    }
    public function MailPriorityChange(INT $dolly_id, STRING $value){

    }




    public function MailType(INT $id){
        $dolly = dolly::findOrFail($id);
        $types = MailType::all();
        return view('dollies.actions.mailTypeForm', compact('dolly','types'))->with('success', 'Dolly created successfully.');
    }

    public function MailTypeChange(INT $dolly_id, STRING $value){

    }




    public function MailArchived(INT $id){
        $dolly = dolly::findOrFail($id);
        return view('dollies.actions.mailArchivedForm', compact('dolly_id'))->with('success', 'Dolly created successfully.');
    }

    public function MailArchivedChange(INT $dolly_id, STRING $value){

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


    public function RecordLevelChange(INT $dolly_id, STRING $value){

    }


    public function RecordStatus(INT $id){
        $dolly = dolly::findOrFail($id);
        $statuses = RecordStatus::all();
        return view('dollies.actions.recordStatusForm', compact('dolly','statuses'))->with('success', 'Dolly created successfully.');
    }



    public function RecordStatusChange(INT $dolly_id, STRING $value){

    }




     public function RecordDate(INT $id){
        $dolly = dolly::findOrFail($id);
        return view('dollies.actions.recordDateForm', compact('dolly'))->with('success', 'Dolly created successfully.');
    }


    public function RecordDateChange(INT $dolly_id, STRING $value){
        echo "Date";
    }


    public function RecordActivity(INT $id){
        $dolly = dolly::findOrFail($id);
        $activities = activity::all();
        return view('dollies.actions.recordActivityForm', compact('dolly','activities'))->with('success', 'Dolly created successfully.');
    }


    public function RecordActivityChange(INT $dolly_id, STRING $value){

    }


    public function RecordContainer(INT $id){
        $dolly = dolly::findOrFail($id);
        $containers = container::all();
        return view('dollies.actions.recordContainerForm', compact('dolly','containers'))->with('success', 'Dolly created successfully.');
    }



    public function RecordContainerChange(INT $dolly_id, STRING $value){

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



     Public function shelfRoom(INT $id){
        $dolly = Dolly::findOrFail($id);
        $rooms = Room::all();
        $rooms->load('floor');
        return view('dollies.actions.shelfRoomForm', compact('dolly','rooms'))->with('success', 'Dolly created successfully.');
     }

     Public function shelfRoomchange(INT $dolly_id, STRING $value){

     }




    Public function ContainerShelf(INT $id){
        $dolly = Dolly::findOrFail($id);
        $shelves = Shelf::all();
        $shelves->load('room');
        return view('dollies.actions.ContainerShelfForm', compact('dolly','shelves'))->with('success', 'Dolly created successfully.');
    }

    Public function ContainerShelfchange(INT $dolly_id, STRING $value){

    }




    Public function RoomFloor(INT $id){
        $dolly = Dolly::findOrFail($id);
        $floors = Floor::all();
        $floors->load('building');
        return view('dollies.actions.RoomFloorForm', compact('floors','dolly'))->with('success', 'Dolly created successfully.');
    }

    Public function RoomFloorChange(INT $dolly_id, STRING $value){

    }



}


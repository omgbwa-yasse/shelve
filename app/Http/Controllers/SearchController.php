<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\Record;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Author;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\Slip;
use App\Models\SlipRecord;

class SearchController extends Controller
{

    public function index(Request $request){
        switch($request['search_type']){
            case 'record' : return $this->record($request);
            case 'mail' : return $this->mail($request);
            case 'transferring' : return $this->transferring($request);
            case 'transferring_record' : return $this->transferringRecord($request);
            default: return $this->default($request) ;
        }
    }

    public function record(Request $request)
   {
       if($request->input('advanced') == true){
            $query = $request->input('query');
            $records = Record::where('name', 'LIKE', "%$query%")
            ->orWhere('code', 'LIKE', "%$query%")
            ->orWhere('date_start', 'LIKE', "%$query%")
            ->orWhere('date_end', 'LIKE', "%$query%")
            ->orWhere('date_exact', 'LIKE', "%$query%")
            ->orWhere('biographical_history', 'LIKE', "%$query%")
            ->orWhere('archival_history', 'LIKE', "%$query%")
            ->orWhere('acquisition_source', 'LIKE', "%$query%")
            ->orWhere('content', 'LIKE', "%$query%")
            ->orWhere('appraisal', 'LIKE', "%$query%")
            ->orWhere('accrual', 'LIKE', "%$query%")
            ->orWhere('arrangement', 'LIKE', "%$query%")
            ->orWhere('access_conditions', 'LIKE', "%$query%")
            ->orWhere('reproduction_conditions', 'LIKE', "%$query%")
            ->orWhere('language_material', 'LIKE', "%$query%")
            ->orWhere('characteristic', 'LIKE', "%$query%")
            ->orWhere('finding_aids', 'LIKE', "%$query%")
            ->orWhere('location_original', 'LIKE', "%$query%")
            ->orWhere('location_copy', 'LIKE', "%$query%")
            ->orWhere('related_unit', 'LIKE', "%$query%")
            ->orWhere('publication_note', 'LIKE', "%$query%")
            ->orWhere('note', 'LIKE', "%$query%")
            ->orWhere('archivist_note', 'LIKE', "%$query%")
            ->orWhere('rule_convention', 'LIKE', "%$query%")
            ->get();

        }else{
            $query = $request->input('query');
            $records = Record::where('name', 'LIKE', "%$query%")
                    ->orWhere('code', 'LIKE', "%$query%")
                    ->orWhere('content', 'LIKE', "%$query%")
                    ->get();
        }

        $statuses = RecordStatus::all();
        $terms = Term::all();
        return view('records.index', compact('records', 'statuses', 'terms'));
    }


    public function mail(Request $request)
    {
        $query = $request->input('query');

        if ($request->input('advanced') == false) {
            $mails = Mail::where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%")
                ->get();
        } else {
            $mails = Mail::where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%")
                ->orWhere('date', 'LIKE', "%$query%")
                ->orWhereHas('authors', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%$query%");
                })
                ->orWhereHas('priority', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%$query%");
                })
                ->orWhereHas('type', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%$query%");
                })
                ->orWhereHas('typology', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%$query%");
                })
                ->orWhereHas('documentType', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%$query%");
                })
                ->get();
        }

        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();

        return view('mails.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));
    }



    public function transferring(Request $request)
    {
        if($request->input('advanced') == false){
            $query = $request->input('query');
            $slips = Slip::where('name', 'LIKE', "%$query%")
            ->orWhere('code', 'LIKE', "%$query%")
            ->orWhere('description', 'LIKE', "%$query%")
            ->get();

        } else{
            $query = $request->input('query');
            $slips = Slip::where('name', 'LIKE', "%$query%")
                    ->orWhere('code', 'LIKE', "%$query%")
                    ->orWhere('description', 'LIKE', "%$query%")
                    ->orWhereHas('officer', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%$query%");
                    })
                    ->orWhereHas('user', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%$query%");
                    })
                    ->get();
        }

        return view('transferrings.slips.index', compact('slips'));
    }





    public function transferringRecord(Request $request)
    {
        if($request->input('advanced') == true){
            $query = $request->input('query');
            $records = SlipRecord::where('name', 'LIKE', "%$query%")
                        ->orWhere('code', 'LIKE', "%$query%")
                        ->orWhere('date_start', 'LIKE', "%$query%")
                        ->orWhere('date_end', 'LIKE', "%$query%")
                        ->orWhere('date_exact', 'LIKE', "%$query%")
                        ->orWhere('content', 'LIKE', "%$query%")
                        ->orWhereHas('level', function ($q) use ($query) {
                            $q->where('name', 'LIKE', "%$query%");
                        })
                        ->orWhereHas('slip', function ($q) use ($query) {
                            $q->where('name', 'LIKE', "%$query%");
                        })
                        ->orWhereHas('support', function ($q) use ($query) {
                            $q->where('name', 'LIKE', "%$query%");
                        })
                        ->orWhereHas('activity', function ($q) use ($query) {
                            $q->where('name', 'LIKE', "%$query%");
                        })
                        ->orWhereHas('container', function ($q) use ($query) {
                            $q->where('name', 'LIKE', "%$query%");
                        })
                        ->get();

        } else {
                $query = $request->input('query');
                $records = SlipRecord::where('name', 'LIKE', "%$query%")
                            ->orWhere('code', 'LIKE', "%$query%")
                            ->orWhere('content', 'LIKE', "%$query%")
                            ->get();
        }
        $records->load('slip');
        return view('search.transferring.record', compact('records'));
    }




    public function default(Request $request)
    {
        $query = $request->input('query');
        $records = record::where('name', 'LIKE', "%$query%")
                    ->orWhere('code', 'LIKE', "%$query%")
                    ->orWhere('content', 'LIKE', "%$query%")
                    ->latest()->take(4)
                    ->get();

        $mails = Mail::where('name', 'LIKE', "%$query%")
                    ->orWhere('code', 'LIKE', "%$query%")
                    ->orWhere('description', 'LIKE', "%$query%")
                    ->latest()->take(4)
                    ->get();

        $transferrings = Slip::where('name', 'LIKE', "%$query%")
                    ->orWhere('code', 'LIKE', "%$query%")
                    ->orWhere('description', 'LIKE', "%$query%")
                    ->latest()->take(4)
                    ->get();

        $transferringRecords = SlipRecord::where('name', 'LIKE', "%$query%")
                    ->orWhere('code', 'LIKE', "%$query%")
                    ->orWhere('content', 'LIKE', "%$query%")
                    ->latest()->take(4)
                    ->get();

        return view('search.index', compact('records', 'mails', 'transferrings','transferringRecords'));
    }

}

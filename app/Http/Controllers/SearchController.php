<?php
namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\SlipStatus;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\Record;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Author;
use App\Models\CommunicationRecord;
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
            case 'communication' : return $this->communication($request);
            case 'communication_record' : return $this->communicationRecord($request);
            case 'transferring' : return $this->transferring($request);
            case 'transferring_record' : return $this->transferringRecord($request);
            default: return $this->default($request);
        }
    }



    public function record(Request $request)
    {
        $queries = preg_split('/[+\s]+/', $request->input('query'), -1, PREG_SPLIT_NO_EMPTY);

        $records = Record::query();

        foreach ($queries as $query) {
            $records->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('code', 'LIKE', "%{$query}%")
                            ->orWhere('content', 'LIKE', "%{$query}%")
                            ->orWhereHas('authors', function ($q) use ($query) {
                                $q->where('name', 'LIKE', "%$query%");
                            })
                            ->orWhereHas('activity', function ($q) use ($query) {
                                $q->where('name', 'LIKE', "%$query%");
                            })
                            ->orWhereHas('terms', function ($q) use ($query) {
                                $q->where('name', 'LIKE', "%$query%");
                            });
            });
        }

        $records = $records->paginate(15); // Pagination après construction de la requête
        $users = User::all();
        $organisations = Organisation::all();
        $slipStatuses = SlipStatus::all();

        return view('records.index', compact('records', 'users', 'organisations', 'slipStatuses'));
    }


    public function communication(Request $request)
    {
        $query = $request->input('query');
        $communications = Record::where('name', 'LIKE', "%$query%")->paginate(10); // Corrected variable name

        return view('search.communication.slip', compact('communications'));
    }

    public function communicationRecord(Request $request)
    {
        $query = $request->input('query');
        $communicationRecords = CommunicationRecord::where('name', 'LIKE', "%$query%")->paginate(10); // Corrected variable name

        return view('search.communication.record', compact('communicationRecords'));
    }

    public function mail(Request $request)
    {
        $query = $request->input('query');

        if ($request->input('advanced') == false) {
            $mails = Mail::where('name', 'LIKE', "%$query%")->paginate(10);
        } elseif ($categ = $request->input('categ')) {
            switch ($categ) {
                case "dates":
                    $mails = Mail::where('date', 'LIKE', "%{$request->input('date')}%")->paginate(10);
                    break;
                case "typology":
                    $mails = Mail::where('typology_id', $request->input('id'))->paginate(10);
                    break;
                case "author":
                    $mails = Mail::where('author_id', $request->input('id'))->paginate(10);
                    break;
                case "container":
                    $mails = Mail::where('container_id', $request->input('id'))->paginate(10);
                    break;
                default:
                    $mails = Mail::where('code', 'LIKE', "%$query%")
                        ->orWhere('name', 'LIKE', "%$query%")
                        ->orWhere('description', 'LIKE', "%$query%")
                        ->paginate(10);
                    break;
            }
        } else {
            $mails = Mail::where('code', 'LIKE', "%$query%")
                ->orWhere('name', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%")
                ->paginate(10);
        }

        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();

        return view('mails.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));
    }

    public function transferring(Request $request)
    {
        $query = $request->input('query');

        if ($request->input('advanced') == true) {
            $slips = Slip::where('name', 'LIKE', "%$query%")->paginate(10);
        } else {
            $slips = Slip::where('name', 'LIKE', "%$query%")
                ->orWhereHas('officer', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%$query%");
                })
                ->orWhereHas('user', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%$query%");
                })
                ->paginate(10);
        }

        return view('transferrings.slips.index', compact('slips'));
    }

    public function transferringRecord(Request $request)
    {
        $query = $request->input('query');

        if ($request->input('advanced') == true) {
            $records = SlipRecord::where('date_start', 'LIKE', "%$query%")
                ->orWhere('date_end', 'LIKE', "%$query%")
                ->orWhere('date_exact', 'LIKE', "%$query%")
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
                })->paginate(10);
        } else {
            $records = SlipRecord::where('name', 'LIKE', "%$query%")->paginate(10);
        }

        $records->load('slip');

        return view('search.transferring.record', compact('records'));
    }

    public function default(Request $request)
    {
        $query = $request->input('query');
        $records = Record::where('name', 'LIKE', "%$query%")
            ->orWhere('code', 'LIKE', "%$query%")
            ->orWhere('content', 'LIKE', "%$query%")
            ->latest()->take(4)
            ->paginate(10);

        $mails = Mail::where('name', 'LIKE', "%$query%")
            ->orWhere('code', 'LIKE', "%$query%")
            ->orWhere('description', 'LIKE', "%$query%")
            ->latest()->take(4)
            ->paginate(10);

        $transferrings = Slip::where('name', 'LIKE', "%$query%")
            ->orWhere('code', 'LIKE', "%$query%")
            ->orWhere('description', 'LIKE', "%$query%")
            ->latest()->take(4)
            ->paginate(10);

        $transferringRecords = SlipRecord::where('name', 'LIKE', "%$query%")
            ->orWhere('code', 'LIKE', "%$query%")
            ->orWhere('content', 'LIKE', "%$query%")
            ->latest()->take(4)
            ->paginate(10);

        return view('search.index', compact('records', 'mails', 'transferrings', 'transferringRecords'));
    }
}

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
    public function index(Request $request)
    {
        switch ($request->input('search_type')) {
            case 'record':
                return $this->record($request);
            case 'mail':
                return $this->mail($request);
            case 'communication':
                return $this->communication($request);
            case 'communication_record':
                return $this->communicationRecord($request);
            case 'transferring':
                return $this->transferring($request);
            case 'transferring_record':
                return $this->transferringRecord($request);
            default:
                return $this->default($request);
        }
    }

    public function record(Request $request)
    {
        $queries = $this->convertStringToWords($request);

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

        $records = $records->paginate(15);
        $users = User::all();
        $organisations = Organisation::all();
        $slipStatuses = SlipStatus::all();

        return view('records.index', compact('records', 'users', 'organisations', 'slipStatuses'));
    }

    public function communication(Request $request)
    {
        $queries = preg_split('/[+\s]+/', $request->input('query'), -1, PREG_SPLIT_NO_EMPTY);
        $communications = Record::query();

        foreach ($queries as $query) {
            $communications->orWhere('name', 'LIKE', "%$query%");
        }
        $communications = $communications->paginate(10);
        return view('search.communication.slip', compact('communications'));
    }


    public function communicationRecord(Request $request)
    {
        $queries = $this->convertStringToWords($request);

        $communicationRecords = CommunicationRecord::query();

        foreach ($queries as $query) {
            $communicationRecords->orWhere('name', 'LIKE', "%$query%");
        }

        $communicationRecords = $communicationRecords->paginate(10);

        return view('search.communication.record', compact('communicationRecords'));
    }



    public function mail(Request $request)
    {
        $queries = $this->convertStringToWords($request);

        $mails = Mail::query();
        $categ = $request->input('categ');

        switch ($categ) {
            case "dates":
                foreach ($queries as $query) {
                    $mails->orWhere('date', 'LIKE', "%{$query}%");
                }
                break;

            case "typology":
                foreach ($queries as $query) {
                    $mails->orWhere('typology_id', $request->input('id'));
                }
                break;

            case "author":
                foreach ($queries as $query) {
                    $mails->orWhere('author_id', $request->input('id'));
                }
                break;

            case "container":
                foreach ($queries as $query) {
                    $mails->orWhere('container_id', $request->input('id'));
                }
                break;

            default:
                foreach ($queries as $query) {
                    $mails->orWhere('code', 'LIKE', "%$query%")
                        ->orWhere('name', 'LIKE', "%$query%")
                        ->orWhere('description', 'LIKE', "%$query%");
                }
                break;
        }

        $mails = $mails->paginate(10);

        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();

        return view('mails.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));
    }



    public function transferring(Request $request)
    {
        $queries = $this->convertStringToWords($request);

        $slips = Slip::query();

        if ($request->input('advanced')) {
            foreach ($queries as $query) {
                $slips->where('name', 'LIKE', "%$query%");
            }
        } else {
            foreach ($queries as $query) {
                $slips->where('name', 'LIKE', "%$query%")
                    ->orWhereHas('officer', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%$query%");
                    })
                    ->orWhereHas('user', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%$query%");
                    });
            }
        }

        $slips = $slips->paginate(10);

        return view('transferrings.slips.index', compact('slips'));
    }




    public function transferringRecord(Request $request)
    {
        $queries = $this->convertStringToWords($request);

        $records = SlipRecord::query();

        if ($request->input('advanced')) {
            foreach ($queries as $query) {
                $records->where('date_start', 'LIKE', "%$query%")
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
                    ->orWhereHas('containers', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%$query%");
                    });
            }
        } else {
            foreach ($queries as $query) {
                $records->where('name', 'LIKE', "%$query%");
            }
        }

        $records = $records->with('slip')->paginate(10);

        return view('search.transferring.record', compact('records'));
    }


    public function default(Request $request)
    {
        $queries = $this->convertStringToWords($request);

        $records = Record::query();
        foreach ($queries as $query) {
            $records->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('content', 'LIKE', "%$query%");
        }
        $records = $records->latest()->take(4)->paginate(10);

        $mails = Mail::query();
        foreach ($queries as $query) {
            $mails->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%");
        }
        $mails = $mails->latest()->take(4)->paginate(10);

        $transferrings = Slip::query();
        foreach ($queries as $query) {
            $transferrings->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%");
        }
        $transferrings = $transferrings->latest()->take(4)->paginate(10);

        $transferringRecords = SlipRecord::query();
        foreach ($queries as $query) {
            $transferringRecords->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('content', 'LIKE', "%$query%");
        }
        $transferringRecords = $transferringRecords->latest()->take(4)->paginate(10);

        return view('search.index', compact('records', 'mails', 'transferrings', 'transferringRecords'));
    }


    public function convertStringToWords(Request $request)
    {
        $inputQuery = $request->input('query', '');
        if (empty($inputQuery)) {
            return [];
        }
        $queries = preg_split('/[+\s]+/', $inputQuery, -1, PREG_SPLIT_NO_EMPTY);
        return $queries;
    }


}

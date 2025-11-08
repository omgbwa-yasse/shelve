<?php
namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\SlipStatus;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\RecordPhysical;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Author;
use App\Models\CommunicationRecord;
use App\Models\RecordStatus;

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

        // Recherche dans RecordPhysical
        $physicalRecords = RecordPhysical::query();
        foreach ($queries as $q) {
            $physicalRecords->where(function ($queryBuilder) use ($q) {
                $queryBuilder->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('code', 'LIKE', "%{$q}%")
                    ->orWhere('content', 'LIKE', "%{$q}%")
                    ->orWhereHas('authors', function ($qb) use ($q) {
                        $qb->where('name', 'LIKE', "%$q%");
                    })
                    ->orWhereHas('activity', function ($qb) use ($q) {
                        $qb->where('name', 'LIKE', "%$q%");
                    })
                    ->orWhereHas('terms', function ($qb) use ($q) {
                        $qb->where('name', 'LIKE', "%$q%");
                    });
            });
        }
        $physicalRecords = $physicalRecords->with(['status', 'support', 'level', 'activity', 'containers', 'authors']);

        // Recherche dans RecordDigitalFolder
        $folders = RecordDigitalFolder::query();
        foreach ($queries as $q) {
            $folders->where(function ($queryBuilder) use ($q) {
                $queryBuilder->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('code', 'LIKE', "%{$q}%")
                    ->orWhere('description', 'LIKE', "%{$q}%");
            });
        }
        $folders = $folders->with(['type', 'creator', 'organisation']);

        // Recherche dans RecordDigitalDocument
        $documents = RecordDigitalDocument::query();
        foreach ($queries as $q) {
            $documents->where(function ($queryBuilder) use ($q) {
                $queryBuilder->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('code', 'LIKE', "%{$q}%")
                    ->orWhere('description', 'LIKE', "%{$q}%");
            });
        }
        $documents = $documents->with(['type', 'folder', 'creator', 'organisation']);

        // Combiner tous les résultats avec marqueur de type
        $allRecords = collect();

        foreach ($physicalRecords->get() as $record) {
            $record->record_type = 'physical';
            $record->type_label = 'Dossier Physique';
            $allRecords->push($record);
        }

        foreach ($folders->get() as $folder) {
            $folder->record_type = 'folder';
            $folder->type_label = 'Dossier Numérique';
            $allRecords->push($folder);
        }

        foreach ($documents->get() as $document) {
            $document->record_type = 'document';
            $document->type_label = 'Document Numérique';
            $allRecords->push($document);
        }

        // Paginer manuellement
        $perPage = 15;
        $page = $request->input('page', 1);
        $records = new \Illuminate\Pagination\LengthAwarePaginator(
            $allRecords->forPage($page, $perPage),
            $allRecords->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $statuses = RecordStatus::all();
        $terms = [];
        $users = User::all();
        $organisations = Organisation::all();
        $slipStatuses = SlipStatus::all();

        return view('records.index', compact(
            'records',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }

    public function communication(Request $request)
    {
        $queries = preg_split('/[+\s]+/', $request->input('query'), -1, PREG_SPLIT_NO_EMPTY);
        $communications = RecordPhysical::query();

        foreach ($queries as $query) {
            $communications->orWhere('name', 'LIKE', "%$query%");
        }
        $communications = $communications->paginate(10);
        return view('search.communication.slip', compact('communications'));
    }


    public function communicationRecord(Request $request)
    {
        $queries = $this->convertStringToWords($request);

        $communicationRecords = CommunicationRecordPhysical::query();

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
        $typologies = MailTypology::all();
        $authors = Author::all();

        return view('mails.index', compact('mails', 'priorities', 'typologies', 'authors'));
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

        // Records Physical (top 4)
        $records = RecordPhysical::query();
        foreach ($queries as $query) {
            $records->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('content', 'LIKE', "%$query%");
        }
        $records = $records->latest()->take(4)->get()->map(function($r) {
            $r->record_type = 'physical';
            $r->type_label = 'Dossier Physique';
            return $r;
        });

        // Digital Folders (top 4)
        $folders = RecordDigitalFolder::query();
        foreach ($queries as $query) {
            $folders->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%");
        }
        $folders = $folders->latest()->take(4)->get()->map(function($f) {
            $f->record_type = 'folder';
            $f->type_label = 'Dossier Numérique';
            return $f;
        });

        // Digital Documents (top 4)
        $documents = RecordDigitalDocument::query();
        foreach ($queries as $query) {
            $documents->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%");
        }
        $documents = $documents->latest()->take(4)->get()->map(function($d) {
            $d->record_type = 'document';
            $d->type_label = 'Document Numérique';
            return $d;
        });

        // Combiner tous les records
        $allRecords = $records->concat($folders)->concat($documents);

        $mails = Mail::query();
        foreach ($queries as $query) {
            $mails->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%");
        }
        $mails = $mails->latest()->take(4)->get();

        $transferrings = Slip::query();
        foreach ($queries as $query) {
            $transferrings->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "%$query%");
        }
        $transferrings = $transferrings->latest()->take(4)->get();

        $transferringRecords = SlipRecord::query();
        foreach ($queries as $query) {
            $transferringRecords->where('name', 'LIKE', "%$query%")
                ->orWhere('code', 'LIKE', "%$query%")
                ->orWhere('content', 'LIKE', "%$query%");
        }
        $transferringRecords = $transferringRecords->latest()->take(4)->get();

        return view('search.index', compact('allRecords', 'mails', 'transferrings', 'transferringRecords'));
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

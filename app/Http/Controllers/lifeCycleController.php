<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordSupport;
use App\Models\RecordStatus;
use App\Models\Container;
use App\Models\Activity;
use App\Models\SlipStatus;
use App\Models\Accession;
use App\Models\Author;
use App\Models\RecordLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class lifeCycleController extends Controller
{

    public function recordToRetain()
    {
        $title = "actifs";

        $records = Record::with('activity.retentions')
            ->whereHas('activity.retentions', function ($query) {
                $query->whereRaw('DATE_ADD(created_at, INTERVAL duration YEAR) < NOW()');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        return view('records.index', compact('records', 'title',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }


    public function recordToTransfer()
    {
        $title = "à transférer aux archives historiques";
        $records = Record::with('activity')->get();
        $records = Record::whereHas('activity.retentions', function ($query) {
            $query->whereRaw('DATE_ADD(created_at, INTERVAL duration YEAR) > NOW()');
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);
        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();
        return view('records.index', compact('records', 'title',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }



    public function recordToSort()
    {
        $title = "à trier après durée légale";
        $records = Record::with('activity.retentions.sort')
            ->whereHas('activity.retentions', function ($query) {
                $query->whereRaw('DATE_ADD(created_at, INTERVAL duration YEAR) > NOW()')
                    ->whereHas('sort', function ($query) {
                        $query->where('code', 'T');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        return view('records.index', compact('records', 'title',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }


    public function recordToStore()
    {
        $title = "à transférer au dépôt d'archives";
        $records = Record::whereHas('activity.communicability', function ($query) {
                $query->whereRaw('DATE_ADD(records.created_at, INTERVAL communicabilities.duration YEAR) > NOW()');
            })->paginate(10);
        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        return view('records.index', compact('records', 'title',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }

    public function recordToKeep()
    {
        $title = "à conserver après durée légale";

        $records = Record::with('activity.retentions.sort')
            ->whereHas('activity.retentions', function ($query) {
                $query->whereRaw('DATE_ADD(created_at, INTERVAL duration YEAR) > NOW()')
                    ->whereHas('sort', function ($query) {
                        $query->where('code', 'C');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        return view('records.index', compact('records', 'title',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }



    public function recordToEliminate()
    {
        $title = "à éliminer après durée légale";

        $records = Record::with('activity.retentions.sort')
            ->whereHas('activity.retentions', function ($query) {
                $query->whereRaw('DATE_ADD(created_at, INTERVAL duration YEAR) > NOW()')
                    ->whereHas('sort', function ($query) {
                        $query->where('code', 'D');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $slipStatuses = SlipStatus::all();
        $statuses = RecordStatus::all();
        $terms
        $users = User::select('id', 'name')->get();
        $organisations = Organisation::select('id', 'name')->get();

        return view('records.index', compact('records', 'title',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }


}

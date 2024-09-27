<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Container;
use App\Models\RecordLevel;
use App\Models\RecordSupport;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SlipRecordController extends Controller
{


    public function create(Slip $slip)
    {
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        return view('transferrings.records.create', compact('slip','levels', 'supports', 'activities', 'containers', 'users'));
    }




    public function store(Request $request, Slip $slip)
    {
        // Assurez-vous que getDateFormat retourne une valeur valide (un seul caractère)
        $dateFormat = $this->getDateFormat($request->date_start, $request->date_end);
        if (strlen($dateFormat) > 1) {
            return back()->withErrors(['date_format' => 'The date format must not be greater than 1 character.'])->withInput();
        }

        $request->merge(['date_format' => $dateFormat]);
        $request->merge(['creator_id' => auth()->id()]);
        $request->merge(['slip_id' => $slip->id]);

        $request->validate([
            'slip_id' => 'required|exists:slips,id',
            'code' => 'required|string|max:10',
            'name' => 'required|string',
            'date_format' => 'required|string|max:1',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'content' => 'nullable|string',
            'level_id' => 'required|exists:record_levels,id',
            'width' => 'nullable|numeric',
            'width_description' => 'nullable|string|max:100',
            'support_id' => 'required|exists:record_supports,id',
            'activity_id' => 'required|exists:activities,id',
            'container_id' => 'nullable|exists:containers,id',
            'creator_id' => 'required|exists:users,id',
        ]);

        $slipRecordData = [
            'slip_id' => $request->input('slip_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'date_format' => $request->input('date_format'),
            'date_start' => $request->input('date_start'),
            'date_end' => $request->input('date_end'),
            'date_exact' => $request->input('date_exact'),
            'content' => $request->input('content'),
            'level_id' => $request->input('level_id'),
            'width' => $request->input('width'),
            'width_description' => $request->input('width_description'),
            'support_id' => $request->input('support_id'),
            'activity_id' => $request->input('activity_id'),
            'container_id' => $request->input('container_id'),
            'creator_id' => $request->input('creator_id'),
        ];

        $slipRecord = SlipRecord::create($slipRecordData);
        $slip = $slipRecord->slip;
        return view('transferrings.slips.show', compact('slip'));
    }

    private function getDateFormat($dateStart, $dateEnd)
    {
        $start = new \DateTime($dateStart);
        $end = new \DateTime($dateEnd);

        if ($start->format('Y') !== $end->format('Y')) {
            return 'Y';
        } elseif ($start->format('m') !== $end->format('m')) {
            return 'M';
        } elseif ($start->format('d') !== $end->format('d')) {
            return 'D';
        }
        return 'D';
    }





    public function show(INT $id, INT $slipRecordId)
    {
        $slipRecord = SlipRecord::findOrFail($slipRecordId);
        $slip = Slip::findOrFail($id);
        return view('transferrings.records.show', compact('slip', 'slipRecord'));
    }



    public function edit(INT $slipId, INT $id)
    {
        $slipRecord = SlipRecord::findOrFail($id);
        $slip = Slip::findOrFail($slipId);
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        return view('transferrings.records.edit', compact('slip', 'levels','slipRecord', 'supports', 'activities', 'containers', 'users'));
    }




    public function update(Request $request, Slip $slip, SlipRecord $slipRecord)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required',
            'date_format' => 'required|max:1',
            'date_start' => 'nullable|max:10',
            'date_end' => 'nullable|max:10',
            'date_exact' => 'nullable|date',
            'content' => 'nullable',
            'level_id' => 'required',
            'width' => 'nullable|numeric',
            'width_description' => 'nullable|max:100',
            'support_id' => 'required|exists:record_supports,id',
            'activity_id' => 'required|exists:activities,id',
            'container_id' => 'nullable|exists:containers,id',
        ]);

        $slipRecord->update($request->all());
        $slip = $slipRecord->slip;
        return view('transferrings.slips.show', compact('slip', 'slipRecords'));
    }



    public function destroy(int $slip_id, int $slipRecord_id)
    {
        $slipRecord = SlipRecord::where(['slip_id' => $slip_id, 'id' => $slipRecord_id])->firstOrFail();
        $slip = $slipRecord->slip;
        $slipRecord->delete();
        return view('transferrings.slips.show', compact('slip'));
    }





}





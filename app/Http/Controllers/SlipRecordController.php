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


    public function index(Slip $slip)
    {
        $slip->load('records');
        $slipRecords = $slip->records;
        return view('transferrings.records.index', compact('slip', 'slipRecords'));
    }




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
        $request->merge(['date_format' => $this->getDateFormat($request->date_start, $request->date_end)]);
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

        SlipRecord::create([
            'code' => $request->code,
            'name' => $request->name,
            'date_format' => $request->date_format,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'date_exact' => $request->date_exact,
            'content' => $request->input('content'),
            'level_id' => $request->level_id,
            'width' => $request->width,
            'width_description' => $request->width_description,
            'support_id' => $request->support_id,
            'activity_id' => $request->activity_id,
            'container_id' => $request->container_id,
            'slip_id' => $slip->id,
            'creator_id' => auth()->id(),
        ]);

        return redirect()->route('slips.records.index', $slip->id)
            ->with('success', 'Slip record created successfully.');
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

        return redirect()->route('slips.records.index', $slip->id)
            ->with('success', 'Slip record updated successfully.');
    }



    public function destroy(Slip $slip, SlipRecord $slipRecord)
    {
        $slipRecord->delete();

        return redirect()->route('slips.records.index', $slip->id)
            ->with('success', 'Slip record deleted successfully.');
    }



   public function getDateFormat($date1, $date2) {
        $patternY = '/^\d{4}$/';
        $patternYM = '/^\d{4}[-\/\.]\d{2}$/';
        $patternYMD = '/^\d{4}[-\/\.]\d{2}[-\/\.]\d{2}$/';

        $format1 = (preg_match($patternY, $date1)) ? 'Y' : ((preg_match($patternYM, $date1)) ? 'Y-M' : 'Y-M-D');
        $format2 = (preg_match($patternY, $date2)) ? 'Y' : ((preg_match($patternYM, $date2)) ? 'Y-M' : 'Y-M-D');

        if ($format1 == $format2) {
            return $format1;
        }
    }

}





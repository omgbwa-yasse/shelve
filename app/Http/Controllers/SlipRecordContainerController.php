<?php

namespace App\Http\Controllers;
use App\Models\SlipRecord;
use App\Models\SlipRecordContainer;
use Illuminate\Http\Request;

class SlipRecordContainerController extends Controller
{

        public function index()
        {
            $containers = SlipRecordContainer::all();
            return view('transferrings.slips.records.containers.index', compact('containers'));
        }


        public function store(Request $request, SlipRecord $slipRecord)
        {
            $request->validate([
                'slip_record_id' => 'required|exists:slips,id',
                'container_id' => 'required|exists:containers,id',
                'creator_id' => 'required|exists:users,id',
                'description' => 'required|string|max:200',
            ]);

            SlipRecordContainer::create($request->all());

            return redirect()->route('slips.records.show', $slipRecord)
                             ->with('success', 'Slip record container created successfully.');
        }



        public function edit(SlipRecord $slipRecord, SlipRecordContainer $slipRecordContainer)
        {
            return view('transferrings.slips.records.show', compact('slipRecord', 'slipRecordContainer'));
        }


        public function update(Request $request, SlipRecordContainer $slipRecordContainer)
        {
            $request->validate([
                'slip_record_id' => 'required|exists:slips,id',
                'container_id' => 'required|exists:containers,id',
                'creator_id' => 'required|exists:users,id',
                'description' => 'required|string|max:200',
            ]);

            $slipRecordContainer->update($request->all());

            return redirect()->route('slips.records.container.index')
                             ->with('success', 'Slip record container updated successfully.');
        }


        public function destroy(SlipRecord $slipRecord, SlipRecordContainer $slipRecordContainer)
        {
            $slipRecordContainer->delete();
            return redirect()->route('slips.records.show', $slipRecord )
                             ->with('success', 'Slip record container deleted successfully.');
        }


}

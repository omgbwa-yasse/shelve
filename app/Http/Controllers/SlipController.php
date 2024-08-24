<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\Slip;
use App\Models\SlipStatus;
use App\Models\User;


class SlipController extends Controller
{

    public function index()
    {
        $slips = Slip::all();
        return view('transferrings.slips.index', compact('slips'));
    }



    public function create()
    {
        $organisations = Organisation::all();
        $users = User::all();
        $slipStatuses = SlipStatus::all();
        return view('transferrings.slips.create', compact('organisations', 'users', 'slipStatuses'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'officer_organisation_id' => 'required|exists:organisations,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
            'slip_status_id' => 'required|exists:slip_statuses,id',
            'is_received' => 'nullable|boolean',
            'received_date' => 'nullable|date',
            'is_approved' => 'nullable|boolean',
            'approved_date' => 'nullable|date',
        ]);

        $request->merge(['officer_id' => auth()->user()->id]);

        Slip::create($request->all());

        return redirect()->route('slips.index')
            ->with('success', 'Slip created successfully.');
    }






    public function show(Slip $slip)
    {
        return view('transferrings.slips.show', compact('slip'));
    }



    public function edit(Slip $slip)
    {
        $organisations = Organisation::all();
        $users = User::all();
        $slipStatuses = SlipStatus::all();
        return view('transferrings.slips.edit', compact('slip', 'organisations', 'users', 'slipStatuses'));
    }



    public function update(Request $request, Slip $slip)
    {
        $request->validate([
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'officer_organisation_id' => 'required|exists:organisations,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
            'slip_status_id' => 'required|exists:slip_statuses,id',
            'is_received' => 'nullable|boolean',
            'received_date' => 'nullable|date',
            'is_approved' => 'nullable|boolean',
            'approved_date' => 'nullable|date',
        ]);

        $request->merge(['officer_id' => auth()->user()->id]);

        $slip->update($request->all());

        return redirect()->route('slips.index')
            ->with('success', 'Slip updated successfully.');
    }




    public function destroy(Slip $slip)
    {
        $slip->delete();

        return redirect()->route('slip.index')
            ->with('success', 'Slip deleted successfully.');
    }


    public function sort(Request $request)
    {
        $type = $request->input('categ');
        $slips = [];

        switch ($type) {
            case 'project':
                $slips = Slip::where('is_received', '=', false)
                            ->where('is_approved', '=', false)
                            ->get();
                break;

            case 'received':
                $slips = Slip::where('is_received', '=', true)
                            ->whereNull('is_approved')
                            ->get();
                break;

            case 'approved':
                $slips = Slip::where('is_approved', '=', true)
                            ->get();
                break;
        }

        $slips->load('officer', 'officerOrganisation', 'userOrganisation', 'user','slipStatus','records');
        return view('transferrings.slips.index', compact('slips'));
    }




}



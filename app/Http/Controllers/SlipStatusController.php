<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\SlipStatus;


class SlipStatusController extends Controller
{


    public function index()
    {
        $statuses = SlipStatus::all();
        return view('transferrings.statuses.index', compact('statuses'));
    }



    public function create()
    {
        return view('transferrings.statuses.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50|unique:slip_statuses,name',
            'description' => 'nullable',
        ]);

        SlipStatus::create($request->all());

        return redirect()->route('transferring-status.index')
            ->with('success', 'Transferring status created successfully.');
    }



    public function show(SlipStatus $transferringStatus)
    {
        return view('transferrings.statuses.show', compact('transferringStatus'));
    }



    public function edit(SlipStatus $transferringStatus)
    {
        return view('transferrings.statuses.edit', compact('transferringStatus'));
    }



    public function update(Request $request, SlipStatus $transferringStatus)
    {
        $request->validate([
            'name' => 'required|max:50|unique:slip_statuses,name',
            'description' => 'nullable',
        ]);

        $transferringStatus->update($request->all());

        return redirect()->route('transferring-status.index')
            ->with('success', 'Transferring status updated successfully.');
    }


    public function destroy(SlipStatus $transferringStatus)
    {
        $transferringStatus->load('transferrings');

        if ($transferringStatus->transferrings()->exists()) {
            return redirect()->route('transferrings.statuses.index')
                ->with('error', 'Cannot delete transferring status because it is associated with one or more transferrings.');
        }

        $transferringStatus->delete();

        return redirect()->route('transferrings.statuses.index')
            ->with('success', 'Transferring status deleted successfully.');
    }




}



<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Communicability;


class CommunicabilityController extends Controller
{

    public function index()
    {
        $communicabilities = Communicability::all();

        return view('communicabilities.index', compact('communicabilities'));
    }


    public function create()
    {
        return view('communicabilities.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:communicabilities|max:10',
            'name' => 'required|max:100',
            'duration' => 'required|integer',
            'decription' => 'nullable',
        ]);

        Communicability::create($request->all());

        return redirect()->route('communicabilities.index')
                        ->with('success', 'Communicability created successfully.');
    }


    public function show(Communicability $communicability)
    {
        return view('communicabilities.show', compact('communicability'));
    }


    public function edit(Communicability $communicability)
    {
        return view('communicabilities.edit', compact('communicability'));
    }


    public function update(Request $request, Communicability $communicability)
    {
        $request->validate([
            'code' => 'required|unique:communicabilities,code,'.$communicability->id.'|max:10',
            'name' => 'required|max:100',
            'duration' => 'required|integer',
            'decription' => 'nullable',
        ]);

        $communicability->update($request->all());

        return redirect()->route('communicabilities.index')
                        ->with('success', 'Communicability updated successfully.');
    }


    public function destroy(Communicability $communicability)
    {
        $communicability->delete();

        return redirect()->route('communicabilities.index')
                        ->with('success', 'Communicability deleted successfully.');
    }
}



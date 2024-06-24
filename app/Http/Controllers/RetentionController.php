<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Retention;
use App\models\Sort;


class RetentionController extends Controller
{
    public function index()
    {
        $retentions = Retention::all();
        return view('retentions.index', compact('retentions'));
    }

    public function create()
    {
        $sorts = Sort::all();
        return view('retentions.create', compact('sorts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'duration' => 'required|integer',
            'sort_id' => 'required|exists:sorts,id',
        ]);

        Retention::create($request->all());

        return redirect()->route('retentions.index')
            ->with('success', 'Retention created successfully.');
    }

    public function show(Retention $retention)
    {
        return view('retentions.show', compact('retention'));
    }

    public function edit(Retention $retention)
    {
        $sorts = Sort::all();
        return view('retentions.edit', compact('retention', 'sorts'));
    }

    public function update(Request $request, Retention $retention)
    {
        $request->validate([
            'code' => 'required',
            'duration' => 'required|integer',
            'sort_id' => 'required|exists:sorts,id',
        ]);

        $retention->update($request->all());

        return redirect()->route('retentions.index')
            ->with('success', 'Retention updated successfully.');
    }

    public function destroy(Retention $retention)
    {
        $retention->delete();

        return redirect()->route('retentions.index')
            ->with('success', 'Retention deleted successfully.');
    }
}



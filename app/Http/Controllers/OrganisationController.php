<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{

    public function index()
    {
        $organisations = Organisation::all();

        return view('organisations.index', compact('organisations'));
    }


    public function create()
    {
        $organisations = Organisation::all();

        return view('organisations.create', compact('organisations'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:organisations|max:10',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:organisations,id',
        ]);

        Organisation::create($request->all());

        return redirect()->route('organisations.index')
                        ->with('success', 'Organisation created successfully.');
    }




    public function show(Organisation $organisation)
    {
        return view('organisations.show', compact('organisation'));
    }




    public function edit(Organisation $organisation)
    {
        $organisations = Organisation::where('id', '<>', $organisation->id)->get();

        return view('organisations.edit', compact('organisation', 'organisations'));
    }



    public function update(Request $request, Organisation $organisation)
    {
        $request->validate([
            'code' => 'required|unique:organisations,code,'.$organisation->id.'|max:10',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:organisations,id',
        ]);

        $organisation->update($request->all());

        return redirect()->route('organisations.index')
                        ->with('success', 'Organisation updated successfully.');
    }



    public function destroy(Organisation $organisation)
    {
        $organisation->delete();

        return redirect()->route('organisations.index')
                        ->with('success', 'Organisation deleted successfully.');
    }
}

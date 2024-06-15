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
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:organisations,id',
        ]);

        Organisation::create($request->all());

        return redirect()->route('organisations.index')->with('success', 'Organisation created successfully.');
    }

    public function edit(Organisation $organisation)
    {
        $organisations = Organisation::all();
        return view('organisations.edit', compact('organisation', 'organisations'));
    }

    public function update(Request $request, Organisation $organisation)
    {
        $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:organisations,id',
        ]);

        $organisation->update($request->all());

        return redirect()->route('organisations.index')->with('success', 'Organisation updated successfully.');
    }

    public function destroy(Organisation $organisation)
    {
        $organisation->delete();

        return redirect()->route('organisations.index')->with('success', 'Organisation deleted successfully.');
    }

}

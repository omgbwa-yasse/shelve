<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\RecordSupport;

    class RecordSupportController extends Controller
    {


        public function index()
        {
            $recordSupports = RecordSupport::all();
            return view('records.supports.index', compact('recordSupports'));
        }



        public function create()
        {
            return view('records.supports.create');
        }



        public function store(Request $request)
        {
            $request->validate([
                'name' => 'required|unique:record_supports|max:100',
                'description' => 'nullable',
            ]);

            RecordSupport::create($request->all());

            return redirect()->route('record-supports.index')
                ->with('success', 'Support enregistré avec succès.');
        }



        public function show($id)
        {
            $recordSupport = RecordSupport::findOrFail($id);
            return view('records.supports.show', compact('recordSupport'));
        }



        public function edit($id)
        {
            $recordSupport = RecordSupport::findOrFail($id);
            return view('records.supports.edit', compact('recordSupport'));
        }



        public function update(Request $request, $id)
        {
            $request->validate([
                'name' => 'required|unique:record_supports|max:100',
                'description' => 'nullable',
            ]);

            $recordSupport = RecordSupport::findOrFail($id);
            $recordSupport->update($request->all());

            return redirect()->route('record-supports.index')
                ->with('success', 'Support mis à jour avec succès.');
        }



        public function destroy($id)
        {
            $recordSupport = RecordSupport::findOrFail($id);
            $recordSupport->delete();

            return redirect()->route('record-supports.index')
                ->with('success', 'Support supprimé avec succès.');
        }

}

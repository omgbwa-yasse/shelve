<?php

namespace App\Http\Controllers\Museum;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifactConditionReport;
use App\Models\RecordArtifact;
use Illuminate\Http\Request;

class ConservationController extends Controller
{
    /**
     * Display a listing of conservation records.
     */
    public function index(Request $request)
    {
        $reports = RecordArtifactConditionReport::with('artifact')
            ->orderBy('report_date', 'desc')
            ->paginate(20);

        return view('museum.conservation.index', compact('reports'));
    }

    /**
     * Show the form for creating a new conservation record.
     */
    public function create()
    {
        $artifacts = RecordArtifact::orderBy('code')->get();
        return view('museum.conservation.create', compact('artifacts'));
    }

    /**
     * Store a newly created conservation record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'artifact_id' => 'required|exists:record_artifacts,id',
            'report_date' => 'required|date',
            'condition' => 'required|string',
            'notes' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        RecordArtifactConditionReport::create($validated);

        return redirect()->route('museum.conservation.index')
            ->with('success', 'Rapport de conservation créé avec succès.');
    }

    /**
     * Display the specified conservation record.
     */
    public function show($id)
    {
        $report = RecordArtifactConditionReport::with('artifact')->findOrFail($id);
        return view('museum.conservation.show', compact('report'));
    }
}

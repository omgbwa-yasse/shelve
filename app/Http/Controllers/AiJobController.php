<?php

namespace App\Http\Controllers;

use App\Models\AiJob;
use Illuminate\Http\Request;

class AiJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jobs = AiJob::with(['aiModel'])->paginate(15);
        return view('ai.job.index', compact('jobs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.job.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_type' => 'required|string|max:255',
            'ai_model_id' => 'required|exists:ai_models,id',
            'status' => 'required|string|max:50',
            'parameters' => 'nullable|json',
            'input' => 'required|string',
            'result' => 'nullable|json',
            'error' => 'nullable|string',
        ]);

        AiJob::create($validated);

        return redirect()->route('ai.job.index')->with('success', 'AI Job created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiJob  $aiJob
     * @return \Illuminate\Http\Response
     */
    public function show(AiJob $aiJob)
    {
        return view('ai.job.show', compact('aiJob'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiJob  $aiJob
     * @return \Illuminate\Http\Response
     */
    public function edit(AiJob $aiJob)
    {
        return view('ai.job.edit', compact('aiJob'));
    }




     public function update(Request $request, AiJob $aiJob)
    {
        $validated = $request->validate([
            'job_type' => 'required|string|max:255',
            'ai_model_id' => 'required|exists:ai_models,id',
            'status' => 'required|string|max:50',
            'parameters' => 'nullable|json',
            'input' => 'required|string',
            'result' => 'nullable|json',
            'error' => 'nullable|string',
        ]);

        $aiJob->update($validated);

        return redirect()->route('ai.job.index')->with('success', 'AI Job updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiJob  $aiJob
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiJob $aiJob)
    {
        $aiJob->delete();

        return redirect()->route('ai.job.index')->with('success', 'AI Job deleted successfully');
    }
}

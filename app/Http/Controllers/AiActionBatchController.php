<?php

namespace App\Http\Controllers;

use App\Models\AiActionBatch;
use Illuminate\Http\Request;

class AiActionBatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $batches = AiActionBatch::with(['user', 'actions'])->paginate(15);
        return view('ai.action-batches.index', compact('batches'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.action-batches.create');
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string',
        ]);

        AiActionBatch::create($validated);

        return redirect()->route('ai.action-batches.index')->with('success', 'AI Action Batch created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiActionBatch  $aiActionBatch
     * @return \Illuminate\Http\Response
     */
    public function show(AiActionBatch $aiActionBatch)
    {
        return view('ai.action-batches.show', compact('aiActionBatch'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiActionBatch  $aiActionBatch
     * @return \Illuminate\Http\Response
     */
    public function edit(AiActionBatch $aiActionBatch)
    {
        return view('ai.action-batches.edit', compact('aiActionBatch'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiActionBatch  $aiActionBatch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiActionBatch $aiActionBatch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string',
        ]);

        $aiActionBatch->update($validated);

        return redirect()->route('ai.action-batches.index')->with('success', 'AI Action Batch updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiActionBatch  $aiActionBatch
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiActionBatch $aiActionBatch)
    {
        $aiActionBatch->delete();

        return redirect()->route('ai.action-batches.index')->with('success', 'AI Action Batch deleted successfully');
    }
}

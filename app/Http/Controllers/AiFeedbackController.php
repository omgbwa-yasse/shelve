<?php

namespace App\Http\Controllers;

use App\Models\AiFeedback;
use Illuminate\Http\Request;

class AiFeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $feedbacks = AiFeedback::with(['user', 'interaction'])->paginate(15);
        return view('ai.feedback.index', compact('feedbacks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.feedback.create');
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
            'user_id' => 'required|exists:users,id',
            'ai_interaction_id' => 'required|exists:ai_interactions,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'was_helpful' => 'boolean',
        ]);

        AiFeedback::create($validated);

        return redirect()->route('ai.feedback.index')->with('success', 'AI Feedback created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiFeedback  $aiFeedback
     * @return \Illuminate\Http\Response
     */
    public function show(AiFeedback $aiFeedback)
    {
        return view('ai.feedback.show', compact('aiFeedback'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiFeedback  $aiFeedback
     * @return \Illuminate\Http\Response
     */
    public function edit(AiFeedback $aiFeedback)
    {
        return view('ai.feedback.edit', compact('aiFeedback'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiFeedback  $aiFeedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiFeedback $aiFeedback)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ai_interaction_id' => 'required|exists:ai_interactions,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'was_helpful' => 'boolean',
        ]);

        $aiFeedback->update($validated);

        return redirect()->route('ai.feedback.index')->with('success', 'AI Feedback updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiFeedback  $aiFeedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiFeedback $aiFeedback)
    {
        $aiFeedback->delete();

        return redirect()->route('ai.feedback.index')->with('success', 'AI Feedback deleted successfully');
    }
}

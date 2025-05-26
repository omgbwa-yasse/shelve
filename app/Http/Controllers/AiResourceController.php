<?php

namespace App\Http\Controllers;

use App\Models\AiResource;
use Illuminate\Http\Request;

class AiResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $resources = AiResource::with(['chat'])->paginate(15);
        return view('ai.resources.index', compact('resources'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.resources.create');
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
            'ai_chat_id' => 'required|exists:ai_chats,id',
            'resource_type' => 'required|string',
            'resource_id' => 'required|integer',
            'content_used' => 'nullable|json',
        ]);

        AiResource::create($validated);

        return redirect()->route('ai.resources.index')->with('success', 'AI Resource created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiResource  $aiResource
     * @return \Illuminate\Http\Response
     */
    public function show(AiResource $aiResource)
    {
        return view('ai.resources.show', compact('aiResource'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiResource  $aiResource
     * @return \Illuminate\Http\Response
     */
    public function edit(AiResource $aiResource)
    {
        return view('ai.resources.edit', compact('aiResource'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiResource  $aiResource
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiResource $aiResource)
    {
        $validated = $request->validate([
            'ai_chat_id' => 'required|exists:ai_chats,id',
            'resource_type' => 'required|string',
            'resource_id' => 'required|integer',
            'content_used' => 'nullable|json',
        ]);

        $aiResource->update($validated);

        return redirect()->route('ai.resources.index')->with('success', 'AI Resource updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiResource  $aiResource
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiResource $aiResource)
    {
        $aiResource->delete();

        return redirect()->route('ai.resources.index')->with('success', 'AI Resource deleted successfully');
    }
}

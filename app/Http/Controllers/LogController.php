<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logs = Log::with('user')->orderBy('created_at', 'desc')->get();
        return view('logs.index', compact('logs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
        ]);

        Log::create($validatedData);

        return response()->json(['message' => 'Log enregistré avec succès'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('logs.show', compact('log'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Log $log)
    {
        $validatedData = $request->validate([
            'action' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
        ]);

        $log->update($validatedData);

        return response()->json(['message' => 'Log mis à jour avec succès'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Log $log)
    {
        $log->delete();
        return response()->json(['message' => 'Log supprimé avec succès'], 200);
    }
}

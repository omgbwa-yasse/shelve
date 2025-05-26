<?php

namespace App\Http\Controllers;

use App\Models\AiJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Queue;
use App\Services\OllamaService;


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
        return view('ai.jobs.index', compact('jobs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.jobs.create');
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

        return redirect()->route('ai.jobs.index')->with('success', 'AI Job created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiJob  $aiJob
     * @return \Illuminate\Http\Response
     */
    public function show(AiJob $aiJob)
    {
        return view('ai.jobs.show', compact('aiJob'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiJob  $aiJob
     * @return \Illuminate\Http\Response
     */
    public function edit(AiJob $aiJob)
    {
        return view('ai.jobs.edit', compact('aiJob'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiJob  $aiJob
     * @return \Illuminate\Http\Response
     */
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

        return redirect()->route('ai.jobs.index')->with('success', 'AI Job updated successfully');
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

        return redirect()->route('ai.jobs.index')->with('success', 'AI Job deleted successfully');
    }


    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Créer un job de traitement par lot
     */
    public function createBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_type' => 'required|string',
            'ai_model_id' => 'required|exists:ai_models,id',
            'inputs' => 'required|array',
            'parameters' => 'nullable|array'
        ]);

        try {
            $job = $this->ollamaService->createBatchJob(
                $validated['job_type'],
                $validated['ai_model_id'],
                $validated['inputs'],
                $validated['parameters'] ?? []
            );

            // Traitement en arrière-plan
            Queue::push(function () use ($job) {
                $this->ollamaService->processBatchJob($job);
            });

            return response()->json([
                'success' => true,
                'job_id' => $job->id,
                'status' => 'queued'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le statut d'un job
     */
    public function getJobStatus(AiJob $job): JsonResponse
    {
        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
            'result' => $job->result ? json_decode($job->result, true) : null,
            'error' => $job->error
        ]);
    }


}

<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\PublicTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpacTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = PublicTemplate::orderBy('name')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Templates retrieved successfully',
            'data' => $templates
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('opac.templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:email,notification,report',
            'is_active' => 'boolean',
        ]);

        $template = PublicTemplate::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Template created successfully',
            'data' => $template
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicTemplate $template)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Template details retrieved successfully',
            'data' => $template
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicTemplate $template)
    {
        return view('opac.templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'type' => 'sometimes|in:email,notification,report',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Template updated successfully',
            'data' => $template
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicTemplate $template)
    {
        $template->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Template deleted successfully'
        ], 200);
    }

    /**
     * Preview the template with sample data.
     */
    public function preview(PublicTemplate $template)
    {
        $sampleData = $this->getSampleData($template->variables ?? []);
        $preview = $this->renderTemplate($template->content, $sampleData);

        return view('opac.templates.preview', compact('template', 'preview', 'sampleData'));
    }

    /**
     * Get sample data for template variables.
     */
    private function getSampleData(array $variables)
    {
        $sampleData = [];
        foreach ($variables as $variable) {
            $sampleData[$variable] = $this->getSampleValue($variable);
        }
        return $sampleData;
    }

    /**
     * Get a sample value for a variable.
     */
    private function getSampleValue(string $variable)
    {
        $samples = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'title' => 'Sample Title',
            'content' => 'Sample content goes here...',
            'url' => 'https://example.com',
        ];

        return $samples[$variable] ?? 'Sample ' . $variable;
    }

    /**
     * Render the template with given data.
     */
    private function renderTemplate(string $content, array $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
}

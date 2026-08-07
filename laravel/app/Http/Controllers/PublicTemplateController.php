<?php

namespace App\Http\Controllers;

use App\Models\PublicTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = PublicTemplate::orderBy('name')->paginate(10);
        return view('public.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('public.templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:page,email,notification',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['author_id'] = auth()->id();
        $template = PublicTemplate::create($validated);

        return redirect()->route('public.templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicTemplate $template)
    {
        return view('public.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicTemplate $template)
    {
        return view('public.templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:page,email,notification',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $template->update($validated);

        return redirect()->route('public.templates.show', $template)
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicTemplate $template)
    {
        $template->delete();

        return redirect()->route('public.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Preview the template with sample data.
     */
    public function preview(PublicTemplate $template)
    {
        $sampleData = $this->getSampleData($template->variables ?? []);
        $preview = $this->renderTemplate($template->content, $sampleData);

        return view('public.templates.preview', compact('template', 'preview', 'sampleData'));
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

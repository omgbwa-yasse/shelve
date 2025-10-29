<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * OPAC Template Controller - Manages OPAC template display and configuration
 */
class TemplateController extends Controller
{
    /**
     * Display the available templates for OPAC
     */
    public function index()
    {
        $templates = Cache::remember('opac_templates', 3600, function () {
            return PublicTemplate::where('type', 'opac')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        });

        return view('opac.templates.index', compact('templates'));
    }

    /**
     * Display a specific template
     */
    public function show($id)
    {
        $template = PublicTemplate::where('type', 'opac')
            ->where('status', 'active')
            ->findOrFail($id);

        return view('opac.templates.show', compact('template'));
    }

    /**
     * Preview a template with sample data
     */
    public function preview($id)
    {
        $template = PublicTemplate::where('type', 'opac')
            ->where('status', 'active')
            ->findOrFail($id);

        // Sample data for preview
        $sampleData = [
            'library_name' => config('app.name', 'My Library'),
            'current_date' => now()->format('F j, Y'),
            'user_name' => auth('public')->user()->name ?? 'Guest User',
            'search_query' => 'Sample Search Query',
            'total_records' => '1,234',
            'record_title' => 'Sample Document Title',
            'record_author' => 'Sample Author Name',
        ];

        $renderedContent = $this->renderTemplate($template->content, $sampleData);

        return view('opac.templates.preview', compact('template', 'renderedContent'));
    }

    /**
     * Apply template customization (for authenticated users)
     */
    public function customize(Request $request, $id)
    {
        if (!auth('public')->check()) {
            return redirect()->route('opac.login')->with('error', __('Please login to customize templates'));
        }

        $template = PublicTemplate::where('type', 'opac')
            ->where('status', 'active')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'font_size' => 'nullable|in:small,medium,large',
            'layout_style' => 'nullable|in:compact,standard,expanded',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Store user customizations in session or user preferences
        $customizations = [
            'template_id' => $id,
            'primary_color' => $request->input('primary_color'),
            'secondary_color' => $request->input('secondary_color'),
            'font_size' => $request->input('font_size', 'medium'),
            'layout_style' => $request->input('layout_style', 'standard'),
        ];

        session(['opac_template_customizations' => $customizations]);

        return back()->with('success', __('Template customization saved successfully'));
    }

    /**
     * Get current template for OPAC (used by layout)
     */
    public function getCurrentTemplate()
    {
        // Check if user has selected a template
        $customizations = session('opac_template_customizations');

        if ($customizations && isset($customizations['template_id'])) {
            $template = PublicTemplate::where('type', 'opac')
                ->where('status', 'active')
                ->find($customizations['template_id']);

            if ($template) {
                return [
                    'template' => $template,
                    'customizations' => $customizations
                ];
            }
        }

        // Return default template
        $defaultTemplate = Cache::remember('default_opac_template', 3600, function () {
            return PublicTemplate::where('type', 'opac')
                ->where('status', 'active')
                ->where('name', 'Default')
                ->first() ?? PublicTemplate::where('type', 'opac')
                    ->where('status', 'active')
                    ->first();
        });

        return [
            'template' => $defaultTemplate,
            'customizations' => [
                'primary_color' => '#2c3e50',
                'secondary_color' => '#3498db',
                'font_size' => 'medium',
                'layout_style' => 'standard'
            ]
        ];
    }

    /**
     * Render template content with variables
     */
    private function renderTemplate($content, $variables = [])
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }
}

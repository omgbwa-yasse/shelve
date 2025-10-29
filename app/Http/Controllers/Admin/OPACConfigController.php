<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OPACConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class OPACConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display OPAC configuration dashboard
     */
    public function index()
    {
        $config = OPACConfig::first();

        if (!$config) {
            $config = OPACConfig::create([
                'site_name' => 'Archive OPAC',
                'site_description' => 'Online Public Access Catalog',
                'is_enabled' => true,
                'allow_downloads' => true,
                'items_per_page' => 20,
                'visible_record_fields' => ['title', 'description', 'date_creation', 'authors'],
                'visible_activity_fields' => ['name', 'description', 'date_debut', 'date_fin'],
                'searchable_fields' => ['title', 'description', 'content'],
                'allowed_file_types' => ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'],
                'contact_email' => config('mail.from.address'),
                'custom_css' => '',
                'custom_js' => ''
            ]);
        }

        return view('public.admin.opac.index', compact('config'));
    }

    /**
     * Update OPAC configuration
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'is_enabled' => 'boolean',
            'allow_downloads' => 'boolean',
            'require_login_for_downloads' => 'boolean',
            'items_per_page' => 'required|integer|min:5|max:100',
            'contact_email' => 'nullable|email',
            'footer_text' => 'nullable|string|max:1000',
            'custom_css' => 'nullable|string|max:50000',
            'custom_js' => 'nullable|string|max:50000',
            'visible_record_fields' => 'array',
            'visible_activity_fields' => 'array',
            'searchable_fields' => 'array',
            'allowed_file_types' => 'array',
            'logo' => 'nullable|image|max:2048'
        ]);

        $config = OPACConfig::first();

        if (!$config) {
            $config = new OPACConfig();
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($config->logo_path && Storage::exists('public/' . $config->logo_path)) {
                Storage::delete('public/' . $config->logo_path);
            }

            $logoPath = $request->file('logo')->store('opac/logos', 'public');
            $config->logo_path = $logoPath;
        }

        // Update configuration
        $config->fill([
            'site_name' => $request->site_name,
            'site_description' => $request->site_description,
            'is_enabled' => $request->boolean('is_enabled'),
            'allow_downloads' => $request->boolean('allow_downloads'),
            'require_login_for_downloads' => $request->boolean('require_login_for_downloads'),
            'items_per_page' => $request->items_per_page,
            'contact_email' => $request->contact_email,
            'footer_text' => $request->footer_text,
            'custom_css' => $request->custom_css,
            'custom_js' => $request->custom_js,
            'visible_record_fields' => $request->visible_record_fields ?? [],
            'visible_activity_fields' => $request->visible_activity_fields ?? [],
            'searchable_fields' => $request->searchable_fields ?? [],
            'allowed_file_types' => $request->allowed_file_types ?? []
        ]);

        $config->save();

        // Clear cache
        Cache::forget('opac_config');

        return redirect()->route('admin.opac.index')
            ->with('success', __('OPAC configuration updated successfully.'));
    }

    /**
     * Preview OPAC with current configuration
     */
    public function preview()
    {
        return redirect()->route('opac.index')
            ->with('preview_mode', true);
    }

    /**
     * Reset OPAC configuration to defaults
     */
    public function reset()
    {
        $config = OPACConfig::first();

        if ($config) {
            // Delete logo if exists
            if ($config->logo_path && Storage::exists('public/' . $config->logo_path)) {
                Storage::delete('public/' . $config->logo_path);
            }

            $config->delete();
        }

        // Clear cache
        Cache::forget('opac_config');

        return redirect()->route('admin.opac.index')
            ->with('success', __('OPAC configuration reset to defaults.'));
    }

    /**
     * Get available fields for configuration
     */
    private function getAvailableFields()
    {
        return [
            'record_fields' => [
                'id' => __('ID'),
                'code' => __('Code'),
                'title' => __('Title'),
                'description' => __('Description'),
                'date_creation' => __('Creation Date'),
                'date_debut' => __('Start Date'),
                'date_fin' => __('End Date'),
                'authors' => __('Authors'),
                'content' => __('Content'),
                'niveau_description' => __('Description Level'),
                'cote' => __('Reference Code'),
                'producteur' => __('Producer'),
                'service_versant' => __('Transferring Service'),
                'modalite_entree' => __('Entry Method'),
                'langue' => __('Language'),
                'support' => __('Support'),
                'format' => __('Format'),
                'volumetrie' => __('Volume'),
                'statut' => __('Status')
            ],
            'activity_fields' => [
                'id' => __('ID'),
                'code' => __('Code'),
                'name' => __('Name'),
                'description' => __('Description'),
                'date_debut' => __('Start Date'),
                'date_fin' => __('End Date'),
                'niveau' => __('Level'),
                'processus' => __('Process'),
                'activite_parent' => __('Parent Activity'),
                'organisation_id' => __('Organisation')
            ]
        ];
    }

    /**
     * Export OPAC configuration
     */
    public function export()
    {
        $config = OPACConfig::first();

        if (!$config) {
            return response()->json(['error' => 'No configuration found'], 404);
        }

        $configData = $config->toArray();
        unset($configData['id'], $configData['created_at'], $configData['updated_at']);

        return response()->json($configData)
            ->header('Content-Disposition', 'attachment; filename="opac-config.json"');
    }

    /**
     * Import OPAC configuration
     */
    public function import(Request $request)
    {
        $request->validate([
            'config_file' => 'required|file|mimes:json'
        ]);

        try {
            $content = file_get_contents($request->file('config_file')->path());
            $configData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format');
            }

            $config = OPACConfig::first();

            if (!$config) {
                $config = new OPACConfig();
            }

            // Only import allowed fields
            $allowedFields = [
                'site_name', 'site_description', 'is_enabled', 'allow_downloads',
                'require_login_for_downloads', 'items_per_page', 'contact_email',
                'footer_text', 'custom_css', 'custom_js', 'visible_record_fields',
                'visible_activity_fields', 'searchable_fields', 'allowed_file_types'
            ];

            foreach ($allowedFields as $field) {
                if (isset($configData[$field])) {
                    $config->$field = $configData[$field];
                }
            }

            $config->save();

            // Clear cache
            Cache::forget('opac_config');

            return redirect()->route('admin.opac.index')
                ->with('success', __('OPAC configuration imported successfully.'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Failed to import configuration: ') . $e->getMessage());
        }
    }
}

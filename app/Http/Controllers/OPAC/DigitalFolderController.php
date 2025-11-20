<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use Illuminate\Http\Request;

class DigitalFolderController extends Controller
{
    public function index(Request $request)
    {
        // Show root folders
        $query = RecordDigitalFolder::query()
            ->where('status', 'active')
            ->where('access_level', 'public')
            ->whereNull('parent_id');

        if ($request->filled('q')) {
            $search = $request->get('q');
            $query = RecordDigitalFolder::query()
                ->where('status', 'active')
                ->where('access_level', 'public')
                ->where('name', 'like', "%{$search}%");
        }

        $folders = $query->latest()->paginate(20);

        return view('opac.digital.folders.index', compact('folders'));
    }

    public function show($id)
    {
        $folder = RecordDigitalFolder::where('status', 'active')
            ->where('access_level', 'public')
            ->with(['children' => function($q) {
                $q->where('status', 'active')->where('access_level', 'public');
            }, 'documents' => function($q) {
                $q->where('status', 'active')->where('access_level', 'public');
            }])
            ->findOrFail($id);

        return view('opac.digital.folders.show', compact('folder'));
    }
}

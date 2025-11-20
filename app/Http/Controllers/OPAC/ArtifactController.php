<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtifactController extends Controller
{
    public function index(Request $request)
    {
        $query = RecordArtifact::query()
            ->where('status', 'active')
            ->where('access_level', 'public');

        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $artifacts = $query->latest()->paginate(12);

        $categories = RecordArtifact::where('status', 'active')
            ->where('access_level', 'public')
            ->distinct()
            ->pluck('category');

        return view('opac.artifacts.index', compact('artifacts', 'categories'));
    }

    public function show($id)
    {
        $artifact = RecordArtifact::where('status', 'active')
            ->where('access_level', 'public')
            ->with(['exhibitions', 'attachments'])
            ->findOrFail($id);

        return view('opac.artifacts.show', compact('artifact'));
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifact;
use App\Services\RecordArtifactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtifactController extends Controller
{
    protected RecordArtifactService $artifactService;

    public function __construct(RecordArtifactService $artifactService)
    {
        $this->middleware('auth');
        $this->artifactService = $artifactService;
    }

    /**
     * Display a listing of artifacts with gallery view.
     */
    public function index(Request $request)
    {
        $query = RecordArtifact::with(['type', 'creator', 'organisation']);

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $viewMode = $request->get('view', 'gallery');
        $artifacts = $query->latest()->paginate($viewMode === 'gallery' ? 12 : 20);
        
        return view('artifacts.index', compact('artifacts', 'viewMode'));
    }

    /**
     * Show the form for creating a new artifact.
     */
    public function create()
    {
        return view('artifacts.create');
    }

    /**
     * Store a newly created artifact in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'sub_category' => 'nullable|string',
            'material' => 'nullable|string',
            'acquisition_date' => 'nullable|date',
            'acquisition_method' => 'nullable|string',
            'valuation' => 'nullable|numeric',
            'condition' => 'nullable|string',
            'height' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
        ]);

        try {
            $creator = Auth::user();
            $organisation = Auth::user()->currentOrganisation;
            
            $artifact = $this->artifactService->createArtifact(
                $validated,
                $creator,
                $organisation
            );
            
            return redirect()
                ->route('artifacts.show', $artifact->id)
                ->with('success', 'Artifact created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating artifact: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified artifact with detailed information.
     */
    public function show(string $id)
    {
        $artifact = RecordArtifact::with([
            'type',
            'creator',
            'organisation',
            'images',
            'loans',
            'exhibitions',
            'conservations'
        ])->findOrFail($id);

        return view('artifacts.show', compact('artifact'));
    }

    /**
     * Show the form for editing the specified artifact.
     */
    public function edit(string $id)
    {
        $artifact = RecordArtifact::findOrFail($id);
        
        return view('artifacts.edit', compact('artifact'));
    }

    /**
     * Update the specified artifact in storage.
     */
    public function update(Request $request, string $id)
    {
        $artifact = RecordArtifact::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'condition' => 'nullable|string',
            'valuation' => 'nullable|numeric',
            'location' => 'nullable|string',
        ]);

        try {
            $artifact->update($validated);
            
            return redirect()
                ->route('artifacts.show', $artifact->id)
                ->with('success', 'Artifact updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error updating artifact: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified artifact from storage.
     */
    public function destroy(string $id)
    {
        try {
            $artifact = RecordArtifact::findOrFail($id);
            $artifact->delete();
            
            return redirect()
                ->route('artifacts.index')
                ->with('success', 'Artifact deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting artifact: ' . $e->getMessage());
        }
    }

    /**
     * Display exhibitions for the artifact.
     */
    public function exhibitions(string $id)
    {
        $artifact = RecordArtifact::with('exhibitions')->findOrFail($id);
        
        return view('artifacts.exhibitions', compact('artifact'));
    }

    /**
     * Display loan tracking for the artifact.
     */
    public function loans(string $id)
    {
        $artifact = RecordArtifact::with(['loans' => function($query) {
            $query->orderBy('loan_date', 'desc');
        }])->findOrFail($id);
        
        return view('artifacts.loans', compact('artifact'));
    }

    /**
     * Add an image to the artifact.
     */
    public function addImage(Request $request, string $id)
    {
        $artifact = RecordArtifact::findOrFail($id);
        
        $validated = $request->validate([
            'image' => 'required|image|max:5120',
            'caption' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        try {
            $path = $request->file('image')->store('artifacts/images', 'public');
            
            $artifact->images()->create([
                'path' => $path,
                'caption' => $validated['caption'] ?? null,
                'is_primary' => $validated['is_primary'] ?? false,
            ]);
            
            return back()->with('success', 'Image added successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error adding image: ' . $e->getMessage());
        }
    }
}

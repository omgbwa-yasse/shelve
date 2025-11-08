<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordPeriodical;
use App\Models\RecordPeriodicalIssue;
use Illuminate\Http\Request;

class PeriodicalController extends Controller
{
    /**
     * Display issues of a periodical.
     */
    public function issues($id)
    {
        $periodical = RecordPeriodical::with('issues')->findOrFail($id);
        $issues = $periodical->issues()->orderBy('publication_date', 'desc')->paginate(20);

        return view('library.periodicals.issues', compact('periodical', 'issues'));
    }

    /**
     * Store a new issue.
     */
    public function storeIssue(Request $request, $id)
    {
        $periodical = RecordPeriodical::findOrFail($id);

        $validated = $request->validate([
            'volume' => 'nullable|string|max:50',
            'number' => 'required|string|max:50',
            'publication_date' => 'required|date',
            'pages' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $validated['periodical_id'] = $periodical->id;

        RecordPeriodicalIssue::create($validated);

        return redirect()->route('library.periodicals.issues', $periodical->id)
            ->with('success', 'Numéro ajouté avec succès.');
    }
}

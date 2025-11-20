<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\WorkplaceActivity;
use Illuminate\Http\Request;

class WorkplaceActivityController extends Controller
{
    public function index(Request $request, Workplace $workplace)
    {
        $this->authorize('view', $workplace);

        $query = $workplace->activities()
            ->with('user')
            ->latest();

        if ($request->filled('type')) {
            $query->where('activity_type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(20);

        return view('workplaces.activities.index', compact('workplace', 'activities'));
    }
}

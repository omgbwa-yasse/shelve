<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\RecordPeriodic;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get statistics (modèle unifié Record)
        $stats = [
            'records' => Record::currentVersion()->count(),
            'physical' => Record::currentVersion()->whereHas('mediums', fn ($q) => $q->physical())->count(),
            'digital' => Record::currentVersion()->whereHas('mediums', fn ($q) => $q->digital())->count(),
            'periodicals' => RecordPeriodic::count(),
        ];

        // Get recent activities (placeholder - you'll need to implement an Activity model)
        $recentActivities = collect([
            (object) [
                'description' => 'Created document',
                'subject' => 'Annual Report 2024.pdf',
                'created_at' => now()->subHours(2),
            ],
            (object) [
                'description' => 'Updated folder',
                'subject' => 'Financial Documents',
                'created_at' => now()->subHours(5),
            ],

        ]);

        return view('dashboard', compact('stats', 'recentActivities'));
    }
}

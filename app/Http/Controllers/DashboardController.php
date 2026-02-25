<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
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
        // Get statistics
        $stats = [
            'folders' => RecordDigitalFolder::count(),
            'documents' => RecordDigitalDocument::count(),
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

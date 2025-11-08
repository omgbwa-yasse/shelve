<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminPanelController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Admin dashboard with system overview.
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_organisations' => \App\Models\Organisation::count(),
            'total_records' => \App\Models\Record::count(),
            'total_documents' => \App\Models\RecordDigitalDocument::count(),
            'active_sessions' => DB::table('sessions')->count(),
        ];

        $recentActivity = \App\Models\Log::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }

    /**
     * User management interface.
     */
    public function users(Request $request)
    {
        $query = \App\Models\User::with(['roles', 'organisations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('id', $request->role);
            });
        }

        $users = $query->paginate(20);
        $roles = \App\Models\Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * System settings and configuration.
     */
    public function settings()
    {
        $settings = \App\Models\Setting::with('category')
            ->orderBy('category_id')
            ->orderBy('order')
            ->get()
            ->groupBy('category.name');

        return view('admin.settings', compact('settings'));
    }

    /**
     * System audit logs viewer.
     */
    public function logs(Request $request)
    {
        $query = \App\Models\Log::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
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

        $logs = $query->latest()->paginate(50);
        $actions = \App\Models\Log::distinct('action')->pluck('action');

        return view('admin.logs', compact('logs', 'actions'));
    }
}

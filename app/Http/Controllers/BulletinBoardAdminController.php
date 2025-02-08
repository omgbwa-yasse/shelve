<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\User;
use Illuminate\Http\Request;

class BulletinBoardAdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_posts' => BulletinBoard::count(),
            'total_events' => BulletinBoard::where('type', 'event')->count(),
            'total_users' => User::count(),
            'recent_activities' => BulletinBoard::with('user')->latest()->take(10)->get()
        ];

        return view('bulletin-boards.admin.index', compact('stats'));
    }

    public function settings()
    {
        $settings = [
            'allow_comments' => setting('bulletin_board.allow_comments', true),
            'moderation_required' => setting('bulletin_board.moderation_required', false),
            'max_file_size' => setting('bulletin_board.max_file_size', 5), // MB
            'allowed_file_types' => setting('bulletin_board.allowed_file_types', ['pdf', 'doc', 'docx', 'jpg', 'png']),
        ];

        return view('bulletin-boards.admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'allow_comments' => 'boolean',
            'moderation_required' => 'boolean',
            'max_file_size' => 'integer|min:1|max:50',
            'allowed_file_types' => 'array',
            'allowed_file_types.*' => 'string'
        ]);

        foreach ($validated as $key => $value) {
            setting(['bulletin_board.' . $key => $value]);
        }

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    public function users()
    {
        $users = User::with(['roles', 'bulletinBoards'])
            ->withCount('bulletinBoards')
            ->paginate(15);

        return view('bulletin-boards.admin.users', compact('users'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        $user->roles()->sync($validated['roles']);

        return back()->with('success', 'Permissions mises à jour avec succès.');
    }
}

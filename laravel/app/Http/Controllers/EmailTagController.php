<?php

namespace App\Http\Controllers;

use App\Models\EmailTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EmailTagController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', EmailTag::class);

        $tags = EmailTag::byOrganisation(Auth::user()->current_organisation_id)
            ->withCount('messages')
            ->orderBy('name')
            ->get();

        return view('mails.email.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', EmailTag::class);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $data['created_by'] = Auth::id();

        EmailTag::create($data);

        return back()->with('success', 'Étiquette créée.');
    }

    public function update(Request $request, EmailTag $emailTag)
    {
        Gate::authorize('update', $emailTag);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $emailTag->update($data);

        return back()->with('success', 'Étiquette mise à jour.');
    }

    public function destroy(EmailTag $emailTag)
    {
        Gate::authorize('delete', $emailTag);

        $emailTag->delete();

        return back()->with('success', 'Étiquette supprimée.');
    }
}

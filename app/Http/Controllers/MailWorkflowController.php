<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailWorkflowController extends Controller
{
    /**
     * Display a listing of the workflows for the user's current organisation.
     */
    public function index()
    {
        $user = Auth::user();
        $workflows = collect();

        if ($user->current_organisation_id) {
            // Récupérer les workflows assignés à l'organisation
            $workflows = WorkflowInstance::assignedToOrganisation($user->current_organisation_id)
                ->latest()
                ->get();
        }

        return view('mails.workflows.index', compact('workflows'));
    }

    /**
     * Display a listing of the workflows assigned to the current user.
     */
    public function myWorkflows()
    {
        $user = Auth::user();

        // Récupérer les workflows où l'utilisateur a des assignations
        $workflows = WorkflowInstance::whereHas('stepInstances', function ($query) use ($user) {
            $query->where('assigned_to_user_id', $user->id);
        })->latest()->get();

        return view('mails.workflows.my-workflows', compact('workflows'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    /**
     * Display a listing of the workflows for the user's current organisation.
     */
    public function index()
    {
        $user = Auth::user();
        $organisation = $user->currentOrganisation;
        $workflows = collect();
        if ($organisation) {
            $workflows = WorkflowInstance::whereHas('template.organisations', function ($query) use ($organisation) {
                $query->where('organisation_id', $organisation->id);
            })->latest()->get();
        }
        return view('mails.workflows.index', compact('workflows'));
    }

    /**
     * Display a listing of the workflows assigned to the current user.
     */
    public function myWorkflows()
    {
        $user = Auth::user();
        $workflows = WorkflowInstance::whereHas('steps.assignments', function ($query) use ($user) {
            $query->where('assignee_id', $user->id)->where('assignee_type', 'user');
        })->latest()->get();
        return view('mails.workflows.my-workflows', compact('workflows'));
    }
}

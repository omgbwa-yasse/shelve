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
        $organisation = $user->currentOrganisation;
        $workflows = collect();
        
        if ($organisation) {
            // Récupérer les workflows via les templates associés à l'organisation
            $workflows = WorkflowInstance::whereHas('template', function ($query) use ($organisation) {
                $query->whereHas('organisations', function ($subQuery) use ($organisation) {
                    $subQuery->where('organisation_id', $organisation->id);
                });
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
        
        // Récupérer les workflows où l'utilisateur a des assignations
        $workflows = WorkflowInstance::whereHas('stepInstances', function ($query) use ($user) {
            $query->whereHas('assignments', function ($subQuery) use ($user) {
                $subQuery->where('assignee_type', 'user')
                        ->where('assignee_id', $user->id);
            });
        })->latest()->get();
        
        return view('mails.workflows.my-workflows', compact('workflows'));
    }
}

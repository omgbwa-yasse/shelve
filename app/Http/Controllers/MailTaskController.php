<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailTaskController extends Controller
{
    /**
     * Display a listing of the tasks for the user's current organisation.
     */
    public function index()
    {
        $user = Auth::user();
        $tasks = collect();
        
        if ($user && $user->currentOrganisation) {
            // Pour l'instant, récupérons toutes les tâches - à adapter selon le modèle
            $tasks = Task::latest()->get();
        }
        
        return view('mails.tasks.index', compact('tasks'));
    }

    /**
     * Display a listing of the tasks assigned to the current user.
     */
    public function myTasks()
    {
        $user = Auth::user();
        $tasks = collect();
        
        if ($user) {
            // Pour l'instant, récupérons toutes les tâches - à adapter selon le modèle
            $tasks = Task::latest()->get();
        }
        
        return view('mails.tasks.my-tasks', compact('tasks'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use App\Models\Task;
use App\Models\User;
use App\Models\Organisation;
use App\Enums\TaskAssigneeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskAssignmentController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Affiche tous les assignés d'une tâche.
     */
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $assignments = $task->assignments()
            ->with(['assigneeUser', 'assigneeOrganisation'])
            ->orderBy('created_at')
            ->get();

        return view('tasks.assignments.index', compact('task', 'assignments'));
    }

    /**
     * Montre le formulaire d'ajout d'assigné.
     */
    public function create(Task $task)
    {
        $this->authorize('assign', $task);

        $users = User::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();

        return view('tasks.assignments.create', compact('task', 'users', 'organisations'));
    }

    /**
     * Enregistre un nouvel assigné pour une tâche.
     */
    public function store(Request $request, Task $task)
    {
        $this->authorize('assign', $task);

        $validated = $request->validate([
            'assignee_type' => 'required|string',
            'assignee_id' => 'required|integer',
            'role' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        // Vérifier que l'assignation n'existe pas déjà
        $existingAssignment = $task->assignments()
            ->where('assignee_type', $validated['assignee_type'])
            ->where('assignee_id', $validated['assignee_id'])
            ->exists();

        if ($existingAssignment) {
            return back()
                ->withInput()
                ->with('error', 'Cet assigné existe déjà pour cette tâche.');
        }

        // Créer la nouvelle assignation
        DB::beginTransaction();
        try {
            $assignment = new TaskAssignment();
            $assignment->task_id = $task->id;
            $assignment->assignee_type = $validated['assignee_type'];
            $assignment->assignee_id = $validated['assignee_id'];
            $assignment->role = $validated['role'] ?? null;
            $assignment->note = $validated['note'] ?? null;
            $assignment->assigned_by = Auth::id();
            $assignment->save();

            // Créer une entrée dans l'historique des assignations
            $assignment->addToHistory('assign');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'assignation : ' . $e->getMessage());
        }

        return redirect()
            ->route('tasks.assignments.index', $task)
            ->with('success', 'Assigné ajouté avec succès.');
    }

    /**
     * Supprime une assignation.
     */
    public function destroy(Task $task, TaskAssignment $assignment)
    {
        $this->authorize('delete', $assignment);

        // Vérifier que l'assignation appartient bien à la tâche
        if ($assignment->task_id !== $task->id) {
            return back()->with('error', 'Cette assignation n\'appartient pas à cette tâche.');
        }

        DB::beginTransaction();
        try {
            // Créer une entrée dans l'historique des assignations
            $assignment->addToHistory('unassign');

            // Supprimer l'assignation
            $assignment->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        }

        return back()->with('success', 'Assignation supprimée avec succès.');
    }
}

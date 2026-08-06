<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'surname',
        'birthday',
        'current_organisation_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'current_organisation_id');
    }

    /**
     * Alias for organisation relationship (for backward compatibility)
     */
    public function currentOrganisation()
    {
        return $this->organisation();
    }

    /**
     * Many-to-many relationship with organisations through user_organisation_role pivot table
     */
    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'user_organisation_role', 'user_id', 'organisation_id');
    }

    public function recordsCreated()
    {
        return $this->hasMany(RecordPhysical::class, 'user_id');
    }

    public function communications()
    {
        return $this->hasMany(Communication::class, 'user_id');
    }

    public function operatorCommunications()
    {
        return $this->hasMany(Communication::class, 'operator_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    public function operatorReservations()
    {
        return $this->hasMany(Reservation::class, 'operator_id');
    }

    // Workflow and Task Relations
    public function createdWorkflowDefinitions()
    {
        return $this->hasMany(WorkflowDefinition::class, 'created_by');
    }

    public function updatedWorkflowDefinitions()
    {
        return $this->hasMany(WorkflowDefinition::class, 'updated_by');
    }

    public function startedWorkflowInstances()
    {
        return $this->hasMany(WorkflowInstance::class, 'started_by');
    }

    public function completedWorkflowInstances()
    {
        return $this->hasMany(WorkflowInstance::class, 'completed_by');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function completedTasks()
    {
        return $this->hasMany(Task::class, 'completed_by');
    }

    public function taskComments()
    {
        return $this->hasMany(TaskComment::class, 'user_id');
    }

    public function watchedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_watchers', 'user_id', 'task_id')
            ->withPivot('notify_on_update', 'notify_on_comment', 'notify_on_completion', 'added_at');
    }

    public function taskReminders()
    {
        return $this->hasMany(TaskReminder::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return Cache::remember("user_{$this->id}_is_superadmin", 3600, function () {
            return $this->hasRole('superadmin');
        });
    }

    /**
     * Relations avec les rôles (système natif)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Relations avec les permissions (système natif)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * Favoris de l'utilisateur (étape 8 — collaboration).
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    /** Projets/OKR/KPI rattachés directement à cette personne — voir App\Traits\HasAttachable. */
    public function projects()
    {
        return $this->morphMany(Project::class, 'attachable');
    }

    public function objectives()
    {
        return $this->morphMany(Objective::class, 'attachable');
    }

    public function kpis()
    {
        return $this->morphMany(Kpi::class, 'attachable');
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        // Vérifier les permissions directes
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // Vérifier les permissions via les rôles
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }

    /**
     * Assigner un rôle à l'utilisateur
     */
    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && ! $this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Retirer un rôle de l'utilisateur
     */
    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Donner une permission directe à l'utilisateur
     */
    public function givePermissionTo(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission && ! $this->hasPermissionTo($permissionName)) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Synchroniser les rôles de l'utilisateur
     */
    public function syncRoles(array $roleNames): void
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');
        $this->roles()->sync($roleIds);
    }

    public function hasPermission(string $permissionName): bool
    {
        // Si c'est un superadmin, autoriser tout
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Utiliser le système natif pour vérifier la permission
        return $this->hasPermissionTo($permissionName);
    }

    /**
     * Permissions effectives : permissions directes (`user_permissions`) réunies aux
     * permissions héritées des rôles (`user_roles` → `role_permissions`). Utilisé pour
     * piloter l'affichage côté client (`AuthController::effectivePermissions()`) et pour
     * borner les capacités présentées à l'assistant IA (voir `AiCapabilityService`).
     */
    public function effectivePermissionNames(): array
    {
        $direct = $this->permissions()->pluck('name');

        $viaRoles = $this->roles()
            ->with('permissions:id,name')
            ->get()
            ->flatMap(fn ($role) => $role->permissions->pluck('name'));

        return $direct->merge($viaRoles)->unique()->sort()->values()->all();
    }
}

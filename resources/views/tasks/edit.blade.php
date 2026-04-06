@extends('layouts.app')

@push('styles')
<style>
    .edit-card {
        max-width: 850px; margin: 0 auto;
        background: #fff; border: 1px solid #e9ecef;
        border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,.08);
        overflow: hidden;
    }
    .edit-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        padding: 24px 28px; color: #fff;
    }
    .edit-header h2 { font-weight: 700; margin: 0; font-size: 1.3rem; }
    .edit-header p { margin: 4px 0 0; opacity: .8; font-size: .88rem; }
    .edit-body { padding: 28px; }

    .field-label { font-weight: 600; font-size: .88rem; color: #374151; margin-bottom: 6px; display: block; }
    .section-divider {
        margin: 32px 0 20px; padding-bottom: 8px;
        border-bottom: 1px solid #f1f3f5;
        font-weight: 700; font-size: .82rem;
        text-transform: uppercase; color: #94a3b8; letter-spacing: .05em;
    }

    /* Status & Priority visual selectors */
    .selector-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; }
    .selector-option { cursor: pointer; position: relative; }
    .selector-option input { position: absolute; opacity: 0; width: 0; height: 0; }
    .selector-card {
        padding: 10px; border: 2px solid #f1f3f5; border-radius: 12px;
        text-align: center; font-size: .82rem; font-weight: 600;
        transition: all .2s; background: #fafafa; color: #64748b;
    }
    .selector-option input:checked + .selector-card { border-color: #0d6efd; background: #f0f7ff; color: #0d6efd; }

    /* Assignee filter */
    .assignee-filter { display: grid; grid-template-columns: 1fr 1.2fr; gap: 15px; }
    @media (max-width: 600px) { .assignee-filter { grid-template-columns: 1fr; } }

    .field-hint { font-size: .75rem; color: #9ca3af; margin-top: 5px; line-height: 1.4; }

    /* Task Type Badge */
    .type-badge { font-size: .65rem; padding: 2px 8px; border-radius: 10px; font-weight: 800; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3" style="max-width:850px; margin: 0 auto;">
        <a href="{{ route('tasks.show', $task) }}" class="text-muted text-decoration-none small d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> {{ __('Retour aux détails') }}
        </a>
        @if($task->isWorkflowTask)
            <span class="type-badge bg-info text-white">{{ __('Tâche de processus') }}</span>
        @else
            <span class="type-badge bg-secondary text-white">{{ __('Tâche générale') }}</span>
        @endif
    </div>

    <div class="edit-card">
        <div class="edit-header">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
                    <i class="bi bi-pencil"></i>
                </div>
                <div>
                    <h2>{{ __('Modifier la tâche') }}</h2>
                    <p>{{ __('Mise à jour des informations de « :title »', ['title' => Str::limit($task->title, 30)]) }}</p>
                </div>
            </div>
        </div>

        <div class="edit-body">
            <form action="{{ route('tasks.update', $task) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- ── Informations principales ──────────────── --}}
                <div class="mb-4">
                    <label for="title" class="field-label">{{ __('Titre de la tâche') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title', $task->title) }}"
                           required maxlength="190" style="border-radius:10px; font-weight: 500;">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="field-label">{{ __('Description détaillée') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3"
                              style="border-radius:10px;">{{ old('description', $task->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="field-label">{{ __('Statut actuel') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status" style="border-radius:10px;">
                            <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                            <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>{{ __('En cours') }}</option>
                            <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>{{ __('Terminée') }}</option>
                            <option value="cancelled" {{ old('status', $task->status) == 'cancelled' ? 'selected' : '' }}>{{ __('Annulée') }}</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="due_date" class="field-label">{{ __('Échéance') }}</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                               id="due_date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                               style="border-radius:10px;">
                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- ── Priorité ───────────────────────────── --}}
                <div class="mb-4">
                    <label class="field-label">{{ __('Priorité') }} <span class="text-danger">*</span></label>
                    <div class="selector-grid">
                        @foreach(['low' => '🔵 Basse', 'normal' => '⚪ Normale', 'high' => '🟡 Haute', 'urgent' => '🔴 Urgente'] as $val => $lbl)
                            <label class="selector-option">
                                <input type="radio" name="priority" value="{{ $val }}" {{ old('priority', $task->priority) == $val ? 'checked' : '' }}>
                                <div class="selector-card">{{ $lbl }}</div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="section-divider">{{ __('Assignation') }}</div>

                {{-- ── Assignation avec filtre direction ───── --}}
                <div class="mb-4">
                    <div class="assignee-filter">
                        <div>
                            <label class="form-label small text-muted mb-1">{{ __('Direction (Filtre)') }}</label>
                            <select class="form-select" id="roleFilter" style="border-radius:8px;">
                                <option value="">{{ __('Toutes les directions') }}</option>
                                @foreach($roles as $role)
                                    @if($role->users->count() > 0)
                                        <option value="{{ $role->id }}">
                                            {{ $role->display_name ?: $role->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="assigned_to" class="form-label small text-muted mb-1">{{ __('Utilisateur assigné') }}</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror"
                                    id="assigned_to" name="assigned_to" style="border-radius:8px;">
                                <option value="">{{ __('Non assignée') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                            data-roles="{{ $user->roles->pluck('id')->join(',') }}"
                                            {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-light px-4" style="border-radius:10px;">
                        {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary px-5" style="border-radius:10px; font-weight: 600;">
                        <i class="bi bi-check-lg me-1"></i> {{ __('Enregistrer les modifications') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleFilter = document.getElementById('roleFilter');
    const userSelect = document.getElementById('assigned_to');
    const allUsers   = Array.from(userSelect.options);

    roleFilter.addEventListener('change', function() {
        const roleId = this.value;
        userSelect.innerHTML = '';

        allUsers.forEach(opt => {
            if (!opt.value) { // Option "Non assignée" toujours là
                userSelect.appendChild(opt);
                return;
            }
            if (!roleId) { // Tout afficher
                userSelect.appendChild(opt);
            } else {
                const roles = opt.dataset.roles.split(',');
                if (roles.includes(roleId)) {
                    userSelect.appendChild(opt);
                }
            }
        });
    });

    // Initial check (if a role is somehow pre-selected or to handle initial state if needed)
    // But usually roleFilter starts empty on edit too unless we pre-select it based on task->assignedUser->roles
});
</script>
@endpush
@endsection

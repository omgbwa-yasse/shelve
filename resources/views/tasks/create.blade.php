@extends('layouts.app')

@push('styles')
<style>
    .create-task-card {
        max-width: 760px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .create-task-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0a4fbf 100%);
        padding: 24px 28px;
        color: #fff;
    }
    .create-task-header h2 { font-weight: 700; margin: 0; font-size: 1.4rem; }
    .create-task-header p  { margin: 4px 0 0; opacity: .85; font-size: .88rem; }
    .create-task-body { padding: 26px 28px; }

    .field-label { font-weight: 600; font-size: .88rem; color: #374151; margin-bottom: 5px; }
    .field-hint  { font-size: .75rem; color: #9ca3af; margin-top: 3px; }

    .priority-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .priority-option { cursor: pointer; }
    .priority-option input { display: none; }
    .priority-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 10px 6px;
        text-align: center;
        transition: all .18s;
        font-size: .8rem; font-weight: 600;
    }
    .priority-card .picon { font-size: 1.2rem; display: block; margin-bottom: 4px; }
    .priority-option input:checked + .priority-card { border-color: #0d6efd; background: #e8f0fe; }
    .p-urgent  .priority-card { color: #842029; }
    .p-urgent  input:checked + .priority-card { border-color: #dc3545; background: #fdf2f2; }
    .p-high    .priority-card { color: #856404; }
    .p-high    input:checked + .priority-card { border-color: #ffc107; background: #fffbec; }
    .p-normal  .priority-card { color: #374151; }
    .p-low     .priority-card { color: #0c5460; }
    .p-low     input:checked + .priority-card { border-color: #17a2b8; background: #eaf7fa; }

    .section-divider {
        display: flex; align-items: center; gap: 10px;
        margin: 22px 0 16px;
        font-size: .78rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #9ca3af;
    }
    .section-divider::before, .section-divider::after {
        content: ''; flex: 1; height: 1px; background: #e9ecef;
    }

    .assignee-filter {
        display: flex; gap: 8px; align-items: flex-end;
    }
    .assignee-filter > div { flex: 1; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <a href="{{ route('tasks.index') }}" class="text-muted text-decoration-none small d-inline-flex align-items-center gap-1 mb-3">
        <i class="bi bi-arrow-left"></i> {{ __('Retour aux tâches') }}
    </a>

    <div class="create-task-card">
        {{-- Header --}}
        <div class="create-task-header">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
                    <i class="bi bi-plus-circle"></i>
                </div>
                <div>
                    <h2>{{ __('Nouvelle tâche') }}</h2>
                    <p>{{ __('Tâche indépendante de tout processus') }}</p>
                </div>
            </div>
        </div>

        <div class="create-task-body">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                {{-- Status always pending on creation --}}
                <input type="hidden" name="status" value="pending">

                {{-- ── Titre ─────────────────────────────── --}}
                <div class="mb-4">
                    <label for="title" class="field-label">{{ __('Titre de la tâche') }} <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control form-control-lg @error('title') is-invalid @enderror"
                           id="title" name="title"
                           value="{{ old('title') }}"
                           required maxlength="190"
                           placeholder="{{ __('Ex: Préparer le rapport mensuel') }}"
                           style="border-radius:10px;">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- ── Description ───────────────────────── --}}
                <div class="mb-4">
                    <label for="description" class="field-label">
                        {{ __('Description') }}
                        <span class="text-muted fw-normal" style="font-size:.78rem;">{{ __('(optionnel)') }}</span>
                    </label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3"
                              placeholder="{{ __('Ajoutez des détails ou des instructions…') }}"
                              style="border-radius:10px;">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- ── Priorité ───────────────────────────── --}}
                <div class="mb-4">
                    <label class="field-label">{{ __('Priorité') }} <span class="text-danger">*</span></label>
                    <div class="priority-grid">
                        <label class="priority-option p-low">
                            <input type="radio" name="priority" value="low" {{ old('priority') == 'low' ? 'checked' : '' }}>
                            <div class="priority-card"><span class="picon">🔵</span>{{ __('Basse') }}</div>
                        </label>
                        <label class="priority-option p-normal">
                            <input type="radio" name="priority" value="normal" {{ old('priority', 'normal') == 'normal' ? 'checked' : '' }}>
                            <div class="priority-card"><span class="picon">⚪</span>{{ __('Normale') }}</div>
                        </label>
                        <label class="priority-option p-high">
                            <input type="radio" name="priority" value="high" {{ old('priority') == 'high' ? 'checked' : '' }}>
                            <div class="priority-card"><span class="picon">🟡</span>{{ __('Haute') }}</div>
                        </label>
                        <label class="priority-option p-urgent">
                            <input type="radio" name="priority" value="urgent" {{ old('priority') == 'urgent' ? 'checked' : '' }}>
                            <div class="priority-card"><span class="picon">🔴</span>{{ __('Urgente') }}</div>
                        </label>
                    </div>
                    @error('priority') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="section-divider">{{ __('Assignation & Planification') }}</div>

                {{-- ── Assigné à (filtré par direction/rôle) ── --}}
                <div class="mb-4">
                    <label class="field-label">{{ __('Assignée à') }}</label>
                    <div class="assignee-filter mb-2">
                        {{-- Filtre direction --}}
                        <div>
                            <label class="form-label small text-muted mb-1">{{ __('Filtrer par direction') }}</label>
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
                        {{-- Select utilisateur --}}
                        <div>
                            <label for="assigned_to" class="form-label small text-muted mb-1">{{ __('Utilisateur') }}</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror"
                                    id="assigned_to" name="assigned_to" style="border-radius:8px;">
                                <option value="">{{ __('Non assignée') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                            data-roles="{{ $user->roles->pluck('id')->join(',') }}"
                                            {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="field-hint"><i class="bi bi-person me-1"></i>{{ __('Sélectionnez une direction pour filtrer les utilisateurs.') }}</div>
                </div>

                {{-- ── Date limite ────────────────────────── --}}
                <div class="mb-4">
                    <label for="due_date" class="field-label">{{ __('Date limite') }}</label>
                    <input type="date"
                           class="form-control @error('due_date') is-invalid @enderror"
                           id="due_date" name="due_date"
                           value="{{ old('due_date') }}"
                           style="border-radius:10px; max-width:220px;">
                    @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="field-hint"><i class="bi bi-calendar me-1"></i>{{ __('Optionnel — recommandé pour le suivi.') }}</div>
                </div>

                {{-- ── Boutons ─────────────────────────────── --}}
                <div class="d-flex justify-content-between mt-4 pt-2 border-top">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                        <i class="bi bi-x"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg d-flex align-items-center gap-2" style="border-radius:10px;">
                        <i class="bi bi-check2"></i> {{ __('Créer la tâche') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Build a map: roleId -> array of user option elements
const roleFilter = document.getElementById('roleFilter');
const assignedTo = document.getElementById('assigned_to');

// Store all user options
const allOptions = Array.from(assignedTo.querySelectorAll('option[data-roles]'));

roleFilter.addEventListener('change', function() {
    const selectedRole = this.value;
    const currentVal  = assignedTo.value;

    // Remove all user options
    allOptions.forEach(opt => opt.remove());

    // Re-add matching ones
    allOptions.forEach(opt => {
        const roles = opt.dataset.roles ? opt.dataset.roles.split(',') : [];
        if (!selectedRole || roles.includes(selectedRole)) {
            assignedTo.appendChild(opt);
        }
    });

    // Try to preserve selection
    if ([...assignedTo.options].some(o => o.value === currentVal)) {
        assignedTo.value = currentVal;
    } else {
        assignedTo.value = '';
    }
});
</script>
@endpush

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> {{ __('Modifier Tâche') }}</h1>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('tasks.update', $task) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{ __('Titre') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $task->title) }}" required maxlength="190">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="5">{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">{{ __('Statut') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                                        <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>{{ __('En cours') }}</option>
                                        <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>{{ __('Terminée') }}</option>
                                        <option value="cancelled" {{ old('status', $task->status) == 'cancelled' ? 'selected' : '' }}>{{ __('Annulée') }}</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">{{ __('Priorité') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                        <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>{{ __('Basse') }}</option>
                                        <option value="normal" {{ old('priority', $task->priority) == 'normal' ? 'selected' : '' }}>{{ __('Normale') }}</option>
                                        <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>{{ __('Haute') }}</option>
                                        <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>{{ __('Urgente') }}</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label">{{ __('Assigné à') }}</label>
                                    <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                                        <option value="">{{ __('Non assignée') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">{{ __('Date limite') }}</label>
                                    <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                           id="due_date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">{{ __('Informations') }}</h6>
                                <hr>
                                <p class="small mb-2">
                                    <strong>{{ __('Type:') }}</strong>
                                    @if($task->isWorkflowTask)
                                        <span class="badge bg-info">{{ __('Workflow') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Générale') }}</span>
                                    @endif
                                </p>
                                <p class="small mb-2">
                                    <strong>{{ __('Créée par:') }}</strong> {{ $task->creator->name ?? 'N/A' }}
                                </p>
                                <p class="small mb-2">
                                    <strong>{{ __('Date création:') }}</strong> {{ $task->created_at->format('d/m/Y H:i') }}
                                </p>
                                @if($task->completed_at)
                                    <p class="small mb-0">
                                        <strong>{{ __('Terminée le:') }}</strong> {{ $task->completed_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if($task->isOverdue)
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                {{ __('Cette tâche est en retard !') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('Mettre à jour') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-list-task"></i> {{ $task->title }}
            @if($task->isOverdue)
                <span class="badge bg-danger">{{ __('En retard') }}</span>
            @endif
        </h1>
        <div>
            @if($task->status != 'completed')
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                </a>
            @endif
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informations principales -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Informations de la tâche') }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __('Titre') }}</dt>
                        <dd class="col-sm-9">{{ $task->title }}</dd>

                        <dt class="col-sm-3">{{ __('Description') }}</dt>
                        <dd class="col-sm-9">{{ $task->description ?: __('Aucune description') }}</dd>

                        <dt class="col-sm-3">{{ __('Statut') }}</dt>
                        <dd class="col-sm-9">
                            @switch($task->status)
                                @case('pending')
                                    <span class="badge bg-secondary">{{ __('En attente') }}</span>
                                    @break
                                @case('in_progress')
                                    <span class="badge bg-primary">{{ __('En cours') }}</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success">{{ __('Terminée') }}</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">{{ __('Annulée') }}</span>
                                    @break
                            @endswitch
                        </dd>

                        <dt class="col-sm-3">{{ __('Priorité') }}</dt>
                        <dd class="col-sm-9">
                            @switch($task->priority)
                                @case('urgent')
                                    <span class="badge bg-danger">{{ __('Urgente') }}</span>
                                    @break
                                @case('high')
                                    <span class="badge bg-warning">{{ __('Haute') }}</span>
                                    @break
                                @case('normal')
                                    <span class="badge bg-info">{{ __('Normale') }}</span>
                                    @break
                                @case('low')
                                    <span class="badge bg-secondary">{{ __('Basse') }}</span>
                                    @break
                            @endswitch
                        </dd>

                        <dt class="col-sm-3">{{ __('Type') }}</dt>
                        <dd class="col-sm-9">
                            @if($task->isWorkflowTask)
                                <span class="badge bg-info">
                                    <i class="bi bi-diagram-3"></i> {{ __('Tâche de workflow') }}
                                </span>
                                @if($task->workflowInstance)
                                    <br>
                                    <small>
                                        <a href="{{ route('workflows.instances.show', $task->workflowInstance) }}">
                                            {{ $task->workflowInstance->name }}
                                        </a>
                                    </small>
                                @endif
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-list-task"></i> {{ __('Tâche générale') }}
                                </span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">{{ __('Assigné à') }}</dt>
                        <dd class="col-sm-9">{{ $task->assignedUser->name ?? __('Non assignée') }}</dd>

                        <dt class="col-sm-3">{{ __('Date limite') }}</dt>
                        <dd class="col-sm-9">
                            {{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}
                            @if($task->isOverdue)
                                <span class="badge bg-danger ms-2">{{ __('En retard') }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">{{ __('Créée par') }}</dt>
                        <dd class="col-sm-9">{{ $task->creator->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">{{ __('Date création') }}</dt>
                        <dd class="col-sm-9">{{ $task->created_at->format('d/m/Y H:i') }}</dd>

                        @if($task->completed_at)
                            <dt class="col-sm-3">{{ __('Terminée le') }}</dt>
                            <dd class="col-sm-9">
                                {{ $task->completed_at->format('d/m/Y H:i') }}
                                ({{ __('par') }} {{ $task->completer->name ?? 'N/A' }})
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Pièces jointes -->
            @if($task->attachments->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-paperclip"></i> {{ __('Pièces jointes') }}
                            <span class="badge bg-primary">{{ $task->attachments->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Attaché par') }}</th>
                                        <th>{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($task->attachments as $attachment)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $attachment->attachable_type }}</span>
                                            </td>
                                            <td>{{ $attachment->description }}</td>
                                            <td>{{ $attachment->attachedBy->name ?? 'N/A' }}</td>
                                            <td>{{ $attachment->attached_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Commentaires -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-left-text"></i> {{ __('Commentaires') }}
                        <span class="badge bg-primary">{{ $task->comments->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($task->comments->count() > 0)
                        @foreach($task->comments as $comment)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">
                                            <i class="bi bi-person-circle"></i> {{ $comment->user->name ?? 'N/A' }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $comment->created_at->format('d/m/Y H:i') }}
                                            @if($comment->isEdited)
                                                <span class="badge bg-info">{{ __('modifié') }}</span>
                                            @endif
                                        </small>
                                    </div>
                                    <p class="mb-0 mt-2">{{ $comment->comment }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">{{ __('Aucun commentaire pour l\'instant.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Observateurs -->
            @if($task->watchers->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-eye"></i> {{ __('Observateurs') }}
                            <span class="badge bg-primary">{{ $task->watchers->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($task->watchers as $watcher)
                                <li class="mb-2">
                                    <i class="bi bi-person"></i> {{ $watcher->user->name ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">
                                        @if($watcher->notify_on_update) <i class="bi bi-bell"></i> Màj @endif
                                        @if($watcher->notify_on_comment) <i class="bi bi-chat"></i> Comm. @endif
                                        @if($watcher->notify_on_completion) <i class="bi bi-check"></i> Fin @endif
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Rappels -->
            @if($task->reminders->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-alarm"></i> {{ __('Rappels') }}
                            <span class="badge bg-primary">{{ $task->reminders->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($task->reminders as $reminder)
                                <li class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $reminder->remind_at->format('d/m/Y H:i') }}</span>
                                        @if($reminder->is_sent)
                                            <span class="badge bg-success">{{ __('Envoyé') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('En attente') }}</span>
                                        @endif
                                    </div>
                                    @if($reminder->message)
                                        <small class="text-muted">{{ $reminder->message }}</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Historique -->
            @if($task->history->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> {{ __('Historique') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($task->history->sortByDesc('changed_at')->take(10) as $history)
                                <div class="mb-3">
                                    <small class="text-muted">{{ $history->changed_at->format('d/m/Y H:i') }}</small>
                                    <br>
                                    <strong>{{ $history->action }}</strong>
                                    @if($history->field_changed)
                                        <br>
                                        <small>
                                            {{ $history->field_changed }}:
                                            <del class="text-danger">{{ $history->old_value }}</del>
                                            →
                                            <ins class="text-success">{{ $history->new_value }}</ins>
                                        </small>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ __('par') }} {{ $history->user->name ?? 'N/A' }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Actions') }}</h5>
                </div>
                <div class="card-body">
                    @if($task->status != 'completed')
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning w-100 mb-2">
                            <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                        </a>
                    @endif
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer cette tâche ?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> {{ __('Supprimer') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

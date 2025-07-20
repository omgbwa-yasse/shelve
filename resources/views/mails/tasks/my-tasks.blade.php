@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Mes Tâches') }}</h3>
                </div>
                <div class="card-body">
                    @if($tasks && count($tasks) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Titre') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Statut') }}</th>
                                        <th>{{ __('Priorité') }}</th>
                                        <th>{{ __('Date limite') }}</th>
                                        <th>{{ __('Date de création') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tasks as $task)
                                        <tr>
                                            <td>{{ $task->title }}</td>
                                            <td>{{ Str::limit($task->description, 50) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($task->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}">
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($task->due_date)
                                                    {{ $task->due_date->format('d/m/Y') }}
                                                @else
                                                    {{ __('Aucune') }}
                                                @endif
                                            </td>
                                            <td>{{ $task->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('workflows.tasks.show', $task->id) }}" class="btn btn-sm btn-primary">
                                                    {{ __('Voir') }}
                                                </a>
                                                @if($task->status !== 'completed')
                                                    <a href="{{ route('workflows.tasks.edit', $task->id) }}" class="btn btn-sm btn-warning">
                                                        {{ __('Modifier') }}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('Aucune tâche assignée.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

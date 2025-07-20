@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Tâches de l\'Organisation') }}</h3>
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
                                        <th>{{ __('Assigné à') }}</th>
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
                                                @if($task->assigned_to)
                                                    {{ $task->assignedUser->name ?? 'N/A' }}
                                                @else
                                                    {{ __('Non assigné') }}
                                                @endif
                                            </td>
                                            <td>{{ $task->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('workflows.tasks.show', $task->id) }}" class="btn btn-sm btn-primary">
                                                    {{ __('Voir') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('Aucune tâche trouvée pour votre organisation.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

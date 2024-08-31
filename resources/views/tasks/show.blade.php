@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="bi bi-card-text me-2"></i>
                <h1 class="h3 mb-0">Task Details: {{ $task->name }}</h1>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h2 class="h4">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        {{ $task->name }}
                    </h2>
                    <span class="badge bg-secondary">
                        <i class="bi bi-flag me-1"></i>
                        Status: {{ $task->taskStatus->name }}
                    </span>
                </div>

                <div class="mb-4">
                    <h3 class="h5">
                        <i class="bi bi-info-circle me-2"></i>
                        Description
                    </h3>
                    <p>{{ $task->description }}</p>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h3 class="h5">
                            <i class="bi bi-clock me-2"></i>
                            Details
                        </h3>
                        <p><strong>Duration:</strong> {{ $task->duration }} hours</p>
                        <p><strong>Task Type:</strong> {{ $task->taskType->name }}</p>
                    </div>

                    <div class="col-md-6">
                        <h3 class="h5">
                            <i class="bi bi-people me-2"></i>
                            Assigned Users
                        </h3>
                        <ul class="list-group">
                            @foreach($task->users as $user)
                                <li class="list-group-item">
                                    <i class="bi bi-person me-2"></i>
                                    {{ $user->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <h3 class="h5">
                        <i class="bi bi-building me-2"></i>
                        Organizations
                    </h3>
                    <ul class="list-group">
                        @foreach($task->organisations as $org)
                            <li class="list-group-item">
                                <i class="bi bi-building me-2"></i>
                                {{ $org->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h3 class="h5">
                        <i class="bi bi-paperclip me-2"></i>
                        Attachments
                    </h3>
                    <ul class="list-group">
                        @foreach($task->attachments as $attachment)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="text-primary">
                                    <i class="bi bi-file-earmark me-2"></i>
                                    {{ $attachment->name }}
                                </a>
                                <form action="{{ route('tasks.download', ['task' => $task->id, 'attachment' => $attachment->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download me-1"></i>
                                        Download
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h3 class="h5">
                        <i class="bi bi-alarm me-2"></i>
                        Task Remembers
                    </h3>
                    <ul class="list-group">
                        @foreach($task->taskRemembers as $remember)
                            <li class="list-group-item">
                                <i class="bi bi-alarm me-2"></i>
                                {{ $remember->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h3 class="h5">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Task Records
                    </h3>
                    <ul class="list-group">
                        @foreach($task->taskRecords as $record)
                            <li class="list-group-item">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                {{ $record->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h3 class="h5">
                        <i class="bi bi-eye me-2"></i>
                        Task Supervisions
                    </h3>
                    <ul class="list-group">
                        @foreach($task->taskSupervisions as $supervision)
                            <li class="list-group-item">
                                <i class="bi bi-eye me-2"></i>
                                {{ $supervision->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h3 class="h5">
                        <i class="bi bi-envelope me-2"></i>
                        Task Mails
                    </h3>
                    <ul class="list-group">
                        @foreach($task->taskMails as $mail)
                            <li class="list-group-item">
                                <i class="bi bi-envelope me-2"></i>
                                {{ $mail->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h3 class="h5">
                        <i class="bi bi-box me-2"></i>
                        Task Containers
                    </h3>
                    <ul class="list-group">
                        @foreach($task->taskContainers as $container)
                            <li class="list-group-item">
                                <i class="bi bi-box me-2"></i>
                                {{ $container->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil-square me-1"></i>
                        Edit Task
                    </a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            Delete Task
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

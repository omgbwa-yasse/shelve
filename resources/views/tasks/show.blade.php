@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Task Details: {{ $task->name }}</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $task->name }}</h5>
                <h6 class="card-subtitle mb-2 text-muted">Status: {{ $task->taskStatus->name }}</h6>
                <p class="card-text">{{ $task->description }}</p>
                <p><strong>Duration:</strong> {{ $task->duration }} hours</p>

                <h6>Task Types:</h6>
                <ul>
                    @foreach($task->taskTypes as $type)
                        <li>{{ $type->name }}</li>
                    @endforeach
                </ul>

                <h6>Assigned Users:</h6>
                <ul>
                    @foreach($task->users as $user)
                        <li>{{ $user->name }}</li>
                    @endforeach
                </ul>

                <h6>Organizations:</h6>
                <ul>
                    @foreach($task->organisations as $org)
                        <li>{{ $org->name }}</li>
                    @endforeach
                </ul>

                <h6>Attachments:</h6>
                <ul>
                    @foreach($task->attachments as $attachment)
                        <li>
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                {{ basename($attachment->file_path) }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary">Edit Task</a>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this task?')">Delete Task</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Task: {{ $task->name }}</h1>
        <form action="{{ route('workflows.tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $task->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required>{{ $task->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="duration">Duration (hours)</label>
                <input type="number" class="form-control" id="duration" name="duration" value="{{ $task->duration }}" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                    @foreach($taskStatuses as $status)
                        <option value="{{ $status->value }}" {{ $task->status && $task->status->value == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="task_type_ids">Task Types</label>
                <select class="form-control" id="task_type_ids" name="task_type_ids[]" multiple required>
                    @foreach($taskTypes as $type)
                        <option value="{{ $type->id }}" {{ $task->taskTypes->contains($type->id) ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="user_ids">Assigned Users</label>
                <select class="form-control" id="user_ids" name="user_ids[]" multiple required>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $task->users->contains($user->id) ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="organisation_ids">Organisations</label>
                <select class="form-control" id="organisation_ids" name="organisation_ids[]" multiple required>
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ $task->organisations->contains($organisation->id) ? 'selected' : '' }}>
                            {{ $organisation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="attachments">Add New Attachments</label>
                <input type="file" class="form-control-file" id="attachments" name="attachments[]" multiple>
            </div>
            <button type="submit" class="btn btn-primary">Update Task</button>
        </form>

        <h2 class="mt-4">Current Attachments</h2>
        <ul class="list-group">
            @foreach($task->attachments as $attachment)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ basename($attachment->file_path) }}
                    <form action="{{ route('workflows.tasks.remove-attachment', ['task' => $task->id, 'attachment' => $attachment->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this attachment?')">Remove</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
@endsection

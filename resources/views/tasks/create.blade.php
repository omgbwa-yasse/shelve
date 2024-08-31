@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create New Task</h1>
        <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required></textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="duration">Duration (hours)</label>
                <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" required>
                @error('duration')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="task_status_id">Status</label>
                <select class="form-control @error('task_status_id') is-invalid @enderror" id="task_status_id" name="task_status_id" required>
                    @foreach($taskStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                @error('task_status_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="task_type_ids">Task Types</label>
                <select class="form-control @error('task_type_ids') is-invalid @enderror" id="task_type_ids" name="task_type_ids[]" multiple required>
                    @foreach($taskTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('task_type_ids')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="user_ids">Assigned Users</label>
                <select class="form-control @error('user_ids') is-invalid @enderror" id="user_ids" name="user_ids[]" multiple required>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_ids')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="organisation_ids">Organisations</label>
                <select class="form-control @error('organisation_ids') is-invalid @enderror" id="organisation_ids" name="organisation_ids[]" multiple required>
                    @foreach($organisations as $organisation)
                        <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                    @endforeach
                </select>
                @error('organisation_ids')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="attachments">Attachments</label>
                <input type="file" class="form-control-file @error('attachments') is-invalid @enderror" id="attachments" name="attachments[]" multiple>
                @error('attachments')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Create Task</button>
        </form>
    </div>
@endsection

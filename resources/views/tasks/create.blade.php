@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="bi bi-card-text me-2"></i>
                <h1 class="h3 mb-0">Create New Task</h1>
            </div>
            <div class="card-body">
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
                        <label for="task_type_id">Task Type</label>
                        <select class="form-control @error('task_type_id') is-invalid @enderror" id="task_type_id" name="task_type_id" required>
                            @foreach($taskTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('task_type_id')
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
                        <label for="organisation_ids">Organizations</label>
                        <select class="form-control @error('organisation_ids') is-invalid @enderror" id="organisation_ids" name="organisation_ids[]" multiple required>
                            @foreach($organisations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
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
                    <div class="form-group">
                        <label for="task_remember_ids">Task Remembers</label>
                        <select class="form-control @error('task_remember_ids') is-invalid @enderror" id="task_remember_ids" name="task_remember_ids[]" multiple>
                            @foreach($taskRemembers as $remember)
                                <option value="{{ $remember->id }}">{{ $remember->description }}</option>
                            @endforeach
                        </select>
                        @error('task_remember_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="task_record_ids">Task Records</label>
                        <select class="form-control @error('task_record_ids') is-invalid @enderror" id="task_record_ids" name="task_record_ids[]" multiple>
                            @foreach($taskRecords as $record)
                                <option value="{{ $record->id }}">{{ $record->description }}</option>
                            @endforeach
                        </select>
                        @error('task_record_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="task_supervision_ids">Task Supervisions</label>
                        <select class="form-control @error('task_supervision_ids') is-invalid @enderror" id="task_supervision_ids" name="task_supervision_ids[]" multiple>
                            @foreach($taskSupervisions as $supervision)
                                <option value="{{ $supervision->id }}">{{ $supervision->description }}</option>
                            @endforeach
                        </select>
                        @error('task_supervision_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="task_mail_ids">Task Mails</label>
                        <select class="form-control @error('task_mail_ids') is-invalid @enderror" id="task_mail_ids" name="task_mail_ids[]" multiple>
                            @foreach($taskMails as $mail)
                                <option value="{{ $mail->id }}">{{ $mail->description }}</option>
                            @endforeach
                        </select>
                        @error('task_mail_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="task_container_ids">Task Containers</label>
                        <select class="form-control @error('task_container_ids') is-invalid @enderror" id="task_container_ids" name="task_container_ids[]" multiple>
                            @foreach($taskContainers as $container)
                                <option value="{{ $container->id }}">{{ $container->description }}</option>
                            @endforeach
                        </select>
                        @error('task_container_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </form>
            </div>
        </div>
    </div>
@endsection

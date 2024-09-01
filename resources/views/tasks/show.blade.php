@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0">
                            <i class="bi bi-card-text me-2"></i>
                            Task Details: {{ $task->name }}
                        </h1>
                        <span class="badge bg-light text-primary">
                        Status: {{ $task->taskStatus->name }}
                    </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h3 class="h4 border-bottom pb-2">
                                <i class="bi bi-info-circle me-2"></i>
                                Description
                            </h3>
                            <p class="lead">{{ $task->description }}</p>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h3 class="h5">
                                    <i class="bi bi-clock me-2"></i>
                                    Details
                                </h3>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Duration:</strong> {{ $task->duration }} hours</li>
                                    <li class="list-group-item"><strong>Task Type:</strong> {{ $task->taskType->name }}</li>
                                </ul>
                            </div>

                            <div class="col-md-6">
                                <h3 class="h5">
                                    <i class="bi bi-people me-2"></i>
                                    Assigned Users
                                </h3>
                                <ul class="list-group list-group-flush">
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
                            <h3 class="h5 border-bottom pb-2">
                                <i class="bi bi-building me-2"></i>
                                Organizations
                            </h3>
                            <div class="row">
                                @foreach($task->organisations as $org)
                                    <div class="col-md-4 mb-2">
                                        <div class="card">
                                            <div class="card-body">
                                                <i class="bi bi-building me-2"></i>
                                                {{ $org->name }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <h3 class="h5 border-bottom pb-2">
                                <i class="bi bi-paperclip me-2"></i>
                                Attachments
                            </h3>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($task->attachments as $attachment)
                                        <tr>
                                            <td>
                                                <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="text-primary">
                                                    <i class="bi bi-file-earmark me-2"></i>
                                                    {{ $attachment->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <form action="{{ route('tasks.download', ['task' => $task->id, 'attachment' => $attachment->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-download me-1"></i>
                                                        Download
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-list-check me-2"></i>
                            Related Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="taskAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingMails">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMails" aria-expanded="false" aria-controls="collapseMails">
                                        <i class="bi bi-envelope me-2"></i>
                                        Task Mails
                                    </button>
                                </h3>
                                <div id="collapseMails" class="accordion-collapse collapse" aria-labelledby="headingMails" data-bs-parent="#taskAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach($task->taskMails as $mail)
                                                <li class="list-group-item">
                                                    <i class="bi bi-envelope me-2"></i>
                                                    {{ $mail->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingContainers">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContainers" aria-expanded="false" aria-controls="collapseContainers">
                                        <i class="bi bi-box me-2"></i>
                                        Task Containers
                                    </button>
                                </h3>
                                <div id="collapseContainers" class="accordion-collapse collapse" aria-labelledby="headingContainers" data-bs-parent="#taskAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach($task->taskContainers as $container)
                                                <li class="list-group-item">
                                                    <i class="bi bi-box me-2"></i>
                                                    {{ $container->code }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingRecords">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRecords" aria-expanded="false" aria-controls="collapseRecords">
                                        <i class="bi bi-file-earmark-text me-2"></i>
                                        Task Records
                                    </button>
                                </h3>
                                <div id="collapseRecords" class="accordion-collapse collapse" aria-labelledby="headingRecords" data-bs-parent="#taskAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach($task->taskRecords as $record)
                                                <li class="list-group-item">
                                                    <i class="bi bi-file-earmark-text me-2"></i>
                                                    {{ $record->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingRemembers">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRemembers" aria-expanded="false" aria-controls="collapseRemembers">
                                        <i class="bi bi-alarm me-2"></i>
                                        Task Remembers
                                    </button>
                                </h3>
                                <div id="collapseRemembers" class="accordion-collapse collapse" aria-labelledby="headingRemembers" data-bs-parent="#taskAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach($task->taskRemembers as $remember)
                                                <li class="list-group-item">
                                                    <i class="bi bi-alarm me-2"></i>
                                                    {{ $remember->periode }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingSupervisions">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSupervisions" aria-expanded="false" aria-controls="collapseSupervisions">
                                        <i class="bi bi-eye me-2"></i>
                                        Task Supervisions
                                    </button>
                                </h3>
                                <div id="collapseSupervisions" class="accordion-collapse collapse" aria-labelledby="headingSupervisions" data-bs-parent="#taskAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach($task->taskSupervisions as $supervision)
                                                <li class="list-group-item">
                                                    <i class="bi bi-eye me-2"></i>
                                                    {{ $supervision->user->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="bi bi-pencil-square me-2"></i>
                            Edit Task
                        </a>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" id="deleteTaskForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-lg w-100" id="deleteTaskBtn">
                                <i class="bi bi-trash me-2"></i>
                                Delete Task
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteTaskBtn = document.getElementById('deleteTaskBtn');
            const deleteTaskForm = document.getElementById('deleteTaskForm');

            deleteTaskBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteTaskForm.submit();
                    }
                });
            });
        });
    </script>
@endpush

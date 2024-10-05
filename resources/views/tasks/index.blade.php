@extends('layouts.app')

@section('content')
    <div class="container-fluid ">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h2 mb-0">
                    <i class="bi bi-list-task me-2"></i>
                    Tasks
                </h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('tasks.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg me-2"></i>
                    Create New Task
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('tasks.index') }}" method="GET" id="taskFilterForm">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                                <input type="text" name="search" class="form-control" placeholder="Search tasks" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" id="statusFilter">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-select" id="typeFilter">
                                <option value="">All Types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-2"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                        {{ $task->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $task->taskType->name ?? "N/A" }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->taskStatus->color ?? 'secondary' }}">
                                        {{ $task->taskStatus->name ?? "N/A"}}
                                    </span>
                                </td>
                                <td>
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $task->duration }} hours
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No tasks found. Try adjusting your filters or <a href="{{ route('tasks.create') }}">create a new task</a>.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection


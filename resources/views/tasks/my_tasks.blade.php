@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="bi bi-card-text me-2"></i>
                <h1 class="h3 mb-0">My Tasks</h1>
            </div>
            <div class="card-body">
                <div id="gantt_chart"></div> <!-- Gantt chart container -->

                <table class="table table-striped mt-4">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tasks as $task)
                        <tr>
                            <td>{{ $task->name }}</td>
                            <td>{{ $task->description }}</td>
                            <td>{{ $task->duration }} hours</td>
                            <td>{{ $task->taskStatus->name }}</td>
                            <td>
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-primary btn-sm">View</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
@endsection


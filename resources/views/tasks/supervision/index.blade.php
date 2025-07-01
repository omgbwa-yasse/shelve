@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Supervised Tasks</h1>
        <div class="mb-3">
            <form action="{{ route('tasks.supervision') }}" method="GET" class="form-inline">
                <input type="text" name="search" class="form-control mr-2" placeholder="Search tasks" value="{{ request('search') }}">
                <select name="status" class="form-control mr-2">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <select name="type" class="form-control mr-2">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->name }}</td>
                    <td>{{ $task->taskType->name }}</td>
                    <td>{{ $task->status ? $task->status->label() : 'N/A' }}</td>
                    <td>{{ $task->duration }} hours</td>
                    <td>
                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $tasks->links() }}
    </div>
@endsection

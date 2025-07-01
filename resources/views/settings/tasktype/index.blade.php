@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Task Types</h1>
        <a href="{{ route('tasktype.create') }}" class="btn btn-primary mb-3">Create New Task Type</a>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Color</th>
                <th>Description</th>
                <th>Activity</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($taskTypes as $taskType)
                <tr>
                    <td>{{ $taskType->id }}</td>
                    <td>{{ $taskType->name }}</td>
                    <td>
                        @if($taskType->color)
                            <span class="badge" style="background-color: {{ $taskType->color }}">{{ $taskType->color }}</span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $taskType->description }}</td>
                    <td>{{ $taskType->activity->name ?? "N/A" }}</td>
                    <td>
                        <a href="{{ route('tasktype.edit', $taskType->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('tasktype.destroy', $taskType->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
